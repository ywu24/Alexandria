<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @file Notification center page
 */

require_once __DIR__ . '/../src/bootstrap.php';

use Alexandria\Services\NotificationService;

// Inizializzazione del servizio
// Assumiamo che NotificationService sia iniettato con PDO tramite bootstrap o direttamente
$notificationService = new NotificationService($pdo);

$root = '..';

// Controllo sessione utente
$email = $_SESSION['email'] ?? null;
if (!$email) {
    redirect('../index.php');
}

$notifiche = [];

try {
    // 1. Recuperiamo TUTTE le notifiche
    // Assumiamo che tu abbia creato un metodo nel servizio che accetti l'email o risolva l'ID utente
    $notifiche = $notificationService->getUserNotifications($email);

    // 2. Segniamo tutte le notifiche non lette come lette
    $notificationService->markAllAsRead($email);

} catch (Exception $e) {
    // Log dell'errore e messaggio utente tramite il sistema flash del nuovo framework
    error_log("Errore Centro Notifiche: " . $e->getMessage());
    flash('error', 'Impossibile caricare le notifiche al momento.');
}
?>

<!DOCTYPE html>
<html lang="it">

<head>
    <?php 
    // Utilizziamo il render_head del nuovo framework
    render_head(
        "Centro Notifiche - Alexandria's Library", 
        ['css/pages/footer.css'], 
        [], 
        '..'
    ); 
    ?>
</head>

<body class="notifiche-page">
    <div class="safe-area spaced-column">

        <div id="nav-placeholder">
            <?php require_once('../nav/nav.php'); ?>
        </div>

        <div id="messages">
            <?php render_messages(); ?>
        </div>

        <main class="container my-5">
            <div class="notifiche-page-container">
                <h1 class="fw-bold mb-4">Centro Notifiche</h1>
                <hr class="mb-5">

                <?php if (empty($notifiche)): ?>
                    <div class="no-reviews-container text-center py-5 border rounded bg-light shadow-sm">
                        <h5 class="text-muted">Non hai ancora ricevuto nessuna notifica.</h5>
                        <p class="mb-0">Tutte le tue comunicazioni importanti appariranno qui.</p>
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($notifiche as $n): ?>
                            <div class="col-12">
                                <div class="card shadow-sm notifica-card <?php echo $n['letta'] == 0 ? 'border-primary new' : 'border-light'; ?>">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="card-title fw-bold mb-0 notifica-title">
                                                <?php echo e($n['titolo']); ?>
                                            </h5>
                                            <?php if ($n['letta'] == 0): ?>
                                                <span class="badge bg-primary rounded-pill">Nuova</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <p class="card-text text-secondary mt-3">
                                            <?php echo e($n['messaggio']); ?>
                                        </p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                            <small class="text-muted notifica-time">
                                                Ricevuta il: <span class="fw-medium"><?php echo date('d/m/Y H:i', strtotime($n['data_creazione'])); ?></span>
                                            </small>
                                            
                                            <?php if (!empty($n['url_azione'])): ?>
                                                <a href="<?php echo $root . '/' . ltrim($n['url_azione'], '/'); ?>" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                                    Vai al dettaglio &gt;
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </main>

    </div>
    <?php require_once('../nav/footer.php'); ?>
</body>

</html>