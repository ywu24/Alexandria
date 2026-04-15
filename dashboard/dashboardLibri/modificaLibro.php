<!DOCTYPE html>
<html lang="en">
<head>
	<title>Modifica libro</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container">
		<h2>Modifica libro</h2>
		<?php
		// Connessione al database
		$table = "Opera";
		require_once("../../utils/connect.php");
		
		// Recupero l'isbn del libro selezionato dalla pagina precedente
		$id = $_GET['id'];
		
		// Recupero i dati del libro dal database
		$sql = "SELECT * FROM $table WHERE ISBN = $id";
		$result = $conn->query($sql);
		if ($result->num_rows > 0) {
			$row = $result->fetch_assoc();
			$titolo = $row["Nome"];
			$autore = $row["Autore"];
			$genere = $row["Genere"];
			$desc = $row["Descrizione"];
			$casaed = $row["CasaEditrice"];
			$annopub = $row["AnnoPubblicazione"];
			$isbn = $row["ISBN"];
		} else {
			echo "Nessun libro trovato";
		}
		
		// Chiudo la connessione al database
		$conn->close();
		?>
		<form action="salvaModificheLibro.php" method="post">
			<div class="form-group">
				<label for="titolo">Titolo:</label>
				<input type="text" class="form-control" id="titolo" name="titolo" value="<?php echo $titolo; ?>">
			</div>
			<div class="form-group">
				<label for="autore">Autore:</label>
				<input type="text" class="form-control" id="autore" name="autore" value="<?php echo $autore; ?>">
			</div>
			<div class="form-group">
				<label for="desc">Descrizione:</label>
				<textarea rows="5" class="form-control" id="desc" name="desc"><?php echo $desc; ?></textarea>
			</div>
			<div class="form-group">
				<label for="casaed">Casa editrice:</label>
				<input type="text" class="form-control" id="casaed" name="casaed" value="<?php echo $casaed; ?>">
			</div>
			<div class="form-group">
				<label for="annopub">Anno di pubblicazione:</label>
				<input type="text" class="form-control" id="annopub" name="annopub" value="<?php echo $annopub; ?>" maxlength="4">
            </div>
			<div class="form-group">
				<label for="genere">Genere</label>
				<select class="form-control" name="genere" id="genere">
					<option <?php if ($genere == "Romanzo Storico") {echo "selected";} ?> value="Romanzo Storico">Romanzo Storico</option>
					<option <?php if ($genere == "Giallo") {echo "selected";} ?> value="Giallo">Giallo</option>
					<option <?php if ($genere == "Biografia") {echo "selected";} ?> value="Biografia">Biografia</option>
					<option <?php if ($genere == "Avventura") {echo "selected";} ?> value="Avventura">Avventura</option>
					<option <?php if ($genere == "Azione") {echo "selected";} ?> value="Azione">Azione</option>
					<option <?php if ($genere == "Fantascienza") {echo "selected";} ?> value="Fantascienza">Fantascienza</option>
					<option <?php if ($genere == "Horros") {echo "selected";} ?> value="Horror">Horror</option>
					<option <?php if ($genere == "Umoristico") {echo "selected";} ?> value="Umoristico">Umoristico</option>
					<option <?php if ($genere == "Distopia") {echo "selected";} ?> value="Distopia">Distopia</option>
				</select>
			</div>
			<div class="form-group">
				<label for="isbn">ISBN:</label>
				<input type="text" class="form-control" id="isbn" name="isbn" value="<?php echo $isbn; ?>">
            </div>
		<input type="hidden" name="id" value="<?php echo $id; ?>">
		<button type="submit" class="btn btn-primary">Salva Modifiche</button>
	</form>
</div>
</body>
</html>

