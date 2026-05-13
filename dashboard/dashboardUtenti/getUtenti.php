<?php
session_start();
require_once("../../utils/connect.php");

header('Content-Type: application/json');

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    exit(json_encode([]));
}

$pdo = DatabaseConnection::getInstance()->getConnection();

$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$sort = $_POST['sort_type'] ?? 'id';
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;

$allowed_sort = ['id', 'Nome', 'Cognome', 'Email', 'Utenza', 'punteggio'];
if (!in_array($sort, $allowed_sort)) $sort = 'id';

// 2. Costruzione Query
$sql = "SELECT id, Nome, Cognome, Email, punteggio, Utenza FROM Utente WHERE 1=1";

if ($search !== '') {
    $sql .= " AND (
        id LIKE :s1 OR 
        Nome LIKE :s2 OR 
        Cognome LIKE :s3 OR 
        Email LIKE :s4 OR
        CONCAT(Nome, ' ', Cognome) LIKE :s5
    )";
}

$direction = ($sort === 'punteggio') ? 'DESC' : 'ASC';
$sql .= " ORDER BY $sort $direction LIMIT :limit OFFSET :offset";

try {
    $stmt = $pdo->prepare($sql);
    
    if ($search !== '') {
        $st = "%$search%";
        // Bindiamo lo stesso valore a placeholder diversi
        $stmt->bindValue(':s1', $st, PDO::PARAM_STR);
        $stmt->bindValue(':s2', $st, PDO::PARAM_STR);
        $stmt->bindValue(':s3', $st, PDO::PARAM_STR);
        $stmt->bindValue(':s4', $st, PDO::PARAM_STR);
        $stmt->bindValue(':s5', $st, PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $utenti = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    exit(json_encode([["html" => "<td>Errore SQL: " . $e->getMessage() . "</td>"]]));
}

$response = [];
foreach ($utenti as $row) {
    
    $ruoli = [1 => "Admin", 2 => "Bibliotecario", 3 => "Docente", 4 => "Cittadino"];
    $desc = $ruoli[$row['Utenza']] ?? "N/A";
    
    if ($_SESSION['utenza'] == 1) {
        $html = "
            <th scope='row' class='col-nascondi row-header'>{$row['id']}</th>
            <td>{$row['Nome']}</td>
            <td>{$row['Cognome']}</td>
            <td class='col-nascondi'>{$row['Email']}</td>
            <td class='col-nascondi'>{$desc}</td>
            <td class='col-nascondi'>{$row['punteggio']}</td>
            <td class='col-nascondi'>
                <div class='btn_actions'>
                    <a class='btn btn-primary' href='modificaUtente.php?id={$row['id']}'>Modifica</a>
                    <a class='btn btn-danger' href='eliminaUtente.php?id={$row['Email']}'>Elimina</a>
                </div>
            </td>";
    } else {
        $html = "
            <td>{$row['Nome']}</td>
            <td>{$row['Cognome']}</td>
            <td class='col-nascondi'>{$row['Email']}</td>
            <td class='col-nascondi'>{$row['punteggio']}</td>
            <td class='col-nascondi'>
                <div class='btn_actions text-center'>
                    <a class='btn btn-primary' href='dettaglioUtente.php?id={$row['id']}'>Prenotazioni</a>
                </div>
            </td>";
    }
    $response[] = [
        'html'      => $html,
        'id'        => $row['id'],
        'email'     => $row['Email'],
        'punteggio' => $row['punteggio'],
        'ruolo'     => $desc
    ];
}

echo json_encode($response);