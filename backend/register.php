<?php
session_start();
require 'conexao.php';

// Função para verificar se o e-mail já existe no banco de dados
function email_exists($conn, $email) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?"); // Ajuste o nome da tabela se necessário
    $stmt->execute([$email]);
    return $stmt->fetchColumn() > 0; // Retorna true se o e-mail já existe
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica se os campos foram preenchidos
    if (empty($username) || empty($email) || empty($password)) {
        echo "Por favor, preencha todos os campos.";
        exit();
    }

    // Verifica se o e-mail já existe
    if (email_exists($conn, $email)) {
        echo "Este e-mail já está em uso. Por favor, use um e-mail diferente."; // Redireciona para a página de registro
        exit();
    }

    // Criptografar a senha
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Inserindo o novo usuário
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $hashed_password]);

    $user_id = $conn->lastInsertId();

    // Redireciona ou exibe uma mensagem de sucesso
    $_SESSION['username'] = $username;
    $_SESSION['user_id'] = $user_id;

    header('Location: ../user.php'); // Redireciona para a página do usuário
    exit();
}
?>