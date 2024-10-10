<?php
session_start();

error_reporting(E_ALL & ~E_NOTICE); // Reporta todos os erros, exceto notices
ini_set('display_errors', 1); // Exibe os erros

if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit();
}
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "Variável de sessão user_id não definida ou vazia";
    exit();
}

// Inclui a lógica para obter os posts do usuário
require 'backend/get_all_posts.php'; // Certifique-se de ter essa função implementada

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcanverse - Página do Usuário</title>
    <link rel="stylesheet" href="frontend/css/style.css"> <!-- Caminho para o CSS -->
</head>
<body>

    <h1>Feed de Posts</h1>

    <!-- Formulário para criar um novo post -->
    <section id="create-post">
        <h3>Criar um Novo Post:</h3>
        <form id="post-form">
            <textarea name="content" rows="4" required placeholder="Digite seu post aqui..."></textarea>
            <br>
            <button type="submit" name="new_post">Criar Post</button>
        </form>
    </section>
    <!-- Seção de Feed de Posts -->
    <section id="post-feed" style="text-align: center;">
   
    <ul id="post-list" style="display: inline-block; text-align: left;">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <li class="post-container">
                    <div class="post-header">
                        <small style="color: gray;">Postado em: <?php echo $post['created_at']; ?></small>
                        <small style="color: gray;">por: <?php echo $post['username']; ?></small>
                    </div>
                    <div class="post-content">
                        <p><?php echo htmlspecialchars($post['content']); ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>Nenhum post encontrado.</li>
        <?php endif; ?>
    </ul>
</section>

    <script src="frontend/js/scripts.js"></script> <!-- Caminho para o JS -->

    <script>
        // Redirecionando para o feed ao clicar no botão
        
            
        
        document.getElementById('post-form').addEventListener('submit', async (event) => {
            event.preventDefault(); // Evita o comportamento padrão do formulário

            const content = document.querySelector('textarea[name="content"]').value; // Captura o valor do textarea

            if (!content.trim()) { // Verifica se o conteúdo não está vazio
                alert("Por favor, escreva algo no post.");
                return;
            }

            const response = await fetch('backend/create_post.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content }), // Envia o conteúdo como JSON
            });

            const result = await response.json(); // Espera a resposta como JSON

            if (response.ok) {
                console.log('Post criado com sucesso:', result);
                window.location.reload(); // Recarrega a página
            } else {
                console.error('Erro ao criar o post:', result.error);
            }
        });

        async function deletePost(postId) {
            try {
                const response = await fetch('backend/delete_post.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ postId }), // Envia o ID do post
                });

                const result = await response.json();

                if (response.ok) {
                    console.log('Post excluído com sucesso:', result);
                    window.location.reload(); // Recarrega a página após excluir o post
                } else {
                    console.error('Erro ao excluir o post:', result.error);
                    alert(result.error); // Mostra o erro ao usuário
                }
            } catch (error) {
                console.error('Erro na requisição:', error);
                alert('Houve um problema ao tentar excluir o post. Tente novamente mais tarde.');
            }
        }

        // Adiciona evento de exclusão para cada texto "(Excluir)"
        document.querySelectorAll('.delete-post').forEach(span => {
            span.addEventListener('click', function() {
                const postId = this.getAttribute('data-post-id'); // Captura o ID do post
                console.log('ID do Post:', postId); // Para verificar se está capturando corretamente
                if (postId) { // Verifica se postId não é null ou undefined
                    if (confirm('Tem certeza que deseja excluir este post?')) {
                        deletePost(postId); // Chama a função de deletar
                    }
                } else {
                    console.error('ID do post não encontrado.'); // Mensagem de erro
                }
            });
        });

    </script>
</body>
</html>