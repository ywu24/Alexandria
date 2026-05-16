<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Dashboard
 * @file Handles book and copy insertion
 */

require_once __DIR__ . '/../../src/bootstrap.php';

use Alexandria\Services\AuthService;
use Alexandria\Services\BookService;

$authService = new AuthService($pdo);
if (!$authService->isAdmin() && !$authService->isLibrarian()) {
    redirect('../../index.php');
}

$bookService = new BookService($pdo);

// --- LOGICA INSERIMENTO OPERA ---
if (isset($_POST['btnOpera'])) {
    $titolo = $_POST['titolo'];
    $autore = $_POST['autore'];
    $casa_editrice = $_POST['casaed'];
    $isbn = $_POST['isbn'];
    $genere = $_POST['genere'];
    $descrizione = $_POST['desc'];
    $anno_pubblicazione = $_POST['annopub'];
    $copie = (int) $_POST['qty'];

    // Gestione File
    $file = $_FILES['image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $file_name_new = str_replace(' ', '', $titolo) . uniqid() . '.' . $file_ext;
    $file_destination = '../../img/books/' . $file_name_new;
    $copertina = $file_name_new;

    // Controllo errori caricamento
    if ($file['error'] === 0 && in_array($file_ext, ['jpg', 'jpeg', 'png']) && $file['size'] <= 5000000) {
        move_uploaded_file($file_tmp, $file_destination);
    }

    try {
        $bookService->insert([
            'isbn' => $isbn,
            'nome' => $titolo,
            'autore' => $autore,
            'genere' => $genere,
            'descrizione' => $descrizione,
            'copertina' => $copertina,
            'casa_editrice' => $casa_editrice,
            'anno_pubblicazione' => $anno_pubblicazione,
        ], $copie);

        flash('success', 'Dati sul libro e relativa/e copia/e aggiunto al database');
        redirect('dashboardLibri.php');
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        redirect('dashboardLibri.php');
    }

// --- LOGICA INSERIMENTO SOLO COPIE ---
} elseif (isset($_POST['btnCopia'])) {
    $isbn = $_POST['isbn'];
    $copie = (int) $_POST['qty'];

    try {
        $bookService->addCopies($isbn, $copie);
        flash('success', 'Copia/e libro aggiunto al database');
        redirect('dashboardLibri.php');
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        redirect('dashboardLibri.php');
    }
}
