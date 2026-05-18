<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage edit_profile
 * @file Change profile picture handling file
 */

require_once __DIR__ . '/../../src/bootstrap.php';

$root = '../..';
$email = $_SESSION['email'];

if (isset($_POST['propicIns'])) {

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === 4) {
        // nessun file selezionato
        flash('error', 'Nessun file selezionato');
        redirect('../edit_profile.php');
    }

    //Recupera i dati del file inviato
    $file = $_FILES['image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];

    // Estrae l'estensione del file
    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));

    // Crea un nome univoco per il file
    $file_name_new = uniqid() . '.' . $file_ext;

    // Specifica la directory di destinazione per il file
    $file_destination = '../../img/users/' . $file_name_new;

    // Controlla se ci sono errori durante il caricamento del file
    if ($file_error === 0) {
        // Verifica che il file sia di un formato supportato
        $allowed = array('jpg', 'jpeg', 'png');
        if (in_array($file_ext, $allowed)) {
            // Verifica che la dimensione del file non superi un limite specificato
            if ($file_size <= 5000000) {
                // Carica il file
                if (!move_uploaded_file($file_tmp, $file_destination)) {
                    throw new RuntimeException('Errore nel caricamento della copertina. Verificare i permessi della cartella img/books/.');
                }
            } else {
                flash('error', 'Il file è troppo grande. Il limite massimo è 5 MB.');
                redirect('../edit_profile.php');
            }
        } else {
            flash('error', 'Il file non è supportato. I formati supportati sono: jpg, jpeg e png.');
            redirect('../edit_profile.php');
        }
    } else {
        flash('error', 'Si è verificato un errore durante il caricamento del file.');
        redirect('../edit_profile.php');
    }
    $propic = $file_name_new;

    $sql = "UPDATE `Utente` SET propic = :propic WHERE `Utente`.`Email` = :email";
    try {
        $query = $pdo->prepare($sql);
        $query->bindParam(':propic', $propic);
        $query->bindParam(':email', $email);
        $query->execute();
        $query->closeCursor();
        flash('success', "Immagine cambiata con successo.");
        redirect('../edit_profile.php');
    } catch (PDOException $e) {
        flash('error', "Error: " . $sql . "<br>" . $e->getMessage());
        redirect('../edit_profile.php');
    }
}
