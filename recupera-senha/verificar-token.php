<?php
session_start();
include_once '../backend/conexao.php';

if (!isset($_SESSION['token'])) {
    header('Location: ../index.php');
    exit();
}

$token = $_SESSION['token'];
?>


<!DOCTYPE html>
<html>
<head>
  <title>Recuperação de senha</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
    }
    .container {
      width: 400px;
      margin: 50px auto;
      height: 350px;
      padding: 40px;
      border: 1px solid #ccc;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      text-align: center;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .header h2 {
      text-align: center;
    }
    .header p {
      text-align: center;
      font-weight: 300;
      color: gray;
    }

    .form-group {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
    }
    .form-group label {
      display: block;
      margin-bottom: 8px;
    }
    .form-group input {
      width: 80%;
      height: 40px;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .btn-login {
      width: 100%;
      height: 40px;
      background-color: #4CAF50;
      margin-bottom: 50px;
      color: #fff;
      padding: 10px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-login:hover {
      background-color: #3e8e41;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
        <h2>Verificar token</h2>
        <p>Insira aqui o token que você recebeu no email informado.</p>
    </div>
    <form method="post">
      <div class="form-group">
        <label for="username">Verificar Token:</label>
        <input type="text" id="token" name="token" required>
      </div>
      <button class="btn-login" type="submit">Verificar</button>
    </form>
    </div>

    <?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $response = $_POST['token'];

      if ($response == $token) {

        header('Location: nova-senha.php');

      } else {

        echo '<p>Token inválido</p>';
      
      }
    }

    ?>
     
</body>
</html>