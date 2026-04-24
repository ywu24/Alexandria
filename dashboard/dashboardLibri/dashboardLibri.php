<?php
//LEVARE QUESTA SEZIONE dopo, ma per debuggare serve!!
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
$root = "../..";
$table1 = "Opera";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");
if ($_SESSION['utenza'] == 1 || $_SESSION['utenza'] == 2) {


} else {
	header("Location: ../../index.php");
	exit;
}

try {
	$pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
	echo "Errore durante la connessione al database: " . $e->getMessage();
	exit;
}


if (isset($_POST['createDummy'])) {
	$isbnDummy = rand(1000000000000, 9999999999999);
	$q1 = "INSERT INTO Opera (`ISBN`, `Nome`, `Autore`, `Genere`, `Descrizione`, `Copertina`, `CasaEditrice`, `AnnoPubblicazione`)
		VALUES (:isbn, 'Dummy', 'Dummy', 'Umoristico', 'DummyDummyDummy',  'default.jpg', 'Dummy', 1984)";

	$q2 = "INSERT INTO copiaLibro (`ISBN`, `Stato`) VALUES (:isbn, '1')";

	$query1 = $pdo->prepare($q1);
	$query1->bindParam(':isbn', $isbnDummy);

	$query2 = $pdo->prepare($q2);
	$query2->bindParam(':isbn', $isbnDummy);

	if ($query1->execute() && $query2->execute()) {
		$query1->closeCursor();
		$query2->closeCursor();
		$_SESSION['success_msg'] = "Dummy aggiunto al database";
		header("Location: dashboardLibri.php");
		exit;
	} else {
		$query1->closeCursor();
		$query2->closeCursor();
		echo "<p class = 'errore'> Errore: " . $pdo->errorInfo()[2] . "</p>";
		header("Location: ../dashboard.php");
		exit;
	}
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="viewport" content="width=device-width, user-scalable=no,
	initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0">
	<title>Dashboard Libri</title>
	<!-- Collegamento ai file CSS di Bootstrap -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="../../css/styleDashboard.css">
	<script src="aggiornaCopie.js"></script>
	<link rel="stylesheet" href="../../css/nav.css">
	<link rel="stylesheet" href="../../css/colors.css">
	<link rel="stylesheet" href="../../css/popup.css">
	<link rel="shortcut icon" href="../../immagini/bookDashFavicon.png" type="image/x-icon">
	<script src="https://code.jquery.com/jquery-1.12.2.js"></script>
</head>

<body>
	<div id="nav-placeholder"><?php

	require_once("../../nav/nav.php");
	?></div>
	<div id="messages">
		<?php
		if (isset($_SESSION['success_msg'])) {
			echo '<p class= "successo">' . $_SESSION["success_msg"] . '</p>';
			unset($_SESSION['success_msg']);
		} else if (isset($_SESSION['error_msg'])) {
			echo '<p class="errore">' . $_SESSION["error_msg"] . '</p>';
			unset($_SESSION['error_msg']);
		}

		if (isset($_GET['errore'])) {
			if ($_GET['errore'] == 1) {
				echo "<p class= 'errore'> Errore nell'update</p>";
			}
		}
		?>
	</div>
	<div class="container-fluid">
		<h1 class="text-center" style="font-size:4rem !important;">&#128218;</h1>
		<br>

		<!-- INSERT DI LIBRO RANDOM -->

		<form action="dashboardLibri.php" method='POST'>
			<button class='btn btn-secondary' type='submit' name='createDummy'>CREA LIBRO RANDOM</button>
		</form>




		<form class="form-inline mx-auto" style="width: 300px;" action="dashboardLibri.php" method="post">
			<input class="form-control mr-sm-2 searchbar" type="search" name="search" placeholder="Ricerca un libro..."
				aria-label="Cerca">
			<input class="btn btn-outline-info my-2 my-sm-0" name="search_btn" type="submit" value="Cerca">
		</form>
		<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead class="thead-dark">
					<form action="dashboardLibri.php" method="post">
						<tr>
							<th scope="col" name="test">ISBN<button class="sort_btn" name="sort_isbn">&ensp;
									&#x25B2;</button></th>
							<th scope="col">Titolo<button class="sort_btn" name="sort_nome">&ensp; &#x25B2;</button>
							</th>
							<th scope="col">Autore<button class="sort_btn" name="sort_autore">&ensp; &#x25B2;</button>
							</th>
							<th scope="col">Genere<button class="sort_btn" name="sort_genere">&ensp; &#x25B2;</button>
							</th>
							<th scope="col">Anno<button class="sort_btn" name="sort_anno">&ensp; &#x25B2;</button></th>
							<th scope="col">Casa Editrice<button class="sort_btn" name="sort_casaeditrice">&ensp;
									&#x25B2;</button></th>
							<th scope="col">Copie<button class="sort_btn" name="sort_copies">&ensp; &#x25B2;</button>
							</th>
							<th scope="col">Azioni <div class="btn_adduser"><a href="aggiungiLibro.php"
										class="btn btn-success ml-auto">Aggiungi libro</a></div>
							</th>
						</tr>
					</form>
				</thead>
				<tbody>
					<?php


					$table2 = "copiaLibro";
					switch (true) {
						case isset($_POST['search_btn']):
							$search_text = $_POST['search'] ? '%' . $_POST['search'] . '%' : '%';
							try {

								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice,
														COUNT(idCopia) as copie
														FROM $table1
														LEFT JOIN $table2 ON $table1.ISBN = $table2.ISBN
														WHERE Nome LIKE ?
														OR Autore LIKE ?
														OR Genere LIKE ?
														OR AnnoPubblicazione LIKE ?
														OR CasaEditrice LIKE ?
														OR $table1.ISBN LIKE ?
														GROUP BY $table1.ISBN
													");

								$query->execute(array_fill(0, 6, $search_text));

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();

							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";

							}
							break;

						case isset($_POST['sort_isbn']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, COUNT(idCopia) 
														as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN ORDER BY $table1.ISBN");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";
							}
							break;

						case isset($_POST['sort_copies']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN=$table2.ISBN 
														GROUP BY $table1.ISBN ORDER BY copie DESC");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";

							}
							break;


						case isset($_POST['sort_nome']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN  ORDER BY Nome");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();

							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";

							}
							break;

						case isset($_POST['sort_autore']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN  ORDER BY Autore");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";

							}
							break;

						case isset($_POST['sort_genere']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN  ORDER BY Genere");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";
							}
							break;

						case isset($_POST['sort_anno']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN  ORDER BY AnnoPubblicazione");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);

								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";
							}

							break;

						case isset($_POST['sort_casaeditrice']):
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN  ORDER BY CasaEditrice");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";
							}
							break;

						default:
							try {
								$query = $pdo->prepare("SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, 
														COUNT(idCopia) as copie FROM $table1 LEFT JOIN $table2 on $table1.ISBN = $table2.ISBN 
														GROUP BY $table1.ISBN ");
								$query->execute();

								foreach ($query->fetchAll() as $row) {
									printLibri($row);
								}
								$query->closeCursor();
							} catch (PDOException $e) {
								print "Error!: " . $e->getMessage() . "<br/>";
							}
					}

					function printLibri(&$row)
					{
						$isbn = $row['ISBN'];
						echo "
					<tr data-isbn='$isbn'>
						<th scope='row'>
							<button class='btn btn-sm btn-info btn-espandi' type='button' data-isbn='$isbn'>
								+
							</button> 
							$isbn
						</th>
						<td>" . $row['Nome'] . "</td>
						<td>" . $row['Autore'] . "</td>
						<td>" . $row['Genere'] . "</td>
						<td>" . $row['AnnoPubblicazione'] . "</td>
						<td>" . $row['CasaEditrice'] . "</td>
						<td>
							<input type='number' value='" . $row['copie'] . "' class='form-control-sm' style='width:60px'>
							<button class='btn btn-outline-info btn-sm save'>Salva</button>
						</td>
						<td>
							<a class='btn btn-primary btn-sm' href='modificaLibro.php?id=$isbn'>Modifica</a>
						</td>
					</tr>
					<tr id='row-details-$isbn' style='display:none;' class='bg-light'>
						<td colspan='8'>
							<div id='content-$isbn' class='p-3'>
								Caricamento in corso...
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