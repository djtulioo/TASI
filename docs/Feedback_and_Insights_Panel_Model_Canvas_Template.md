# **Canvas de Feedback e Iteração**

Fase: 4\. Validação

Foco: Ética e Impacto Social

## **1\. Análise de Riscos e Ética**

* **Viés Algorítmico:** Risco da IA interpretar gírias ou erros de português como agressividade. *Mitigação:* Prompt instrui a focar na semântica da reclamação, ignorando a forma.  
* **Privacidade:** Envio de dados pessoais para API externa. *Mitigação:* Camada de serviço remove nomes e telefones antes de enviar o JSON para o Gemini.  
* **Dependência:** Gestor confiar cegamente no resumo. *Mitigação:* Aviso na interface: "Resposta gerada por IA, verifique os dados originais".

## **2\. Feedback de Uso (Iteração)**

* **Pivotagem Estratégica:** O projeto iniciou focado em gráficos (Dashboard). Após testes, percebeu-se que gráficos não mostravam a "dor" do usuário. Pivotou-se para **Interface de Chat**, permitindo análise qualitativa profunda.  
* **Solicitação de Feature:** Usuários pediram feedback ativo (o bot responder ao aluno). Adicionado ao roadmap futuro.

## **3\. Próximos Passos**

1. Implementar Vector Database para buscas semânticas em longos períodos.  
2. Expandir para WhatsApp Business API oficial.  