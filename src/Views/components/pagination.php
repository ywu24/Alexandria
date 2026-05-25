<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Pagination controls display component
 */

/*
 Componente dell'interfaccia utente (View) deputato al rendering dei controlli di paginazione.
 * Agisce come ponte tra la logica di calcolo dei dati e la presentazione visuale finale.
 * * Come funziona il codice:
 * 1. Riceve l'array dei dati di paginazione e un parametro stringa opzionale per classi CSS aggiuntive.
 * 2. Applica un controllo di uscita anticipata (early return) se non sono presenti controlli HTML da renderizzare.
 * 3. Genera un wrapper HTML strutturato sfruttando regole CSS inline (Flexbox) per garantire il perfetto 
 * centramento dei tasti di navigazione.
 * 4. Applica la sanitizzazione dell'output tramite la funzione `e()` sul parametro dinamico `$class` per 
 * prevenire vulnerabilità XSS nell'attributo HTML.
 * 5. Esegue l'iniezione sicura dei link di navigazione HTML precedentemente strutturati dal modulo helper.
 */

/**
 * Render pagination controls
 *
 * @param array $pagination Pagination data from paginate() helper
 * @param string $class Additional CSS classes for the wrapper
 * @return void
 */
function render_pagination(array $pagination, string $class = ''): void
{
    if (empty($pagination['controls'])) {
        return;
    }
?>
    <div class="center pagination-wrapper <?php echo e($class); ?>" style="margin-top: 30px; display: flex; justify-content: center;">
        <div class="pagination">
            <?php echo $pagination['controls']; ?>
        </div>
    </div>
<?php
}
