<?php
session_start();
$root="../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");

if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {
} else {
	header("Location: ../../index.php");
}
if(isset($_POST['id'])){
   
    $id =(int) $_POST['id'];
    try{
        $sql = "DELETE FROM copiaLibro WHERE idCopia= $id AND Stato=1";
        $result = $conn->query($sql);
        if ($conn->affected_rows <= 0) {
             echo "Errore nell'eliminazione del libro con id ".$id;
             $conn->close();
             die();

        }
        echo "ok Libro con id " . $id . " eliminato con successo!";        
    }
    catch(Exception $e){
        echo "Errore nell'eliminazione del libro con id ".$id . ", " . $e;
    } finally{
        
        $conn->close();
    }
}
?>