<?php
$host = 'localhost';  // ou o endereço do seu servidor de banco de dados
$dbname = 'arcanverse'; // substitua pelo nome do seu banco de dados
$username = 'root'; // substitua pelo seu usuário do MySQL
$password = ''; // substitua pela sua senha do MySQL

try {
    // Cria a conexão
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Define o modo de erro do PDO para exceções
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // (Opcional) Define o charset para UTF-8
    $conn->exec("set names utf8");
} catch (PDOException $e) {
    // Captura e exibe erros de conexão
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
}
?>