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
    <title>Arcanverse - Feed</title>
    <link rel="stylesheet" href="frontend/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Caminho para o CSS -->
</head>
<body>
    <header>
        <img class="logo" src="images/1.png" alt="logo" width="70px" height="70px">
        <h1 style="font-size:3vw">ARCANVERSE</h1>
        <button id="user-button" class="router-btn">Meu Perfil</button>
    </header>

    <h1 class="tittle">Feed de Posts</h1>
    

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
    <?php
    // Verifica se o usuário atual já curtiu este post
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
                <small style="color: gray;">Postado em: <?php echo $post['created_at']; ?></small>
                <small style="color: gray;">por: <?php echo $post['username']; ?></small>
            </div>
            <div class="post-content">
             <p><?php echo htmlspecialchars($post['content']); ?></p>
         </div>
            <div class="post-actions">
                <button class="like-button" data-post-id="<?php echo $post['ID']; ?>">
                    <i class="fas fa-heart <?php echo $userLiked ? 'liked' : ''; ?>"></i>
                    <span class="like-count"><?php echo $likeCount; ?></span> <!-- Exibe a contagem de likes -->
                </button>
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
        // Redirecionando para a user page ao clicar no botão
        document.getElementById('user-button').addEventListener('click', () => {
            window.location.href = 'user.php'; // Caminho para a página do feed
        });
           
        document.querySelectorAll('.like-button').forEach(button => {
    button.addEventListener('click', async function() {
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
    </script>
</body>
</html>