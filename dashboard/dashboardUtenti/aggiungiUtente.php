<?php
session_start();//non togliere


//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

	if (isset($_GET['error'])){
		$messaggio = "";
		if($_GET['error']==='1'):
			$messaggio = "Attenzione: tutti i campi sono obbligatori!";
		elseif($_GET['error']==='2'): 
			$messaggio=" Attenzione: inserimento fallito! assicurati che l'utente non esista già e che i dati inseriti siano consistenti";
		endif; 
 		echo '<p class="errore">'.$messaggio ;
	}?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Aggiungi utente</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../css/design-system.css">
	<link rel="stylesheet" href="../../css/components.css">
	<link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/forms.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
</head>
<body class="form-page">
	<div id="nav-placeholder">
		 <?php 
		 	$root = "../../";
			require_once('../../nav/nav.php');
				 ?>
	</div>
	<div class="container mt-5">
		<h1>Aggiungi utente</h1>
		<form action="insert.php" method="post">
			<div class="form-group">
				<label for="nome">Nome</label>
				<input type="text" name="nome" id="nome" class="form-control" required>
			</div>

            <div class="form-group">
				<label for="cognome">Cognome:</label>
				<input type="text" class="form-control" id="cognome" name="cognome" required>
			</div>

			<div class="form-group">
				<label for="email">Email / Nome utente</label>
				<input type="text" name="email" id="email" class="form-control" required>
			</div>


            <div class="form-group">
				<label for="ruolo">Ruolo:</label>
				<select class="form-control" id="ruolo" name="ruolo">
					<option value="4">Cittadino</option>
					<option value="3">Docente</option>
					<option value="2">Bibliotecario</option>
					<option value="1">Admin</option>
				</select>
			</div>

			<div class="form-group">
				<label for="password">Password</label>
				<input type="password" name="password" id="password" class="form-control" required>
			</div>
			<input type="submit" name="submit" class="btn btn-primary" value="Aggiungi utente">
		</form>
	</div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php require_once("../../nav/footer.php"); ?>
</body>
</html>
