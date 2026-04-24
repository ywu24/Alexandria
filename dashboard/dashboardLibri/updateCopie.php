<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$root = "../..";
$table1 = "Opera";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");
$table2 = "copiaLibro";

if (isset($_POST['copie']) && isset($_POST['isbn'])) {

	$isbn = $_POST['isbn'];
	$nuoveCopie = (int) $_POST['copie'];
	if ($nuoveCopie < 0) {
		echo "ERRORE: il numero inserito deve essere positivo o al più uguale zero!";
		exit;
	}

	try {
		$pdo = DatabaseConnection::getInstance()->getConnection();
	} catch (PDOException $e) {
		echo "Errore durante la connessione al database: " . $e->getMessage();
		exit;
	}
	
	$sql1 = "SELECT ISBN, COUNT(idCopia) as copie FROM $table2 WHERE ISBN=:isbn GROUP BY ISBN";
	try {
		$query = $pdo->prepare($sql1);
		$query->bindParam(':isbn', $isbn);
		$query->execute();
		$result = $query->fetch();
		$vecchieCopie = 0;
		if (!$result) {
			#echo "ERRORE: libro non trovato!"; 
			#die();
		} else {
			$vecchieCopie = (int) $result['copie'];
		}

		$differenza = $nuoveCopie - $vecchieCopie;
		if ($differenza > 0) {
			$sql2 = "INSERT INTO $table2 (ISBN, stato) VALUES(:isbn, 1)";
			try {
				for ($i = 0; $i < $differenza; $i++) {
					$query = $pdo->prepare($sql2);
					$query->bindParam(':isbn', $isbn);
					$query->execute();
				}
				echo "okInseriti " . $differenza . " libri con successo!";
			} catch (exception $e) {
				throw new Exception("ERRORE: " .  $e->getMessage());
			}

		} else if ($differenza < 0) {

			$sql2 = "DELETE FROM $table2 WHERE ISBN=:isbn and stato =1 LIMIT " . abs($differenza);
			try {
				$query = $pdo->prepare($sql2);
				$query->bindParam(':isbn', $isbn);
				$query->execute();
				if ($query->rowCount() != abs($differenza)) {
					echo "WARNING: eliminati solo " . $query->rowCount() . " su " . abs($differenza) . " richiesti: non puoi eliminare libri in prestito!";
				} else {
					echo "okEliminati " . abs($differenza) . " libri con successo!";
				}
			} catch (exception $e) {
				throw new Exception("ERRORE: " .  $e->getMessage());
			}

		} else {
			echo "ignora";
		}

	} catch (Exception $e) {
		throw new Exception("ERRORE: libro non trovato " . $e->getMessage());
	}
}
?>