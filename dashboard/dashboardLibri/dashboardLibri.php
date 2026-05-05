<?php
session_start();
$root = "../..";
require_once("$root/auth/cookies.php");
require_once("$root/utils/connect.php");

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
	header("Location: $root/index.php");
	exit;
}

$pdo = DatabaseConnection::getInstance()->getConnection();

// Funzione helper per la riga iniziale (stessa struttura di getLibri.php)
function renderRow($row)
{
	$isbn = $row['ISBN'];
	$btnElimina = ($row['copie'] == 0) ? "<button type='button' class='btn btn-danger btn-sm elimina'>Elimina</button>" : "";
	return "
    <tr data-isbn='$isbn'>
        <th scope='row'><button class='btn btn-sm btn-info btn-espandi' type='button' data-isbn='$isbn'>+</button> $isbn</th>
        <td>" . htmlspecialchars($row['Nome']) . "</td>
        <td>" . htmlspecialchars($row['Autore']) . "</td>
        <td>" . htmlspecialchars($row['Genere']) . "</td>
        <td>{$row['AnnoPubblicazione']}</td>
        <td>" . htmlspecialchars($row['CasaEditrice']) . "</td>
        <td>
            <input type='number' value='{$row['copie']}' class='form-control-sm' style='width:60px'>
            <button class='btn btn-outline-info btn-sm save'>Salva</button>
        </td>
        <td>
            <a class='btn btn-primary btn-sm' href='modificaLibro.php?id=$isbn'>Modifica</a>
            $btnElimina
        </td>
    </tr>
    <tr id='row-details-$isbn' style='display:none;' class='bg-light'>
        <td colspan='8'><div id='content-$isbn' class='p-3'>Caricamento...</div></td>
    </tr>";
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
	<meta charset="UTF-8">
	<title>Dashboard Libri</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?php echo $root; ?>/css/styleDashboard.css">
	<link rel="stylesheet" href="<?php echo $root; ?>/css/messaggi.css">
	<link rel="shortcut icon" href="<?php echo $root; ?>/immagini/bookDashFavicon.png">
	<script src="https://code.jquery.com/jquery-1.12.2.js"></script>
	<script src="aggiornaCopie.js" defer></script>
</head>

<body>
	<?php require_once("../../nav/nav.php"); ?>

	<div id="messages"></div>

	<div class="container-fluid">
		<h1 class="text-center" style="font-size:4rem !important;">&#128218;</h1>

		<div class="d-flex justify-content-between mb-4">
			
			<!-- Cerca gestito da JS -->
			<div class="form-inline mx-auto">
				<input class="form-control mr-sm-2 searchbar" type="search" id="searchInput" placeholder="Cerca libro...">
				<button class="btn btn-outline-info" id="searchBtn" type="button">Cerca</button>
			</div>
		</div>

		<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead class="thead-dark">
					<tr>
						<th scope="col">ISBN <button class="sort_btn" data-sort="ISBN">▲</button></th>
						<th scope="col">Titolo <button class="sort_btn" data-sort="Nome">▲</button></th>
						<th scope="col">Autore <button class="sort_btn" data-sort="Autore">▲</button></th>
						<th scope="col">Genere <button class="sort_btn" data-sort="Genere">▲</button></th>
						<th scope="col">Anno <button class="sort_btn" data-sort="AnnoPubblicazione">▲</button></th>
						<th scope="col">Casa Editrice <button class="sort_btn" data-sort="CasaEditrice">▲</button></th>
						<th scope="col">Copie <button class="sort_btn" data-sort="copie">▲</button></th>
						<th scope="col">Azioni <a href="aggiungiLibro.php" class="btn btn-success btn-sm">Aggiungi</a></th>
					</tr>
				</thead>
				<tbody id="libriTableBody">
					<?php
					$q = $pdo->query("SELECT Opera.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, COUNT(idCopia) as copie 
                                      FROM Opera LEFT JOIN copiaLibro ON Opera.ISBN = copiaLibro.ISBN 
                                      GROUP BY Opera.ISBN ORDER BY Nome ASC");
					while ($row = $q->fetch(PDO::FETCH_ASSOC)) echo renderRow($row);
					?>
				</tbody>
			</table>
		</div>
	</div>
	
		<div class="text-center my-4">
		<button id="loadMoreBtn" class="btn btn-outline-primary shadow-sm" style="display:none;">
			Carica Altro...
		</button>
		</div>
</body>

</html>