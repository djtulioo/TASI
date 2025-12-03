#!/usr/bin/env node

import 'dotenv/config';
import readline from 'node:readline/promises';
import { stdin as input, stdout as output } from 'node:process';
import { GoogleGenAI } from '@google/genai';

// =======================================
// Config Gemini
// =======================================
const GEMINI_API_KEY = process.env.API_KEY_GEMINI;

if (!GEMINI_API_KEY) {
  console.error('Falta API_KEY_GEMINI no ambiente. Configure no .env.');
  process.exit(1);
}

const ai = new GoogleGenAI({ apiKey: GEMINI_API_KEY });
const MODEL_ID = 'gemini-2.5-flash';

// Tipos de cadastro
const CadastroTipo = {
  DEMANDA: 'demanda',
  SUGESTAO: 'sugestao',
  OPINIAO: 'opiniao',
};

// =======================================
// "Ferramentas" (functionDeclarations) p/ Gemini
// =======================================
const functionDeclarations = [
  {
    name: 'solicitar_cadastro_ouvidoria',
    description:
      'Solicita confirmação antes de cadastrar uma entrada de ouvidoria de um dos tipos: demanda, sugestao ou opiniao. Deve ser chamada quando o usuário expressar uma intenção clara de registrar algo.',
    parameters: {
      type: 'object',
      properties: {
        tipo: {
          type: 'string',
          description: 'Tipo do registro: demanda | sugestao | opiniao',
          enum: [CadastroTipo.DEMANDA, CadastroTipo.SUGESTAO, CadastroTipo.OPINIAO],
        },
        titulo: {
          type: 'string',
          description: 'Título resumido do registro',
        },
        descricao: {
          type: 'string',
          description: 'Descrição detalhada fornecida pelo usuário',
        },
      },
      required: ['tipo', 'descricao'],
      additionalProperties: false,
    },
  },
  {
    name: 'confirmar_cadastro_ouvidoria',
    description:
      'Confirma o cadastro solicitado anteriormente e executa o registro. Use somente após o usuário confirmar explicitamente que deseja cadastrar.',
    parameters: {
      type: 'object',
      properties: {
        tipo: {
          type: 'string',
          description: 'Tipo do registro: demanda | sugestao | opiniao',
          enum: [CadastroTipo.DEMANDA, CadastroTipo.SUGESTAO, CadastroTipo.OPINIAO],
        },
        titulo: {
          type: 'string',
          description: 'Título resumido do registro',
        },
        descricao: {
          type: 'string',
          description: 'Descrição detalhada fornecida pelo usuário',
        },
        confirmar: {
          type: 'boolean',
          description: 'True se o usuário confirmou explicitamente o cadastro',
        },
      },
      required: ['tipo', 'descricao', 'confirmar'],
      additionalProperties: false,
    },
  },
];

// Config do modelo (equivalente ao system prompt + tools)
const SYSTEM_INSTRUCTION =
  'Você é um assistente de ouvidoria que ajuda usuários a registrar demanda, sugestão ou opinião. ' +
  'Sempre confirme explicitamente antes de cadastrar. Se o usuário apenas explorar ideias, faça perguntas ' +
  'para esclarecer e, quando apropriado, proponha um pré-cadastro (solicitar_cadastro_ouvidoria). ' +
  'Somente execute o cadastro após confirmação clara (confirmar_cadastro_ouvidoria).';

const CONFIG = {
  systemInstruction: SYSTEM_INSTRUCTION,
  tools: [
    {
      functionDeclarations,
    },
  ],
  // Se quiser forçar sempre usar funções:
  // toolConfig: { functionCallingConfig: { mode: 'any' } },
};

