<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Helpers
 * @file Generic pagination helper
 */

/*
 Gestore generico e centralizzato della paginazione per l'applicazione.
 * Fornisce gli strumenti per suddividere i set di dati massivi del database in blocchi navigabili,
 * ottimizzando le performance del server e l'esperienza utente.
 * * Come funziona il codice:
 * 1. Riceve una query di conteggio e i relativi parametri per determinare il volume totale dei record.
 * 2. Intercetta il parametro globale $_GET['page'], lo sanitizza tramite espressioni regolari per
 * prevenire input malevoli o incoerenti, e lo normalizza entro i limiti minimi (1) e massimi (lastPage).
 * 3. Calcola matematicamente l'OFFSET per la query SQL di selezione dei dati effettivi.
 * 4. Mantiene lo stato dei filtri attivi (es. ricerche o generi) concatenando i parametri URL correnti.
 * 5. Genera dinamicamente una barra di navigazione HTML (struttura a scorrimento con un raggio di 4 pagine).
 * 6. Offre una funzione alternativa (pagination_meta) per esporre lo stato della paginazione 
 * sotto forma di array di metadati puri, ideale per risposte AJAX o API in formato JSON.
 */

/**
 * Calculate pagination controls and LIMIT/OFFSET clause
 *
 * @param PDO $pdo Database connection
 * @param string $countQuery SQL query to count total rows
 * @param array $countParams Parameters for count query
 * @param int $maxPerPage Items per page
 * @param string $pageTo Base URL for pagination links
 * @param array $urlParams Additional URL parameters to preserve
 * @return array ['limit' => string, 'controls' => string, 'page' => int, 'lastPage' => int, 'rowCount' => int]
 */
function paginate(
    PDO $pdo,
    string $countQuery,
    array $countParams = [],
    int $maxPerPage = 10,
    string $pageTo = '',
    array $urlParams = []
): array {
    $paginationCtrls = '';

    $query = $pdo->prepare($countQuery);
    $query->execute($countParams);
    $result = $query->fetch();
    $query->closeCursor();
    $rowCount = (int) ($result['tot'] ?? 0);

    $page = 1;
    if ($rowCount > 0) {
        $p = $_GET['page'] ?? 1;
        $page = (int) preg_replace('#[^0-9]#', '', (string) $p);
        $lastPage = (int) ceil($rowCount / $maxPerPage);

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $lastPage) {
            $page = $lastPage;
        }

        $limit = 'LIMIT ' . $maxPerPage . ' OFFSET ' . (($page - 1) * $maxPerPage);

        if ($lastPage != 1) {
            $queryString = !empty($urlParams) ? http_build_query($urlParams) . '&' : '';
            $baseUrl = $pageTo . '?' . $queryString;

            if ($page > 1) {
                $previous = $page - 1;
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $previous . '">&laquo;</a>';
            }

            for ($i = $page - 4; $i < $page; $i++) {
                if ($i > 0) {
                    $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
                }
            }

            $paginationCtrls .= '<a class="active">' . $page . '</a>';

            for ($i = $page + 1; $i <= $lastPage; $i++) {
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $i . '">' . $i . '</a>';
                if ($i >= $page + 4) {
                    break;
                }
            }

            if ($page != $lastPage) {
                $next = $page + 1;
                $paginationCtrls .= '<a href="' . $baseUrl . 'page=' . $next . '">&raquo;</a>';
            }
        }
    } else {
        $limit = "LIMIT $maxPerPage";
        $lastPage = 1;
    }

    return [
        'limit' => $limit,
        'controls' => $paginationCtrls,
        'page' => $page,
        'lastPage' => $lastPage ?? 1,
        'rowCount' => $rowCount,
    ];
}

/**
 * Build pagination controls for AJAX or API endpoints
 *
 * @param int $page Current page
 * @param int $lastPage Total pages
 * @param int $rowCount Total rows
 * @param int $maxPerPage Items per page
 * @return array Pagination metadata
 */
function pagination_meta(int $page, int $lastPage, int $rowCount, int $maxPerPage = 10): array
{
    return [
        'page' => $page,
        'lastPage' => $lastPage,
        'rowCount' => $rowCount,
        'perPage' => $maxPerPage,
        'hasPrevious' => $page > 1,
        'hasNext' => $page < $lastPage,
    ];
}
