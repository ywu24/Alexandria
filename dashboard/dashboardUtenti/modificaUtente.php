<?php
session_start(); //non togliere

//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['error'])) {
	$messaggio = "";
	if ($_GET['error'] === '1'):
		$messaggio = "Attenzione: non puoi lasciare campi obbligatori vuoti!";
	elseif ($_GET['error'] === '2'):
		$messaggio = " Attenzione: update fallito! assicurati che l'utente non collida con uno esistente e che i nuovi dati inseriti siano consistenti";
	endif;
	echo '<p class="errore">' . $messaggio . '</p>';
} ?>

<!DOCTYPE html>
<html>

<head>
	<title>Gestione Utente</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../css/design-system.css">
	<link rel="stylesheet" href="../../css/components.css">
	<link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/forms.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="form-page">
	<div id="nav-placeholder">
		<?php
		$root = "../../";
		require_once('../../nav/nav.php');
		?>
	</div>
	<div class="container">
		<h2>Gestione Utente</h2>
		<?php
		// Recupero l'ID dell'utente selezionato dalla pagina precedente
		if (!isset($_GET['id'])) {
			header("Location: dashboardUtenti.php?errore=default");
			exit();
		}

		$id = $_GET['id'];
		$table = "utente";
		require_once("../../utils/connect.php");

		try {
			$pdo = DatabaseConnection::getInstance()->getConnection();
		} catch (PDOException $e) {
			echo "Errore durante la connessione al database: " . $e->getMessage();
			exit;
		}

		// Recupero i dati dell'utente dal database
		if ($query = $pdo->prepare("SELECT * FROM Utente WHERE id = :id")) {
			$query->bindParam(':id', $id);
			$query->execute();
			$row = $query->fetch();
			$query->closeCursor();
			if ($row) {
				$nome = $row["Nome"];
				$cognome = $row["Cognome"];
				$email = $row["Email"];
				$ruolo = $row["Utenza"];
			} else {
				echo "<p class='errore'> utente non trovato </p>";
			}
		} else {
			echo "<p class='errore'> errore nella preparazione della query </p>";
		}


		?>
		<form action="salvaModifiche.php" method="post">
			<div class="form-group">
				<label for="nome">Nome:</label>
				<input type="text" class="form-control" id="nome" name="nome" value="<?php echo $nome; ?>">
			</div>
			<div class="form-group">
				<label for="cognome">Cognome:</label>
				<input type="text" class="form-control" id="cognome" name="cognome" value="<?php echo $cognome; ?>">
			</div>
			<div class="form-group">
				<label for="email">Email:</label>
				<input type="text" class="form-control" id="email" name="email" value="<?php echo $email; ?>">
			</div>
			<div class="form-group">
				<label for="ruolo">Ruolo:</label>
				<select class="form-control" id="ruolo" name="ruolo">
					<option <?php if ($row["Utenza"] == 4) {
						echo "selected";
					} ?> value="4">Cittadino</option>
					<option <?php if ($row["Utenza"] == 3) {
						echo "selected";
					} ?> value="3">Docente</option>
					<option <?php if ($row["Utenza"] == 2) {
						echo "selected";
					} ?> value="2">Bibliotecario</option>
					<option <?php if ($row["Utenza"] == 1) {
						echo "selected";
					} ?> value="1">Admin</option>
				</select>
			</div>
			<div class="form-group">
				<label for="password">Password (Opzionale):</label>
				<input type="password" class="form-control" id="password" name="password" value=""
					placeholder="Opzionale. Lasciare vuoto se non si intende cambiare password.">
			</div>
			<input type="hidden" name="id" value="<?php echo $id; ?>">
			<button type="submit" class="btn btn-primary">Salva Modifiche</button>
		</form>
    </div>
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>