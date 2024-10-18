<?php
// Conectar ao banco de dados
session_start();
require './backend/conexao.php';
$post_id = $_GET['post_id'];

// Obter o post
$stmt = $conn->prepare("SELECT * FROM posts WHERE ID = :post_id");
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();

// Verificar se o post existe
if ($stmt->rowCount() > 0) {
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o usuário atual já curtiu este post
    $sql = "SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $_SESSION['user_id'], 'post_id' => $post['ID']]);
    $userLiked = $stmt->rowCount() > 0;

    // Contagem de likes para este post
    $sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['post_id' => $post['ID']]);
    $likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

    // Contagem de comentários para este post
    $sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE post_id = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['post_id' => $post['ID']]);
    $commentCount = $stmt->fetch(PDO::FETCH_ASSOC)['comment_count'];

    // Obter o usuário que postou
    $sql = "SELECT * FROM users WHERE ID = :user_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $post['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    echo "Post não encontrado.";
    exit;
}

// Obter os comentários com os usuários que comentaram
$sql = "SELECT comments.*, users.username FROM comments 
        JOIN users ON comments.user_id = users.ID
        WHERE comments.post_id = :post_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['post_id' => $post['ID']]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ARCANVERSE</title>
  <link rel="stylesheet" href="./frontend/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<header>
    <img class="logo" src="images/1.png" alt="logo" width="70px" height="70px">
    <h1 style="font-size:3vw">ARCANVERSE</h1>
    <button id="user-button" class="router-btn">Meu Perfil</button>
</header>
<h1>Comentários</h1>

<section id="post" class="post-container">
  <div class="post-header">
    <small style="color: gray;">Postado em: <?php echo $post['created_at']; ?></small>
    <small style="color: black; font-size:0.9rem;">por: <?php echo $user['username']; ?></small>
  </div>
  <div class="post-content">
    <h2><?php echo $post['content']; ?></h2>
    <div class="post-actions">
      <button class="like-button" data-post-id="<?php echo $post['ID']; ?>">
        <i class="fas fa-heart <?php echo $userLiked ? 'liked' : ''; ?>"></i>
        <span class="like-count"><?php echo $likeCount; ?></span> <!-- Exibe a contagem de likes -->
      </button>
      <button class="comment-button">
        <i class="fa fa-comment"></i>
        <span class="like-count"><?php echo $commentCount; ?> </span>
      </button>
    </div>
  </div>
</section>

<section id="comments" class="comment-container">
  <ul id="posts-list">
    <?php if (!empty($comments)): ?>
      <?php foreach ($comments as $comment): 
        $post_id=$_GET['post_id'];
        ?>
        <li>
          <section class="post-container">
          <div class="post-header">
            <small style="color: gray;">Postado em: <?php echo $comment['criado_em']; ?></small>
            <small style="color: black; font-size:0.9rem;">por: <?php echo $comment['username']; ?></small>
          </div>
          <div class="post-content">
            <?php echo $comment['content']; ?>
          </div>
          </section>
        </li>
        
      <?php endforeach; ?>
    <?php else: ?>
      <p>Nenhum comentário encontrado.</p>
    <?php endif; ?>
  </ul>
</section>

<form id="comment-form" class="comment-form">
  <input id="post-id" type="hidden" name="post_id" data-id="<?php echo $post_id; ?>">
  <textarea name="content" rows="4" required placeholder="Escreva um comentário..."></textarea>
  <button class="button" id="form-submit" type="submit" name="new_comment">Comentar</button>
</form>

<script>
  document.getElementById('user-button').addEventListener('click', () => {
            window.location.href = './user.php'; // Caminho para a página do feed
        });
document.querySelectorAll('.like-button').forEach(button => {
  button.addEventListener('click', async function() {
    const postId = this.getAttribute('data-post-id');
    const heartIcon = this.querySelector('.fas.fa-heart');
    const likeCountSpan = this.querySelector('.like-count');

    try {
      const response = await fetch('./backend/likes.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId }),
      });

      const result = await response.json();

      if (response.ok) {
        if (result.message === 'Like adicionado.') {
          heartIcon.classList.add('liked');
        } else if (result.message === 'Like removido.') {
          heartIcon.classList.remove('liked');
        }
        likeCountSpan.textContent = result.like_count;
      } else {
        console.error('Erro ao curtir o post:', result.error);
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
    }
  });
});

document.getElementById('form-submit').addEventListener('click', (event) => {
  event.preventDefault(); // Prevenir que o formulário envie automaticamente

  // Obter o ID do post e o conteúdo do comentário
  const postId = document.getElementById('post-id').getAttribute('data-id');
  const content = document.getElementsByName('content')[0].value;

  // Exibir os dados para verificação
  console.log('Post ID:', postId);
  console.log('Comentário:', content);

  // Verificar se o campo de comentário está vazio
  if (content === '') {
    alert('Por favor, preencha o campo de comentário.');
    return;
  }

  // Enviar requisição para o backend
  fetch('./backend/comment.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ post_id: postId, content: content }),
  })
    .then(async response => {
      const result = await response.text(); // Alterar para text() para depuração

      // Tentar parsear a resposta como JSON
      try {
        const jsonResult = JSON.parse(result);
        console.log(jsonResult);

        // Verificar se o comentário foi adicionado com sucesso
        if (jsonResult.message === 'Comentário adicionado.') {
          console.log('Comentário adicionado.');
          alert('Comentário adicionado com sucesso!');
          location.reload(); // Atualizar a página para exibir o novo comentário
        } else {
          console.error('Erro ao adicionar o comentário:', jsonResult.error);
          alert(jsonResult.error);
        }
      } catch (error) {
        console.error('Erro ao processar a resposta como JSON:', error);
        console.error('Resposta recebida do servidor:', result);
      }
    })
    .catch(error => {
      console.error('Erro na requisição:', error);
    });
});
</script>
</body>
</html>