// =======================================
// Funções locais que serão "invocadas"
// =======================================
function solicitarCadastroOuvidoria({ tipo, titulo = '', descricao }) {
  console.log('\n[Pré-cadastro]');
  console.log(`Tipo: ${tipo}`);
  if (titulo) console.log(`Título: ${titulo}`);
  console.log(`Descrição: ${descricao}`);
  console.log('Confirme para efetivar o cadastro.');

  return { status: 'aguardando_confirmacao', tipo, titulo, descricao };
}

function confirmarCadastroOuvidoria({ tipo, titulo = '', descricao, confirmar }) {
  if (!confirmar) {
    console.log('\n[Cadastro cancelado pelo usuário]');
    return { status: 'cancelado' };
  }

  console.log('\n[Cadastro efetivado]');
  console.log(`Tipo: ${tipo}`);
  if (titulo) console.log(`Título: ${titulo}`);
  console.log(`Descrição: ${descricao}`);

  return { status: 'efetivado', tipo, titulo, descricao };
}

// Conversão de FunctionCall do Gemini em execução local
async function executarToolCallGemini(functionCall) {
  const { name, args = {} } = functionCall || {};

  if (name === 'solicitar_cadastro_ouvidoria') {
    const result = solicitarCadastroOuvidoria(args);
    return { name, result };
  }

  if (name === 'confirmar_cadastro_ouvidoria') {
    const result = confirmarCadastroOuvidoria(args);
    return { name, result };
  }

  const result = { error: 'função desconhecida' };
  console.log('\n[Erro]', result.error, '-', name);
  return { name: name || 'desconhecida', result };
}

// =======================================
// Main (loop de chat, estilo OpenAI, mas com Gemini)
// =======================================
async function main() {
  const rl = readline.createInterface({ input, output });
  console.log('Ouvidoria CLI (Gemini). Digite "sair" para encerrar.');

  // Histórico no formato do Gemini: array de Contents
  /** @type {Array<{role: string, parts: any[]}>} */
  const contents = [];

  while (true) {
    const text = await rl.question('\nVocê: ');
    if (!text) continue;
    if (text.trim().toLowerCase() === 'sair') break;

    // Turno do usuário
    contents.push({
      role: 'user',
      parts: [{ text }],
    });

    // 1ª chamada: modelo decide se responde direto ou chama função
    let response;
    try {
      response = await ai.models.generateContent({
        model: MODEL_ID,
        contents,
        config: CONFIG,
      });
    } catch (err) {
      console.error('\nErro na chamada Gemini:', err);
      continue;
    }

    const assistantText = (response.text || '').trim();

    if (assistantText) {
      console.log(`\nAssistente: ${assistantText}`);
    }

    // Guarda o turno do modelo no histórico
    if (response.candidates?.[0]?.content) {
      contents.push(response.candidates[0].content);
    }

    const toolCalls = response.functionCalls || [];

    // Se houver function calls, executa e faz 2ª chamada com FunctionResponse
    if (toolCalls.length > 0) {
      const functionResponses = [];
      for (const call of toolCalls) {
        const { name, result } = await executarToolCallGemini(call);
        functionResponses.push({
          name,
          response: { result },
        });
      }

      // Append FunctionResponse como novo turno de "user"
      contents.push({
        role: 'user',
        parts: functionResponses.map((fr) => ({
          functionResponse: fr,
        })),
      });

      // 2ª chamada: modelo usa os resultados das funções para responder
      let followUp;
      try {
        followUp = await ai.models.generateContent({
          model: MODEL_ID,
          contents,
          config: CONFIG,
        });
      } catch (err) {
        console.error('\nErro na chamada Gemini (follow-up):', err);
        continue;
      }

      const followUpText = (followUp.text || '').trim();
      if (followUpText) {
        console.log(`\nAssistente: ${followUpText}`);
      }

      if (followUp.candidates?.[0]?.content) {
        contents.push(followUp.candidates[0].content);
      }
    }
  }

  rl.close();
  console.log('\nAté logo!');
}

main().catch((err) => {
  console.error('Erro no chatbot:', err);
  process.exit(1);
});
