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