<?php
session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");

if (!isset($_SESSION['utenza']) || ($_SESSION['utenza'] != 1 && $_SESSION['utenza'] != 2)) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

try {
    $pdo = DatabaseConnection::getInstance()->getConnection();
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}

$filtro_stato = $_POST['filtro_stato'] ?? 'tutti';
$data_inizio = $_POST['data_inizio'] ?? '';
$data_fine = $_POST['data_fine'] ?? '';
$limite = isset($_POST['caricaAltro']) ? (int)$_POST['caricaAltro'] : 10;
$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$sort_type = $_POST['sort_type'] ?? 'idPrenotazione DESC';

// Logica SQL per determinare lo stato testuale
$sql_stato = "CASE 
    WHEN InizioPrestito IS NULL THEN 
        CASE WHEN FinePrenotazione < NOW() THEN 'Terminato' ELSE 'Prenotato' END
    WHEN FinePrestito IS NULL THEN 
        CASE WHEN NOW() > FineAttesa THEN 'In Ritardo' ELSE 'In Prestito' END
    ELSE 'Terminato'
END";

$params = [];
$sql = "SELECT idPrenotazione, InizioPrenotazione, FinePrenotazione, InizioPrestito, Copertina,
               FinePrestito, FineAttesa, Autore, Nome, Opera.ISBN as ISBN, email,
               ($sql_stato) as stato_calcolato 
        FROM Prenotazione 
        JOIN copiaLibro ON copiaLibro.idCopia = Prenotazione.idCopia 
        JOIN Opera ON Opera.ISBN = copiaLibro.ISBN 
        WHERE 1=1";

if ($filtro_stato !== 'tutti') {
    if ($filtro_stato === 'In corso') {
        $sql .= " AND ($sql_stato) != 'Terminato'";
    } else {
        $sql .= " AND ($sql_stato) = :filtro_stato";
        $params[':filtro_stato'] = $filtro_stato;
    }
}

if ($data_inizio) {
    $sql .= " AND InizioPrenotazione >= :data_inizio";
    $params[':data_inizio'] = $data_inizio;
}
if ($data_fine) {
    $sql .= " AND InizioPrenotazione <= :data_fine";
    $params[':data_fine'] = $data_fine;
}

// Validazione basilare per l'ordinamento
$allowed_sort = ['ISBN', 'Nome', 'InizioPrenotazione', 'idPrenotazione DESC'];
if (!in_array($sort_type, $allowed_sort)) {
    $sort_type = 'idPrenotazione DESC';
}

$sql .= " ORDER BY $sort_type LIMIT $limite OFFSET $offset";

$stmt = $pdo->prepare($sql);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$output = [];

foreach ($results as $row) {
    // Gestione colori basata sullo stato calcolato
    $color = "text-muted";
    if ($row['stato_calcolato'] == "Prenotato") $color = "text-success";
    if ($row['stato_calcolato'] == "In Prestito") $color = "text-warning";
    if ($row['stato_calcolato'] == "In Ritardo") $color = "text-danger";

    // Determinazione date da mostrare
    $inizio = $row["InizioPrestito"] ?? $row["InizioPrenotazione"];
    $fine = $row["FinePrestito"] ?? ($row["FineAttesa"] ?? $row["FinePrenotazione"]);

    $output[] = [
        
        'idPrenotazione' => $row['idPrenotazione'], 
        'Copertina' => $row['Copertina'],           

        'ISBN' => htmlspecialchars($row['ISBN']),
        'Nome' => htmlspecialchars($row['Nome']),
        'Autore' => htmlspecialchars($row['Autore']),
        'email' => htmlspecialchars($row['email']),
        'stato_calcolato' => $row['stato_calcolato'],
        'color' => $color,
        'inizio_formattato' => date("d/m/Y", strtotime($inizio)),
        'fine_formattata' => date("d/m/Y", strtotime($fine)),
        // ECCO L'URL MANCANTE
        'dettaglioUrl' => "../dashboard/dashboardUtenti/dettaglioPrenotazione.php?id=" . $row['idPrenotazione']
    ];
}

header('Content-Type: application/json');
echo json_encode($output);
?>