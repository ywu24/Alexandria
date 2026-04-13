<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
$root = '..';
require_once("../../auth/cookies.php");

if (isset($_GET['id'])) {
    require_once("../../utils/connect.php");
	$table = "Utente";
	

    $id = $_GET['id'];
    $email = $_SESSION["email"];
    $sql = "DELETE FROM $table WHERE id=$id AND Email!='$email'";
    try{
        $conn->query($sql);
        if($conn->affected_rows== 0){
            header("Location: dashboardUtenti.php?errore=2");
             die();
        }
        header("Location: dashboardUtenti.php?rimosso=1");
        
    } catch (mysqli_sql_exception $e) {
        header("Location: dashboardUtenti.php?errore=1");
    }
    finally{
        die();
    }
}else {
    header("Location: dashboardUtenti.php");
    die();
}

?>