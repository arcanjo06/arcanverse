<?php
session_start();
include 'conexao.php'; // Conexão com o banco de dados

header('Content-Type: application/json'); // Configura o cabeçalho para JSON

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->postId) && !empty(trim($data->postId))) {
        $postId = trim($data->postId);

        // Verifica se o usuário é o dono do post
        $stmt = $conn->prepare("SELECT * FROM posts WHERE ID = :postId AND user_id = :userId");
        $stmt->bindParam(':postId', $postId);
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Deleta o post do banco de dados usando PDO
            $stmt = $conn->prepare("DELETE FROM posts WHERE ID = :postId");
            $stmt->bindParam(':postId', $postId);

            if ($stmt->execute()) {
                echo json_encode(['message' => 'Post deletado com sucesso']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao deletar o post']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['error' => 'Usuário não é o dono do post']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID do post não fornecido ou vazio']);
    }
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Usuário não autorizado']);
}
?>