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

$stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, content, criado_em) VALUES (?, ?, ?, NOW())");
$stmt->execute([$data['post_id'], $_SESSION['user_id'], $data['content']]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["message" => "Comentário adicionado."]);
} else {
    echo json_encode(["error" => "Erro ao adicionar o comentário."]);
}
exit;
?>