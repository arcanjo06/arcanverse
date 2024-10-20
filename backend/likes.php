<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Você deve estar logado para curtir posts.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$post_id = $data['post_id'];
$user_id = $_SESSION['user_id'];

// Verifica se o post já foi curtido
$sql = "SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);

if ($stmt->rowCount() > 0) {
    // Remover like
    $sql = "DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);

    $action = 'like_removed';
} else {
    // Adicionar like
    $sql = "INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);

    $action = 'like_added';

    // Adicionar notificação
    // Primeiro, recupera o ID do usuário que postou o conteúdo
    $sql = "SELECT user_id FROM posts WHERE ID = :post_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['post_id' => $post_id]);
    $post_owner = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];

    // Se o post não for do próprio usuário, envia a notificação
    if ($post_owner != $user_id) {
        $sql = "SELECT username FROM users WHERE ID = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];

        $message = "Usuário $username curtiu seu post.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$post_owner, $user_id, $post_id, $message]);

    }
}

// Pega a nova contagem de likes
$sql = "SELECT COUNT(*) AS like_count FROM likes WHERE post_id = :post_id";
$stmt = $conn->prepare($sql);
$stmt->execute(['post_id' => $post_id]);
$likeCount = $stmt->fetch(PDO::FETCH_ASSOC)['like_count'];

echo json_encode([
    'message' => $action === 'like_added' ? 'Like adicionado.' : 'Like removido.',
    'like_count' => $likeCount // Retorna a nova contagem de likes
]);