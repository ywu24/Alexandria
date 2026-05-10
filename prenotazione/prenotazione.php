<?php
// LEVARE QUESTA SEZIONE dopo il debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once("../utils/connect.php");
$root = '..';
require_once("../auth/cookies.php");

// Controllo accessi: se Admin, vai alla pagina Admin, se non loggato vai a index
if (isset($_SESSION['utenza'])) {
    if ($_SESSION['utenza'] == 2) {
        header("Location: prenotazioneAdmin.php");
        die();
    }
} else {
    header("Location: ../index.php");
    die();
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Le Mie Prenotazioni | Alexandria</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/unified.css">
    <link rel="stylesheet" href="../css/prenotazione.css">
    <link rel="stylesheet" href="../css/colors.css">
    <link rel="stylesheet" href="../css/messaggi.css">
    <!-- Aggiunto stile inline per garantire il layout a due colonne simile all'admin -->
    <style>
        .book-container {
            background: #fff;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #eee;
            overflow: hidden;
        }

        .left-panel {
            border-right: 1px solid #eee;
            padding: 20px;
            display: flex;
        }

        .book-cover {
            width: 100px;
            height: 140px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .status-dot {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .info-meta {
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 10px;
        }

        .delete-button {
            border: none;
            background: none;
            padding: 5px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .delete-button:hover {
            transform: scale(1.1);
        }

        .icon-trash {
            width: 20px;
            opacity: 0.6;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Il tuo JS che gestisce click, delete e load-terminated -->
    <script src="prenotazione.js"></script>
</head>

<body>

    <div id="nav-placeholder">
        <?php require_once("../nav/nav.php"); ?>
    </div>

    <div id="messages" class="container mt-3">
        <?php
        if (isset($_SESSION['success_msg'])) {
            echo '<p class="successo">' . htmlspecialchars($_SESSION['success_msg']) . '</p>';
            unset($_SESSION['success_msg']);
        }
        if (isset($_SESSION['error_msg'])) {
            echo '<p class="errore">' . htmlspecialchars($_SESSION['error_msg']) . '</p>';
            unset($_SESSION['error_msg']);
        }
        ?>
    </div>

    <div class="container mt-5">
        <div class="text-center mb-5">
            <h1 class="display-4 font-weight-bold">Le Mie Prenotazioni</h1>
            <p class="lead text-muted">Gestisci i tuoi prestiti e visualizza lo storico</p>
        </div>

        <div id="bookings-container">
            <?php
            $email = $_SESSION['email'];
            try {
                $pdo = DatabaseConnection::getInstance()->getConnection();

                // Query ottimizzata
                $query = $pdo->prepare("SELECT p.idPrenotazione, o.id as idOpera, o.Copertina, p.idCopia, 
                                        p.InizioPrenotazione, p.FinePrenotazione, p.InizioPrestito, 
                                        p.FinePrestito, p.FineAttesa, o.Autore, o.Nome, o.CasaEditrice, o.ISBN 
                                        FROM Prenotazione p
                                        JOIN copiaLibro c ON c.idCopia = p.idCopia 
                                        JOIN Opera o ON o.ISBN = c.ISBN 
                                        WHERE p.Email = :email 
                                        ORDER BY p.idPrenotazione DESC");
                $query->execute([':email' => $email]);
                $rows = $query->fetchAll();

                $numero_prenotazioni = 0;

                foreach ($rows as $row) {
                    $inizio = "";
                    $fine = "";
                    $stato = "";
                    $color_class = "";

                    // Logica Stati (Coerente con il tuo backend)
                    if ($row["InizioPrestito"] == NULL) {
                        if (strtotime($row['FinePrenotazione']) < time()) {
                            continue; // Salta gli scaduti non ritirati qui (verranno gestiti dai task)
                        } else {
                            $stato = "Prenotato";
                            $color_class = "text-success";
                            $inizio = date("d/m/Y", strtotime($row["InizioPrenotazione"]));
                            $fine = date("d/m/Y", strtotime($row["FinePrenotazione"]));
                        }
                    } else {
                        if ($row['FinePrestito'] == NULL) {
                            if (time() > strtotime($row['FineAttesa'])) {
                                $stato = "In Ritardo";
                                $color_class = "text-danger";
                            } else {
                                $stato = "In Prestito";
                                $color_class = "text-warning";
                            }
                            $inizio = date("d/m/Y", strtotime($row["InizioPrestito"]));
                            $fine = date("d/m/Y", strtotime($row["FineAttesa"]));
                        } else {
                            $stato = "Terminato"; // Gestito sotto nel "Carica Terminate"
                        }
                    }

                    if ($stato != "" && $stato != "Terminato") {
                        $numero_prenotazioni++;
            ?>
                       <div class="book-container shadow-sm">
    <div class="row no-gutters">
        <!-- Colonna Sinistra: 75% di spazio -->
        <div class="col-md-9 left-panel p-3 d-flex">
            <!-- Copertina Ingrandita -->
            <img src="../img/books/<?= $row['Copertina'] ?>" class="book-cover" alt="Copertina" 
                 style="width:140px; height:200px; object-fit:cover; border-radius:8px; margin-right:20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <h4 class="h4 font-weight-bold mb-1"><?= htmlspecialchars($row['Nome']) ?></h4>
                    <?php if ($stato == "Prenotato"): ?>
                        <button class="delete-button" data-id="<?= $row['idPrenotazione'] ?>" title="Annulla">
                            <img src="../img/trash-bin.png" class="icon-trash" style="width:22px;" alt="Elimina">
                        </button>
                    <?php endif; ?>
                </div>
                <p class="info-meta mb-2" style="font-size: 1.1rem;"><?= htmlspecialchars($row['Autore']) ?> | <?= htmlspecialchars($row['CasaEditrice']) ?></p>
                <p class="status-dot <?= $color_class ?> mb-3">● <?= $stato ?></p>
                
                <div class="small text-muted mb-3">
                    <span><strong>Inizio:</strong> <?= $inizio ?></span><br>
                    <span><strong>Scadenza:</strong> <?= $fine ?></span><br>
                    <span class="text-dark"><strong>ISBN:</strong> <?= $row['ISBN'] ?></span>
                </div>

            </div>
        </div>

        <!-- Colonna Destra: Feedback -->
        <div class="col-md-3 bg-light d-flex align-items-start p-3 border-left">
            <p class="text-muted small mb-0">Prenotazione attiva nel sistema.</p>
        </div>
    </div>
</div>
            <?php
                    }
                }

                if ($numero_prenotazioni == 0) {
                    echo "<div class='text-center p-5'><h3 class='text-muted'>Nessuna prenotazione attiva al momento</h3></div>";
                }
            } catch (PDOException $e) {
                echo "<div class='alert alert-danger'>Errore nel caricamento dei dati.</div>";
            }
            ?>
        </div>

        <!-- Sezione Terminate -->
        <div class="mt-5 mb-5 border-top pt-4">
            <div id="terminate-container" class="text-center">
                <button id="load-terminated" class="btn btn-outline-secondary px-5" data-id-utente="<?= $_SESSION['email'] ?>" style="border-radius: 25px;">
                    Mostra prenotazioni terminate
                </button>
            </div>
        </div>
    </div>

</body>

</html>