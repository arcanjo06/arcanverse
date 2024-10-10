<?php
// Inclua o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Consulte o banco de dados para obter os posts
$stmt = $conn->prepare("SELECT posts.ID, posts.content, posts.created_at, users.username FROM posts JOIN users ON posts.user_id = users.ID ORDER BY posts.created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
