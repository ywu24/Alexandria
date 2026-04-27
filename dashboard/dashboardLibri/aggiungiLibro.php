<?php
session_start();
$root = "../..";

if (!isset($_SESSION['copia'])) {
	$_SESSION['copia'] = false;
}
$msg = "";

if (isset($_POST['submitOpera'])) {
	$_SESSION['copia'] = false;
	unset($_POST['submitOpera']);
} else if (isset($_POST['submitCopia'])) {
	$_SESSION['copia'] = true;
	unset($_POST['submitCopia']);
}

if (isset($_SESSION['libroEsiste'])) {
	if ($_SESSION['libroEsiste']) {
		if ($_SESSION['copia'] == false) {
			$_SESSION['copia'] = true;
			header("Location: aggiungiLibro.php");
			exit();
		}
		$msg = "<p class='successo'>Il libro esiste già nel DataBase</p>";
	} else {
		if ($_SESSION['copia'] == true) {
			$_SESSION['copia'] = false;
			header("Location: aggiungiLibro.php");
			exit();
		}
		$msg = "<p class= 'errore'>Il libro non è presente nel DataBase</p>";

	}
	unset($_SESSION['libroEsiste']);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
	<title>Aggiungi libro</title>
	<!-- Link ai file CSS di Bootstrap -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css"
		integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<link rel="stylesheet" href="../../css/styleDashboard.css">
	<link rel="stylesheet" href="../../css/messaggi.css">

</head>

<body>

	<div id="nav-placeholder">
		<?php
		require_once("../../nav/nav.php");
		echo $msg; ?>
	</div>
	<div class="container mt-5">

		<h1>Aggiungi libro</h1>

		<!-- Check -->
		<h2 style="margin-top: 25px; margin-bottom: 25px;">Controlla se il DataBase ha già i dati sul libro</h2>
		<form action="check.php" method="post">
			<div class="form-group">
				<label for="isbn-check">ISBN</label>
				<input type="text" name="isbn-check" id="isbn-check" class="form-control"
					style="width: 89%; display: inline;" required>
				<input type="submit" name="check" class="btn btn-secondary" value="Check"
					style="margin-bottom: 0.425rem; margin-right: 0;">
			</div>
		</form>

		<?php



		if ($_SESSION['copia']) {
			echo '<form action="aggiungiLibro.php" method="post">
				<input type="submit" name="submitOpera" class="btn btn-secondary" value="Torna indietro"
					style="margin-bottom: 0.425rem; padding: 0.175rem 0.375rem;">
				</form>';
		} else {
			echo '<form action="aggiungiLibro.php" method="post">
			<label for="submitCopia">Il DataBase ha già i dati sul libro da aggiungere? Se si, si prega di cliccare
			</label>
			<input type="submit" id="submitCopia" name="submitCopia" class="btn btn-secondary" value="qui"
				style="margin-bottom: 0.425rem; padding: 0.175rem 0.375rem;">
			</form>';
		}
		?>


		<h2 style="margin-top: 25px; margin-bottom: 15px;">Inserisci le informazioni sul libro</h2>
		<form action="insertLibro.php" method="post" enctype="multipart/form-data">

			<div class="form-group">
				<label for="isbn">ISBN</label>
				<input type="text" name="isbn" id="isbn" class="form-control" required>
			</div>

			<?php


			if ($_SESSION['copia']) {
				echo '<div class="form-group">
						<label for="qty">Copie:</label>
						<input type="number" class="form-control" id="qty" name="qty" value="1" min="1" max="50" step="1" style="width: 10%; display: inline;" required>
					</div>
					<br>
					<div class="form-group">
						<input type="submit" name="btnCopia" class="btn btn-primary" value="Aggiungi libro">
					</div>';
			} else {
				echo '<div class="form-group">
				<label for="titolo">Titolo</label>
				<input type="text" name="titolo" id="titolo" class="form-control" required>
			</div>

			<div class="form-group">
				<label for="autore">Autore</label>
				<input type="text" class="form-control" id="autore" name="autore" required>
			</div>

			<div class="form-group">
				<label for="desc">Descrizione</label>
				<textarea rows="5" class="form-control" name="desc" required></textarea>
			</div>

			<div class="form-group">
				<label for="casaed">Casa Editrice</label>
				<input type="text" class="form-control" id="casaed" name="casaed" required>
			</div>

			<div class="form-group">
				<label for="annopub">Anno Pubblicazione</label>
				<input type="text" class="form-control" id="annopub" name="annopub" maxlength="4" required>

			</div>

			<div class="form-group">
				<label for="genere">Genere</label>
				<select class="form-control" name="genere" id="genere" required>
				<option value="Romanzo Storico">Romanzo Storico</option>
				<option value="Giallo">Giallo</option>
				<option value="Biografia">Biografia</option>
				<option value="Avventura">Avventura</option>
				<option value="Azione">Azione</option>
				<option value="Fantascienza">Fantascienza</option>
				<option value="Horros">Horror</option>
				<option value="Umoristico">Umoristico</option>
				<option value="Distopia">Distopia</option>
				</select>
			</div>

			<div class="form-group">
				<label for="qty">Copie:</label>
				<input type="number" class="form-control" id="qty" name="qty" value="1" min="1" max="50" step="1" style="width: 10%; display: inline;" required>
			</div>
			<br>
			<div class="form-group">
				<label class="titolo" for="image">Immagine di Copertina</label>
				<input type="file" name="image" id="">
				<input type="submit" name="btnOpera" class="btn btn-primary" value="Aggiungi libro">
			</div>';
			}
			?>

		</form>
	</div>
	<!-- Link ai file JavaScript di Bootstrap -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"
		integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN"
		crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"
		integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q"
		crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"
		integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl"
		crossorigin="anonymous"></script>
</body>

</html>