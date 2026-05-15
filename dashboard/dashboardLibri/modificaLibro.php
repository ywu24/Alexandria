<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Modifica libro</title>
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
	<div id="nav-placeholder"><?php
	$root = "../..";
	require_once("../../nav/nav.php");
	?></div>
	<div class="container">
		<h2>Modifica libro</h2>
		<?php
		// Connessione al database
		$table = "Opera";
		require_once("../../utils/connect.php");
		try {
			$pdo = DatabaseConnection::getInstance()->getConnection();
		} catch (PDOException $e) {
			echo "Errore durante la connessione al database: " . $e->getMessage();
			exit;
		}

		// Recupero l'isbn del libro selezionato dalla pagina precedente
		$id = $_GET['id'];

		// Recupero i dati del libro dal database
		$q1 = "SELECT * FROM $table WHERE ISBN = :id";
		$query = $pdo->prepare($q1);
		$query->bindParam(':id', $id);
		$query->execute();
		$result = $query->fetch();
		if ($result) {
			$titolo = $result["Nome"];
			$autore = $result["Autore"];
			$genere = $result["Genere"];
			$desc = $result["Descrizione"];
			$casaed = $result["CasaEditrice"];
			$annopub = $result["AnnoPubblicazione"];
			$isbn = $result["ISBN"];
		} else {
			echo "Nessun libro trovato";
		}

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
				<input type="text" class="form-control" id="annopub" name="annopub" value="<?php echo $annopub; ?>"
					maxlength="4">
			</div>
			<div class="form-group">
				<label for="genere">Genere</label>
				<select class="form-control" name="genere" id="genere">
					<option <?php if ($genere == "Romanzo Storico") {
						echo "selected";
					} ?> value="Romanzo Storico">
						Romanzo Storico</option>
					<option <?php if ($genere == "Giallo") {
						echo "selected";
					} ?> value="Giallo">Giallo</option>
					<option <?php if ($genere == "Biografia") {
						echo "selected";
					} ?> value="Biografia">Biografia</option>
					<option <?php if ($genere == "Avventura") {
						echo "selected";
					} ?> value="Avventura">Avventura</option>
					<option <?php if ($genere == "Azione") {
						echo "selected";
					} ?> value="Azione">Azione</option>
					<option <?php if ($genere == "Fantascienza") {
						echo "selected";
					} ?> value="Fantascienza">Fantascienza
					</option>
					<option <?php if ($genere == "Horros") {
						echo "selected";
					} ?> value="Horror">Horror</option>
					<option <?php if ($genere == "Umoristico") {
						echo "selected";
					} ?> value="Umoristico">Umoristico
					</option>
					<option <?php if ($genere == "Distopia") {
						echo "selected";
					} ?> value="Distopia">Distopia</option>
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
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>