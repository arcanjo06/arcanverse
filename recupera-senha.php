
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
        <h2>Recuperação de senha</h2>
        <p>Iremos enviar um email para a conta informada com um link para redefinir sua senha.</p>
    </div>
    <form method="post">
      <div class="form-group">
        <label for="username">Digite seu email:</label>
        <input type="email" id="email" name="email" required>
      </div>
      <button class="btn-login" type="submit">Recuperar senha</button>
    </form>

    <?php 
    session_start();
    include 'backend/conexao.php';

    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
      $email = $_POST['email'];

      $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // E-mail encontrado, gerar token
        $token = bin2hex(random_bytes(3)); // Gerar token de redefinição

        // Inserir o token no banco de dados
        $stmt = $conn->prepare("INSERT INTO tokens (user_id, token, created_at) VALUES (:user_id, :token, :created_at)");
        $stmt->bindValue(':user_id', $result['ID']);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        $stmt->execute();


        $to = $email;
        $subject = 'Redefinir senha';

        $mail = new PHPMailer(true);
        try {
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->SMTPAuth = true;
          $mail->Username = 'arcanverse0@gmail.com';
          $mail->Password = 'kbsg gzyc bdzt tcry';
          $mail->SMTPSecure = 'tls';
          $mail->Port = 587;
           // Remetente e destinatário
           $mail->setFrom('arcanverse0@gmail.com', 'ArcanVerse');
           $mail->addAddress($email);

           // Conteúdo do e-mail
           $mail->isHTML(true);
           $mail->Subject = 'Redefinir senha';
           $mail->Body    = "
               <p>Você solicitou uma redefinição de senha. Aqui está seu token de recuperação: $token</p>
               <p>Se você não fez essa solicitação, ignore este e-mail.</p>
           ";

           // Enviar e-mail
           $mail->send();
           $_SESSION['token'] = $token;
           echo '<p>Se este e-mail estiver registrado, enviaremos um link de recuperação de senha.</p>';
           header( 'Location:recupera-senha/verificar-token.php');
       } catch (Exception $e) {
           echo "Erro ao enviar o e-mail: {$mail->ErrorInfo}";
       }
   } else {
       // Por segurança, você pode exibir a mesma mensagem independentemente de o e-mail existir ou não.
       echo '<p>Se este e-mail estiver registrado, enviaremos um link de recuperação de senha.</p>';
   }
}


    

    
    ?>
  </div>
  <script>
    document.querySelector('.btn-login').addEventListener('click', () => {
      alert('Você será redirecionado para a página para recuperar sua senha.');
      window.location.href = 'recupera-senha/verificar-token.php'; // Caminho para a página do feed
    });

  </script>
</body>
</html>


