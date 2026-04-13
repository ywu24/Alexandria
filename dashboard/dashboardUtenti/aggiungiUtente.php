<?php
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
	<!-- Link ai file CSS di Bootstrap -->
	<link rel="stylesheet" href= "style.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
</head>
<body>
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
	<!-- Link ai file JavaScript di Bootstrap -->
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>
