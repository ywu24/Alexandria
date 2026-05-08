<?php
session_start(); //non togliere


//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2) {
	header("Location: ../../lista/lista.php");
	exit;
}

?>


<!DOCTYPE html>
<html>

<head>
	<title>Gestione Utente</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<link rel="stylesheet" href="../../css/dettaglioUtenti.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="dettaglioUtente.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="../../css/colors.css">
	<link rel="stylesheet" href="../../css/prenotazione.css">
	<link rel="stylesheet" href="../../css/messaggi.css">

	<!--script per importare parti di codice-->
	<script src="https://code.jquery.com/jquery-1.12.2.js"></script>
	<script src="dettaglioPrenotazione.js"></script>
</head>

<body>

	<div id="nav-placeholder"></div>
	<?php
	$root = "../..";
	require_once("../../utils/connect.php");
	require_once("../../nav/nav.php");
	?>
	<div id="messages">
		<?php
		if (isset($_GET['eliminato'])) {
			echo "<p class= 'successo'> Prenotazione eliminata con successo</p>";
		}
		?>
	</div>
	<div class="container">
		<?php

		$table = "Prenotazione";
		$table1 = "copiaLibro";
		$table2 = "Opera";

		if (!isset($_GET['id'])) {
			header("dashboardUtenti.php");
			exit;
		}
		// Recupero l'ID dell'utente selezionato dalla pagina precedente
		$id = $_GET['id'];

		try {
			$pdo = DatabaseConnection::getInstance()->getConnection();
		} catch (PDOException $e) {
			echo "Errore durante la connessione al database: " . $e->getMessage();
			exit;
		}

		// Recupero i dati dell'utente dal database
		$sql = "SELECT * FROM Utente WHERE id = :id";
		if ($query = $pdo->prepare($sql)) {
			$query->bindParam(':id', $id);
			$query->execute();
			$row = $query->fetch();
			$query->closeCursor();

			if ($row) {
				$nome = $row["Nome"];
				$cognome = $row["Cognome"];
				$email = $row["Email"];
				$ruolo = $row["Utenza"];
				$propic = $row["propic"];
			} else {
				header("Location: dashboardUtenti.php?errore=3");
				exit;
			}
			if (empty($propic)) {
				$propic = "userDashFavicon.png";
			}
			echo "
		
		<div class='user-details'>
			<img src='../../img/users/" . $propic . "'>
			<div class='user-status'>
			<h2> Prenotazioni di " . $nome . " " . $cognome . "</h2>
			<br>
			<h3>" . $email . " • " . " • Punti: " . $row['punteggio'] . "</h3>
			</div>
			</div>

		";
		} else {
			throw new Exception("Errore nella preparazione della query: " . $pdo->errorInfo()[2]);
		}

		$query_base = "SELECT idPrenotazione, Copertina, $table.idCopia, InizioPrenotazione, 
		FinePrenotazione, InizioPrestito, FinePrestito, FineAttesa, Autore, Nome, CasaEditrice 
		FROM $table, $table1, $table2 
		WHERE $table1.idCopia = $table.idCopia 
		AND $table2.ISBN = $table1.ISBN 
		AND $table.Email = :email 
		AND finePrestito IS NULL
		ORDER BY $table.idPrenotazione DESC";
		if ($query = $pdo->prepare($query_base)) {
			$query->bindParam(':email', $email);
			$query->execute();


			foreach ($query->fetchAll() as $row) {
				$inizio = "";
				$fine = "";
				if ($row["InizioPrestito"] == NULL) {
					if (strtotime($row['FinePrenotazione']) < time()) {
						###BISOGNA FARE LA DELETE DELLA PRENOTAZIONE DAL DB
					} else {
						$stato = "Prenotato";
						$color = "green";#"#ff7600";
						$inizio = $row["InizioPrenotazione"];
						$fine = $row["FinePrenotazione"];
					}
				} else {
					if ($row['FinePrestito'] == NULL) {
						if (time() > strtotime($row['FineAttesa'])) {
							$stato = "In Ritardo";
							$color = "red";
							$inizio = $row["InizioPrestito"];
							$fine = "attesa = " . $row["FinePrenotazione"];
						} else {
							$stato = "In Prestito";
							$color = "orange";
							$inizio = $row["InizioPrestito"];
							$fine = "attesa = " . $row["FinePrenotazione"];
						}
					} else {
						$stato = "Terminato";
					}

				}


				echo "
    <div class='book-container' style='background:#fff; border-radius:8px; padding:15px; margin-bottom:15px; border:1px solid #eee;'>
        <div class='row'>
            <!-- Copertina -->
            <div class='col-sm-2'>
                <img src='../../img/books/" . $row['Copertina'] . "' class='img-responsive' style='max-height:140px;'>
            </div>
            
            <!-- Info Libro -->
            <div class='col-sm-6'>
                <h3 style='margin-top:0;'>" . $row['Nome'] . "</h3>
                <p class='text-muted'>" . $row['Autore'] . " | " . $row['CasaEditrice'] . "</p>
                <p>Stato: <b style='color: $color'>$stato</b></p>
                
                <button class='btn btn-primary' onclick='apriDettaglioPrenotazione(" . $row['idPrenotazione'] . ")'>
                    Gestisci
                </button>
            </div>
            
            <!-- IL DESTINATARIO (targetDiv): deve avere questo ID esatto -->
            <div class='col-sm-4' id='dettaglio-content-" . $row['idPrenotazione'] . "' style='display:none; border-left: 2px solid #f0f0f0; min-height: 140px;'>
                <!-- Qui il JS inietterà i dati -->
            </div>
        </div>
    </div>";
			}
			$query->closeCursor();
		} else {
			echo "Errore nella preparazione della query.";
		}


		echo '<hr>
				<div id="terminate-container">
						<button id="load-terminated" class="btn btn-secondary" data-id-utente=' . $id . '>
							Mostra prenotazioni terminate
						</button>
				</div>';
		?>



	</div>
</body>

</html>