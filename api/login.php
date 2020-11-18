<?php

$request_body = file_get_contents('php://input');
$request_data = json_decode($request_body, true);
header('Access-Control-Allow-Origin: "*"');

session_start(); // inicia a sessão
if(!empty($_SESSION["nick"])){
    echo '{"status": "logged_in"}';
    exit();
}
if((empty($request_data['email']) || empty($request_data['senha']))){
    http_response_code(400);
    echo '{"success": false, "error": "missing_parameter"}';
    exit();
}

$connect = new mysqli('localhost','root','', 'hackathon_db'); // conectar db
$email =  mysqli_real_escape_string($connect, $request_data['email']);
$senha = sha1($request_data['senha']);
$select = $connect->query("SELECT * FROM users WHERE email = '$email' AND senha = '$senha' LIMIT 1");
if($select->num_rows){
    $row = $select->fetch_assoc(); //  criar sessão e logar automaticamente
    $login_times = (int)$row["login_times"] + 1;
    $select = $connect->query("UPDATE users SET login_times=" . $login_times ." WHERE ID=" . $row['ID']); // AUMENTA QUANTAS VEZES FEZ LOGIN
    // echo("Usuário encontrado!<br> Olá, ". $row['nick']);
    $_SESSION["email"] = $email;
    $_SESSION["nick"] =  $row['nick'];
    $_SESSION["id"] =  $row['ID'];
    
    $response["nick"] = $row['nick'];
    $response["success"] = true;
    echo(json_encode($response));

}else{
    $response["success"] = false;
    $response["error"] = "incorrect_login";
    http_response_code(400);
    echo(json_encode($response));
}
//echo var_dump($select)
?>