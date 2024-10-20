<?php
session_start();
require 'backend/conexao.php';
error_reporting(E_ALL & ~E_NOTICE);  // Conexão com o banco de dados

// Função para obter notificações
function getNotifications($userId) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Verifica se o usuário está autenticado
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
        <span class="header-right">
            <div class="notification">
                <button id="notification-button">
                    <i class="fas fa-bell"></i>
                    <span id="notificationCount" class="badge"></span>
                </button>
            </div>
            <button id="feed-button" class="router-btn">Ver Feed</button>
        </span>
        <button id="logout-button">
            <a href="backend/logout.php">Sair</a>
        </button> 
    </header>

    <!-- Caixa de notificações oculta inicialmente -->
    <div id="notification-box" class="notification-box">
        <h3>Notificações</h3>
        <ul id="notification-list">
            <!-- As notificações serão carregadas aqui -->
        </ul>
    </div>
    

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
            <button class="button" type="submit" name="new_post">Criar Post</button>
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

                        $sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = :post_id";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute(['post_id' => $post['ID']]);
                        $commentCount = $stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];
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
                                <button class="comment-button"  data-post-id="<?php echo $post['ID']; ?>">
                                    <i class="fa fa-comment"></i>
                                    <span class="like-count"><?php echo $commentCount; ?></span>
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

    <script>
        document.getElementById('feed-button').addEventListener('click', () => {
            window.location.href = 'feed.php'; // Caminho para a página do feed
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
            alert('Houve um problema ao tentar excluir o post.');
        }
    }
    </script>
</body>
</html>