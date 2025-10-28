<?php
// header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");

$dsn = "mysql:host=localhost;dbname=alt2";
$user ="root";
$pass = "";
$option=array(
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES UTF8" //for arabic
);

try{
    $con = new PDO($dsn,$user,$pass,$option);
    $con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    include "functions.php";
}catch(PDOException $e){
echo $e->getMessage();
}