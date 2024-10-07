# ArcanVerse

ArcanVerse é uma plataforma de microblogging inspirada no Twitter, onde os usuários podem criar, visualizar, curtir e comentar em posts. O objetivo do projeto é fornecer uma experiência de compartilhamento de pensamentos e ideias de forma simples e eficaz.

## Funcionalidades

- **Criação de Posts**: Usuários podem criar posts com até 280 caracteres.
- **Exclusão de Posts**: Usuários podem excluir apenas seus próprios posts.
- **Curtidas**: Os usuários podem curtir posts de outros usuários.
- **Comentários**: Os usuários podem comentar em posts.
- **Sistema de Autenticação**: Permite que os usuários se registrem e façam login.

## Tecnologias Utilizadas

- **Frontend**: React
- **Backend**: PHP
- **Banco de Dados**: MySQL
- **Servidor**: XAMPP

## Estrutura do Banco de Dados

As seguintes tabelas são usadas no banco de dados:

- `users`: Armazena informações dos usuários.
- `posts`: Armazena os posts criados pelos usuários.
- `likes`: Registra as curtidas em posts.
- `comments`: Armazena os comentários feitos nos posts.

## Como Executar o Projeto

### Requisitos

- XAMPP ou outro servidor local com suporte a PHP e MySQL.
- Node.js e npm instalados para o frontend (React).

### Configuração do Backend

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu_usuario/arcanverse.git
   cd arcanverse/backend
