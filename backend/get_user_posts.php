<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Função para obter os posts do usuário
function get_user_posts($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY posts.created_at DESC"); // Substitua `user_id` pela coluna correspondente
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtendo os posts do usuário
$userPosts = get_user_posts($conn, $user_id); 

function getUserNameById($userId) {
    $query = "SELECT username FROM users WHERE ID = '$userId'";
    $conn = mysqli_connect('localhost', 'root', '', 'arcanverse');
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['username'];
}
?>