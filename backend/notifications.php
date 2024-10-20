<?php
// Inicia a sessão
session_start();
require 'conexao.php'; // Conexão com o banco de dados

// Função para obter notificações
function getNotifications($userId) {
    global $conn;
    $sql = "SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['user_id' => $userId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Função para marcar uma notificação como lida
function markNotificationAsRead($notificationId) {
    global $conn;
    $sql = "UPDATE notifications SET read = 1 WHERE ID = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['id' => $notificationId]);

    return $stmt->rowCount() > 0;
}

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit();
}

// Ação: Verificar o tipo de requisição (GET para obter notificações, POST para marcar como lida)
$action = $_SERVER['REQUEST_METHOD'];
if ($action === 'GET') {
    // Obter notificações
    $notifications = getNotifications($_SESSION['user_id']);
    echo json_encode($notifications);

} elseif ($action === 'POST') {
    // Marcar notificação como lida
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['ID']) && markNotificationAsRead($data['ID'])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Erro ao marcar a notificação como lida']);
    }
} else {
    echo json_encode(['error' => 'Método não permitido']);
}
?>