<?php
session_start();
$root = "../..";
require_once("../../auth/cookies.php");
require_once("../../utils/connect.php");

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
	header("Location: ../../index.php");
	exit;
}

try {
	$pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
	echo "Errore durante la connessione al database: " . $e->getMessage();
	exit;
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	<title>Dashboard Libri</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="../../css/design-system.css">
	<link rel="stylesheet" href="../../css/components.css">
	<link rel="stylesheet" href="../../css/layout.css">
    <link rel="stylesheet" href="../../css/navigation.css">
    <link rel="stylesheet" href="../../css/pages/dashboard.css">
    <link rel="stylesheet" href="../../css/pages/footer.css">
    <link rel="stylesheet" href="../../css/utilities.css">
	<link rel="icon" type="image/svg+xml" href="../../img/bookDashFavicon.svg">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="aggiornaCopie.js" defer></script>
</head>

<body class="dashboard-table">
	<div id="nav-placeholder">
		<?php require_once("../../nav/nav.php"); ?>
	</div>

	<div id="messages"></div>

	<div class="container-fluid">
		<h1 class="text-center" style="font-size:4rem !important;">&#128218;</h1>

		<div class="d-flex justify-content-between mb-4">

			<!-- Cerca gestito da JS -->
			<div class="form-inline mx-auto">
				<input class="form-control mr-sm-2 searchbar" type="search" id="searchInput"
					placeholder="Cerca libro...">
				<button class="btn btn-outline-info" id="searchBtn" type="button">Cerca</button>
			</div>
		</div>

		<?php if ($_SESSION['utenza'] == 1): ?>
			<a href="aggiungiLibro.php" class="btn btn_addlibro btn-success">Aggiungi Libro</a>
		<?php endif; ?>

		<div class="table-responsive">
			<table class="table table-striped table-hover">
				<thead class="thead-dark">
					<tr>
						<th scope="col">ISBN <button class="sort_btn" data-sort="ISBN">▲</button></th>
						<th scope="col">Titolo <button class="sort_btn" data-sort="Nome">▲</button></th>
						<th scope="col" class="col-nascondi">Autore <button class="sort_btn"
								data-sort="Autore">▲</button></th>
						<th scope="col" class="col-nascondi">Genere <button class="sort_btn"
								data-sort="Genere">▲</button></th>
						<th scope="col" class="col-nascondi">Anno <button class="sort_btn"
								data-sort="AnnoPubblicazione">▲</button></th>
						<th scope="col" class="col-nascondi">Casa Editrice <button class="sort_btn"
								data-sort="Genere">▲</button></th>
						<th scope="col" class="col-nascondi">Copie <button class="sort_btn" data-sort="copie">▲</button>
						</th>
						<th scope="col" class="col-nascondi">Azioni </th>
						<th scope="col" class="mobile-only">Info</th>
					</tr>
				</thead>
				<tbody id="libriTableBody">
					<!-- I dati verranno inseriti qui tramite JavaScript -->
				</tbody>
			</table>
		</div>
	</div>

    <div class="text-center my-4">
        <button id="loadMoreBtn" class="btn btn-outline-primary shadow-sm" style="display:none;">
            Carica Altro...
        </button>
    </div>
    <?php require_once("../../nav/footer.php"); ?>
</body>

</html>