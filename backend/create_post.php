<?php
session_start();
include 'conexao.php'; // Conexão com o banco de dados

header('Content-Type: application/json'); // Configura o cabeçalho para JSON

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->content) && !empty(trim($data->content))) {
        $content = trim($data->content);

        // Insere o novo post no banco de dados usando PDO
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, created_at) VALUES (:userId, :content, NOW())");
        $stmt->bindParam(':userId', $userId);
        $stmt->bindParam(':content', $content);

        if ($stmt->execute()) {
            $newPost = [
                'id' => $conn->lastInsertId(),
                'user_id' => $userId,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s'),
                'username' => $_SESSION['username'],
            ];
            echo json_encode($newPost);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar o post']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Conteúdo do post não fornecido ou vazio']);
    }
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Usuário não autorizado']);
}
?>