<?php

if (isset($_GET['id'])) {
    require_once("../../utils/connect.php");
	$table = "Utente";
	

    $id = $_GET['id'];
    $sql = "DELETE FROM $table WHERE id=$id";
    try{
        $conn->query($sql);
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