<?php
session_start();
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifica se o usuário existe
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['ID']; // Armazena o ID do usuário na sessão
        $_SESSION['username'] = $user['username']; // Armazena o nome de usuário na sessão
        header('Location: ../user.php');
        exit();
    } else {
        echo "Usuário ou senha inválidos.";
    }
}
?>