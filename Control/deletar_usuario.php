<?php

include('../views/conexao.php');
$id = intval($_GET['id']);

// REMOVER USUARIO PUXANDO ID DO GET
$sql_code = "DELETE FROM clientes WHERE id = '$id'";
$query_code = $mysqli->query($sql_code) or die($mysqli->error);    
    if($query_code) 
        header("location: ../views/listagem_usuarios.php");
?>
