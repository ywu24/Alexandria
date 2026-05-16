<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles saving book edits
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\BookService;

$bookService = new BookService($pdo);

$isbnOld = $_POST['id'] ?? '';
$isbnNew = $_POST['isbn'] ?? '';
$titolo = $_POST['titolo'] ?? '';
$autore = $_POST['autore'] ?? '';
$genere = $_POST['genere'] ?? '';
$desc = $_POST['desc'] ?? '';
$casaed = $_POST['casaed'] ?? '';
$annopub = $_POST['annopub'] ?? '';

try {
    $bookService->update($isbnOld, [
        'isbn' => $isbnNew,
        'nome' => $titolo,
        'autore' => $autore,
        'genere' => $genere,
        'descrizione' => $desc,
        'casaed' => $casaed,
        'annopub' => $annopub,
    ]);

    flash('success', 'Libro aggiornato con successo!');
    redirect('dashboardLibri.php');
} catch (PDOException $e) {
    flash('error', 'Errore durante l\'aggiornamento: ' . $e->getMessage());
    redirect('dashboardLibri.php?errore=1');
}
