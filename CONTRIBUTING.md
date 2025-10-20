# Guia de Contribuição para o Pulsar Político

Ficamos felizes com seu interesse em contribuir para o projeto! Este guia detalha as normas e o processo para submeter suas contribuições.

## 💬 Código de Conduta

Esperamos que todos os contribuidores sigam um código de conduta que promova um ambiente aberto e acolhedor. Por favor, seja respeitoso em todas as interações. Críticas construtivas são bem-vindas, mas ataques pessoais não serão tolerados.

## 🛠️ Configurando o Ambiente de Desenvolvimento

Antes de começar, você precisa configurar o ambiente de desenvolvimento local. As instruções detalhadas de instalação de dependências e execução do projeto estão no arquivo `BUILD.md`.

➡️ [**Consulte o Guia de Build**](BUILD.md)

## 🌊 Processo de Contribuição (Git Flow)

Nosso processo de submissão de código segue um fluxo padrão do GitHub para garantir organização e qualidade.

1.  **Faça o Fork do Repositório:**
    - Clique no botão "Fork" no canto superior direito da página do repositório para criar uma cópia em sua conta do GitHub.

2.  **Clone o seu Fork:**
    ```bash
    git clone https://github.com/SEU_USUARIO/pulsar-politico.git
    cd pulsar-politico
    ```

3.  **Crie uma Nova Branch:**
    - Crie uma branch descritiva para sua nova funcionalidade (`feature`), correção de bug (`fix`), ou melhoria de documentação (docs). Use o padrão tipo/nome-curto-da-tarefa.
    ```bash
    # Para uma nova funcionalidade
    git switch -c feature/nome-da-funcionalidade

    # Para uma correção de bug
    git switch -c fix/descricao-do-bug
    ```

4.  **Faça suas Alterações:**
    - Implemente sua funcionalidade ou correção.
    - Siga os padrões de estilo do projeto. O projeto utiliza **Laravel Pint** para formatação automática do código PHP. Execute-o antes de commitar:
    ```bash
    # Dentro da pasta apps/web
    ./vendor/bin/pint
    ```
    - Escreva commits claros e concisos, seguindo o padrão de [Commits Convencionais](https://www.conventionalcommits.org/en/v1.0.0/). Exemplo: feat: Adiciona autenticação de dois fatores.

5.  **Envie suas Alterações (Push):**
    - Envie a sua branch para o seu fork no GitHub.
    ```bash
    git push origin NOME_DA_SUA_BRANCH
    ```

6.  **Abra um Pull Request (PR):**
    - Acesse a página do seu fork no GitHub e clique em "New pull request" ou "Compare & pull request".
    - Certifique-se de que a base de comparação seja a branch `main` do repositório original.
    - Descreva detalhadamente as alterações que você fez no PR. Se o PR resolve uma `issue` existente, mencione-a (ex: `Closes #123`).
    - Aguarde a revisão do time. Um ou mais membros irão revisar seu código e podem solicitar alterações antes de aprovar e fazer o merge.

Obrigado por sua colaboração!