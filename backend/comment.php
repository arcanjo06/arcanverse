<?php
require 'conexao.php';
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Usuário não autenticado."]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['post_id']) || !isset($data['content'])) {
    echo json_encode(["error" => "Dados inválidos."]);
    exit;
}

if (empty($data['content'])) {
    echo json_encode(["error" => "O comentário não pode estar vazio."]);
    exit;
}

$post_id = $data['post_id'];
$user_id = $_SESSION['user_id'];
$content = $data['content'];

// Insere o comentário no banco de dados
$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, criado_em) VALUES (?, ?, ?, NOW())");
$stmt->execute([$post_id, $user_id, $content]);

if ($stmt->rowCount() > 0) {
    // Agora, enviamos a notificação para o autor do post
    // Primeiro, pega o ID do usuário que fez o post
    $stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = :post_id");
    $stmt->execute(['post_id' => $post_id]);
    $post_owner = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];

    // Se o comentário não for do próprio autor do post, envia uma notificação
    if ($post_owner != $user_id) {
        $sql = "SELECT username FROM users WHERE ID = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['user_id' => $_SESSION['user_id']]);
        $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];

        $message = "Usuário $username comentou no seu post.";
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, sender_id, post_id, message, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$post_owner, $user_id, $post_id, $message]);
    }

    echo json_encode(["message" => "Comentário adicionado."]);
} else {
    echo json_encode(["error" => "Erro ao adicionar o comentário."]);
}
exit;