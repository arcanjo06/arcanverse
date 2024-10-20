<?php
session_start();
include_once '../backend/conexao.php';

if (!isset($_SESSION['token'])) {
    header('Location: ../index.php');
    exit();
}

$token = $_SESSION['token'];
$sql="SELECT * FROM tokens WHERE token  = :token";
$result = $conn->prepare($sql);
$result->bindValue(':token', $token);
$result->execute();
$tokens = $result->fetch(PDO::FETCH_ASSOC);

$user_id = $tokens['user_id'];
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
        <h2>Troca de Senha</h2>
        <p>Insira aqui a sua nova senha.</p>
    </div>
    <form method="post">
      <div class="form-group">
        <label for="username">Nova Senha:</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button class="btn-login" type="submit">Enviar</button>
    </form>
    </div>

    <?php

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $newpassword = password_hash($_POST['password'], PASSWORD_DEFAULT);


      $stmt = $conn->prepare("UPDATE users SET password = :password WHERE ID = $user_id");
      $stmt->bindParam(':password', $newpassword);
      $stmt->execute();

      if ($stmt->rowCount() > 0) {
        echo '<p>Senha alterada com sucesso!</p>';
        $sql="DELETE FROM tokens WHERE user_id = :user_id";
        $result = $conn->prepare($sql);
        $result->bindParam(':user_id', $user_id);
        $result->execute();
        header('Location: ../index.php');
      }else{
        echo '<p>Erro ao alterar a senha</p>';
      }
    }

    ?>
    <script>
        document.querySelector('.btn-login').addEventListener('click', () => {
            if (document.getElementById('password').value === '') {
                alert('Por favor, informe sua senha.');
                return;
            }

        })

        console.log("<?php echo $newpassword ?>");
    </script>
     
</body>
</html>