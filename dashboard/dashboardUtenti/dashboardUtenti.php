
<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php

session_start();
require_once("../../utils/connect.php");
require_once("../../auth/cookies.php");
if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {

} else {
	header("Location: ../../index.php");
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="viewport" content="width=device-width, user-scalable=no,
	initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
	<title>Dashboard </title>
	<!-- Collegamento ai file CSS di Bootstrap -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="../../css/nav.css">
	<link rel="stylesheet" href="../../css/colors.css">
	<link rel="shortcut icon" href="../../img/userDashFavicon.png" type="image/x-icon">
	<script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
	<div id="nav-placeholder">
		 <?php 
		 	$root = "../../";
			require_once '../../nav/nav.php';
				 ?>
	</div>
	

	<div class="container-fluid">
		<h1 class="text-center" style="font-size:4rem !important;">👤</h1>
		<form class="form-inline mx-auto" style="width: 300px;" action="dashboardUtenti.php" method="post">
			<input class="form-control mr-sm-2 searchbar" type="search" name="search" placeholder="Ricerca un utente"
				aria-label="Cerca">
			<input class="btn btn-outline-info my-2 my-sm-0" name="search_btn" type="submit" value="Cerca">
		</form>
		<div class="table-responsive">
		<table class="table table-striped table-hover table-bordered">
			<thead class="thead-dark">
				<form action="dashboardUtenti.php" method="post">
					<?php
					if ($_SESSION['utenza'] == 1) {
						echo "
						<tr>
							<th data-field='#' scope='col' name='test'>#<button class='sort_btn' name='sort_id'>&ensp; &#x25B2;</button></th> 
							<th data-field='Nome' scope='col'>Nome<button class='sort_btn' name='sort_nome'>&ensp; &#x25B2;</button></th>
							<th data-field='Cognome' scope='col'>Cognome<button class='sort_btn' name='sort_cognome'>&ensp; &#x25B2;</button></th>
							<th data-field='Email' scope='col'>Email<button class='sort_btn' name='sort_email'>&ensp; &#x25B2;</button></th>
							<th data-field='Utenza' scope='col'>Ruolo<button class='sort_btn' name='sort_ruolo'>&ensp; &#x25B2;</button></th>
							<th data-field='Punteggio' scope='col'>Punteggio<button class='sort_btn' name='sort_punteggio'>&ensp; &#x25B2;</button></th>
							<th scope='col'>Azioni<a href='aggiungiUtente.php' class='btn btn_adduser btn-success ml-auto'>Aggiungi utente</a></th>
						</tr>";
					} else if ($_SESSION['utenza'] == 2) {
						echo "
						<tr>
							<th data-field='Nome' scope='col'>Nome<button class='sort_btn' name='sort_nome'>&ensp; &#x25B2;</button></th>
							<th data-field='Cognome' scope='col'>Cognome<button class='sort_btn' name='sort_cognome'>&ensp; &#x25B2;</button></th>
							<th data-field='Email' scope='col'>Email<button class='sort_btn' name='sort_email'>&ensp; &#x25B2;</button></th>
							<th data-field='Punteggio' scope='col'>Punteggio<button class='sort_btn' name='sort_punteggio'>&ensp; &#x25B2;</button></th>
							<th scope='col'><center>Azioni</center></th>
						</tr>";
					}




					?>

				</form>
			</thead>
			
			<tbody>
				<?php
				
				$table = "Utente";

				switch (true) {
					case isset($_POST['search_btn']):
						$search_text = $_POST['search'];
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email, punteggio, Utenza 
												FROM $table 
												WHERE Nome LIKE '%$search_text%' 
												OR Cognome LIKE '%$search_text%' 
												OR Email LIKE '%$search_text%' 
												OR Utenza LIKE '%$search_text%' 
												OR punteggio LIKE '%$search_text%' 
												OR id LIKE '%$search_text%' 
												OR concat(Nome,' ',Cognome) LIKE '%$search_text%' 
												OR concat(Cognome,' ',Nome) LIKE '%$search_text%'") 
												as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;

					case isset($_POST['sort_id']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email,  punteggio, Utenza FROM $table ORDER BY id") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;

					case isset($_POST['sort_nome']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email, punteggio, Utenza FROM $table ORDER BY Nome") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;

					case isset($_POST['sort_cognome']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email, punteggio, Utenza FROM $table ORDER BY Cognome") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;

					case isset($_POST['sort_email']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email,  punteggio, Utenza FROM $table ORDER BY Email") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;


					case isset($_POST['sort_ruolo']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email,  punteggio, Utenza FROM $table ORDER BY Utenza") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;
					case isset($_POST['sort_punteggio']):
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email,  punteggio, Utenza FROM $table ORDER BY punteggio desc") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;

					default:
						try {
							foreach ($conn->query("SELECT id, Nome, Cognome, Email,  punteggio, Utenza FROM $table ORDER BY id") as $row) {
								if ($_SESSION['utenza'] == 1) {
									printUtenti($row);
								} else if ($_SESSION['utenza'] == 2) {
									printUtentiB($row);
								}
							}
						} catch (PDOException $e) {
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						break;
				}
				function printUtenti(&$row)
				{
					if ($row['Utenza'] == 1) {
						$desc_utenza = "Admin";
					} else if ($row['Utenza'] == 2) {
						$desc_utenza = "Bibliotecario";
					} else if ($row['Utenza'] == 3) {
						$desc_utenza = "Docente";
					} else if ($row['Utenza'] == 4) {
						$desc_utenza = "Studente";
					}
					echo "<tr>
                    <th scope='row'>" . $row['id'] . "</th>
                    <td>" . $row['Nome'] . "</td>
                    <td>" . $row['Cognome'] . "</td>
                    <td>" . $row['Email'] . "</td>
					<td>" . $desc_utenza . "</td>
					<td>" . $row['punteggio'] . "</td>
                    <td>
                        <div class='btn_actions'>
                        <a class='btn btn-primary' href='modificaUtente.php?id=" . $row['id'] . "'>Modifica</a>
                        <a class='btn btn-danger' href='eliminaUtente.php?id=" . $row['id'] . "'>Elimina</a>
                        </div>
                    </td>
                    </tr>";
				}

				function printUtentiB(&$row)
				{
					echo "<tr>
                    <td>" . $row['Nome'] . "</td>
                    <td>" . $row['Cognome'] . "</td>
                    <td>" . $row['Email'] . "</td>
					<td>" . $row['punteggio'] . "</td>
                    <td>
                        <div class='btn_actions'>
                        <center><a class='btn btn-primary' href='dettaglioUtente.php?id=" . $row['id'] . "'>Prenotazioni</a></center>
                        </div>
                    </td>
                    </tr>";
				}				
				?>
			</tbody>
		</table>
		</div>
	</div>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
</body>

</html>