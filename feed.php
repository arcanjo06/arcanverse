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
        <div class="header-right">
        <div class="notification">
                <button id="notification-button">
                    <i class="fas fa-bell"></i>
                    <span id="notificationCount" class="badge"></span>
                </button>
            </div>
        <button id="user-button" class="router-btn">Meu Perfil</button>
        </div>
    </header>

    <div id="notification-box" class="notification-box">
        <h3>Notificações</h3>
        <ul id="notification-list">
            <!-- As notificações serão carregadas aqui -->
        </ul>
    </div>

    <h1 class="tittle">Feed de Posts</h1>
    

    <!-- Formulário para criar um novo post -->
    <section id="create-post">
        <h3>Criar um Novo Post:</h3>
        <form id="post-form">
            <textarea name="content" rows="4" required placeholder="Digite seu post aqui..."></textarea>
            <br>
            <button class="button" type="submit" name="new_post">Criar Post</button>
        </form>
    </section>
    <!-- Seção de Feed de Posts -->
    <section id="post-feed">
   
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

    $sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['post_id' => $post['ID']]);
    $commentCount = $stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];
    ?>

        <li class="post-container">
            <div class="post-header">
                <small style="color: gray;">Postado em: <?php echo $post['created_at']; ?></small>
                <small style="color: black; font-size:0.9rem;">por: <?php echo $post['username']; ?></small>
            </div>
            <div class="post-content">
             <p><?php echo htmlspecialchars($post['content']); ?></p>
         </div>
            <div class="post-actions">
                <button class="like-button" data-post-id="<?php echo $post['ID']; ?>">
                    <i class="fas fa-heart <?php echo $userLiked ? 'liked' : ''; ?>"></i>
                    <span class="like-count"><?php echo $likeCount; ?></span> <!-- Exibe a contagem de likes -->
                </button>
                <button class="comment-button" data-post-id="<?php echo $post['ID']; ?>">
                    <i class="fa fa-comment"></i>
                    <span class="like-count"><?php echo $commentCount; ?></span>
                </button>
            </div>
        </li>
    <?php endforeach; ?>
        <?php else: ?>
            <li>Nenhum post encontrado.</li>
        <?php endif; ?>
    </ul>
</section>

    <script> 
    document.getElementById('user-button').addEventListener('click', () => {
            window.location.href = './user.php'; // Caminho para a página do feed
        });
        document.querySelectorAll('.comment-button').forEach(button => {
  button.addEventListener('click', function() {
    const postId = this.getAttribute('data-post-id');
    console.log(postId);
    window.location.href = 'post.php?post_id=' + postId; // redireciona para a página post.php com os parâmetros
  });
});

document.getElementById('notification-button').addEventListener('click', () => {
            const notificationBox = document.getElementById('notification-box');
            notificationBox.style.display = notificationBox.style.display === 'block' ? 'none' : 'block';

            loadNotifications(); // Carrega as notificações ao abrir a caixa
        });

        async function loadNotifications() {
            try {
                const response = await fetch('backend/notifications.php', { 
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                });
                const data = await response.json();
                console.log(data);

                const notificationList = document.getElementById('notification-list');

                notificationList.innerHTML = '';

                data.forEach(notification => {
                    const notificationItem = document.createElement('li');
                    notificationItem.textContent = notification.message;
                    if (!notification.read) {
                        notificationItem.style.border = '2px solid lightgreen';
                    } else {
                        notificationItem.style.color = 'gray';
                    }
                    notificationList.appendChild(notificationItem);
                });
            } catch (error) {
                console.error('Erro ao carregar notificações:', error);
            }
        }

            const notificationCount = document.getElementById('notificationCount');
            try{
                fetch('backend/notifications.php')
                .then(response => response.json())
                .then(data => {
                    notificationCount.textContent = data.length;
                });
            } catch (error) {
                console.error('Erro ao carregar notificações:', error);
            }
        </script>
    <script src="./frontend/js/scripts.js"></script> <!-- Caminho para o JS -->
</body>
</html>