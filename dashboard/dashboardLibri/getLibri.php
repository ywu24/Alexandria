<?php
session_start();
$root = "../..";
require_once("../../utils/connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    exit(json_encode([]));
}

$pdo = DatabaseConnection::getInstance()->getConnection();

$search = $_POST['search'] ?? '';
$sort = $_POST['sort_type'] ?? 'Nome';
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit = 10; // Quanti libri caricare per volta

$table1 = "Opera";
$table2 = "copiaLibro";

$allowed_sort = [
    'ISBN' => "$table1.ISBN", 'Nome' => 'Nome', 'Autore' => 'Autore', 
    'Genere' => 'Genere', 'AnnoPubblicazione' => 'AnnoPubblicazione', 
    'CasaEditrice' => 'CasaEditrice', 'copie' => 'copie'
];
$sort_column = $allowed_sort[$sort] ?? 'Nome';

$sql = "SELECT $table1.ISBN, Nome, Autore, Genere, AnnoPubblicazione, CasaEditrice, COUNT(idCopia) as copie 
        FROM $table1 
        LEFT JOIN $table2 ON $table1.ISBN = $table2.ISBN 
        WHERE 1=1";

$params = [];
if ($search !== '') {
    $sql .= " AND (Nome LIKE :s1 OR Autore LIKE :s2 OR Genere LIKE :s3 OR AnnoPubblicazione LIKE :s4 OR CasaEditrice LIKE :s5 OR $table1.ISBN LIKE :s6)";
    $searchTerm = "%$search%";
    $params[':s1'] = $params[':s2'] = $params[':s3'] = $params[':s4'] = $params[':s5'] = $params[':s6'] = $searchTerm;
}

$sql .= " GROUP BY $table1.ISBN";
$sql .= ($sort === 'copie') ? " ORDER BY copie DESC" : " ORDER BY $sort_column ASC";

// Aggiungiamo LIMIT e OFFSET per la paginazione
$sql .= " LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$libri = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];
foreach ($libri as $row) {
    $isbn = $row['ISBN'];
    $btnElimina = ($row['copie'] == 0) ? "<button type='button' class='btn btn-danger btn-sm elimina'>Elimina</button>" : "";
    
    $html = "
    <tr data-isbn='$isbn'>
        <th scope='row'><button class='btn btn-sm btn-info btn-espandi' type='button' data-isbn='$isbn'>+</button> $isbn</th>
        <td>".htmlspecialchars($row['Nome'])."</td>
        <td class='col-nascondi'>".htmlspecialchars($row['Autore'])."</td>
        <td class='col-nascondi'>".htmlspecialchars($row['Genere'])."</td>
        <td class='col-nascondi'>{$row['AnnoPubblicazione']}</td>
        <td class='col-nascondi'>".htmlspecialchars($row['CasaEditrice'])."</td>
        <td class='col-nascondi'>
            <input type='number' value='{$row['copie']}' class='form-control-sm' style='width:60px'>
            <button class='btn btn-outline-info btn-sm save'>Salva</button>
        </td>
        <td class='col-nascondi'>
            <a class='btn btn-primary btn-sm btn_modifica' href='modificaLibro.php?id=$isbn'>Modifica</a>
            $btnElimina
        </td>
        <td class='mobile-only'>
            <button class='btn btn-sm btn-secondary btn-info-mobile' type='button'>Info</button>
        </td>
    </tr>
    <tr id='row-details-$isbn' style='display:none;' class='bg-light'>
        <td colspan='9'><div id='content-$isbn' class='p-3'>Caricamento in corso...</div></td>
    </tr>";
    
    $response[] = ['html' => $html];
}

echo json_encode($response);