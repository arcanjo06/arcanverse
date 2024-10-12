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
require 'backend/get_user_posts.php'; // Certifique-se de ter essa função implementada

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arcanverse - Página do Usuário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="frontend/css/style.css"> <!-- Caminho para o CSS -->
</head>
<body>
    <header>
        <img class="logo" src="images/1.png" alt="logo" width="70px" height="70px">
        <h1 style="font-size:3vw">ARCANVERSE</h1>
        <button id="feed-button" class="router-btn">Ver Feed</button>
        <button id="logout-button">
            <a href="backend/logout.php">Sair</a>
        </button> 
    </header>
    

    <!-- Seção de Boas-vindas -->
    <section id="welcome-section">
        <h2>Bem-vindo, <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</h2>
    </section>

    <!-- Formulário para criar um novo post -->
    <section id="create-post">
        <h3>Criar um Novo Post:</h3>
        <form id="post-form">
            <textarea name="content" rows="4" required placeholder="Digite seu post aqui..."></textarea>
            <br>
            <button type="submit" name="new_post">Criar Post</button>
        </form>
    </section>

    <!-- Seção de Posts do Usuário -->
    <section id="user-posts" style="padding:20px">
        <h3>Seus Posts:</h3>
        <ul id="posts-list">
            <?php if (!empty($userPosts)): ?>
                <?php foreach ($userPosts as $post):?>
                    <?php
                        $userLiked = false;
                        $sql = "SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute(['user_id' => $_SESSION['user_id'], 'post_id' => $post['ID']]);
    
                        if ($stmt->rowCount() > 0) {
                            $userLiked = true;
                        }

                            // Contagem de likes para este post
                        $sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = :post_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute(['post_id' => $post['ID']]);
                        $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];
                        ?>
                    <li class="post-container">
                        <div class="post-header">
                        <?php
                        $userId = $post['user_id'];
                        $userName = getUserNameById($userId);
                        
                        ?>
                            <small style="color: gray;">
                                Postado em: <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                                por:<?php echo $userName; ?>
                            </small>
                        </div>
                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <div class="post-actions">
                                <button class="like-button" data-post-id="<?php echo $post['ID']; ?>">
                                <i class="fas fa-heart <?php echo $userLiked ? 'liked' : ''; ?>"></i>
                                <span class="like-count"><?php echo $likeCount; ?></span> <!-- Exibe a contagem de likes -->
                                </button>
                            </div>
                            <span id="delete-post-<?php echo $post['ID']; ?>" class="delete-post" data-post-id="<?php echo $post['ID']; ?>" style="color: red; cursor: pointer;">Excluir</span>
                        </div>
                        
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Você ainda não fez nenhum post.</li>
            <?php endif; ?>
        </ul>
    </section>

    <script src="frontend/js/scripts.js"></script> <!-- Caminho para o JS -->

    <script>
        // Redirecionando para o feed ao clicar no botão
        document.getElementById('feed-button').addEventListener('click', () => {
            window.location.href = 'feed.php'; // Caminho para a página do feed
        });

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

        document.querySelectorAll('.like-button').forEach(button => {
            button.addEventListener('click', async function() {
                console.log('Adicionando evento ao botão', button);
                const postId = this.getAttribute('data-post-id');
                const heartIcon = this.querySelector('.fas.fa-heart');
                const likeCountSpan = this.querySelector('.like-count');

            try {
                const response = await fetch('backend/likes.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                 },
                    body: JSON.stringify({ post_id: postId }),
                });

            const result = await response.json();
            console.log(result);

            if (response.ok) {
                if (result.message === 'Like adicionado.') {
                    heartIcon.classList.add('liked'); // Adiciona classe liked
                } else if (result.message === 'Like removido.') {
                    heartIcon.classList.remove('liked'); // Remove classe liked
                }
                likeCountSpan.textContent = result.like_count; // Atualiza a contagem de likes
            } else {
                console.error('Erro ao curtir o post:', result.error);
            }
        } catch (error) {
            console.error('Erro na requisição:', error);
        }
    });
});

    </script>
</body>
</html>