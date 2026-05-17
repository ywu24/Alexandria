<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Helpers
 * @file Generic pagination helper
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
