<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root= "../..";
$table1 = "Opera";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");
$table2 = "copiaLibro";

if(isset($_POST['copie']) && isset($_POST['isbn'])){
	#echo "CIAOAOAAOAO";
	$isbn = $_POST['isbn'];
	$nuoveCopie = (int) $_POST['copie'];
	if($nuoveCopie<0){
		echo "ERRORE: il numero inserito deve essere positivo o al più uguale zero!";
		die();
	}
	$sql1 = "SELECT ISBN, COUNT(idCopia) as copie FROM $table2 WHERE ISBN='" . $isbn . "' GROUP BY ISBN";
	try{
		$result = $conn->query($sql1);
		$row = $result->fetch_assoc();
		$vecchieCopie = 0;
		if ($row==NULL){
			#echo "ERRORE: libro non trovato!"; 
			#die();
		} else{	
			$vecchieCopie = (int)$row['copie'];
		}
		
		$differenza = $nuoveCopie - $vecchieCopie;
		if($differenza>0){
			$sql2 = "INSERT INTO $table2 (ISBN, stato) VALUES('$isbn', 1)";
			try{
				for($i= 0; $i<$differenza; $i++ ){
					$conn->query($sql2);
					
				}
				echo "okinseriti " . $differenza . " libri con successo!";
			}
			catch(exception $e){
				echo "ERRORE: inserimento fallito"; 
			}
			
		}
		else if($differenza<0){
			
			$sql2 = "DELETE FROM $table2 WHERE ISBN='$isbn' and stato =1 LIMIT " . abs($differenza);
			try{
				$conn->query($sql2);
				if($conn->affected_rows!= abs($differenza)){
					echo "WARNING: eliminati solo ". $conn->affected_rows . " su " . abs($differenza) . " richiesti: non puoi eliminare libri in prestito!";
				}
				else{
					echo "okeliminati " . abs($differenza) . " libri con successo!";
				}
			}
			catch(exception $e){
				echo "ERRORE: nessun libro eliminato";
			}	
			
		}
		else{
			echo "ignora";
			
			}
		
	}
	catch (exception $e){
		echo "ERRORE: libro non trovato " . $e; 
	}
	finally{
		$conn->close();
	}
}
?>