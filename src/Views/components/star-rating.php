<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Star rating display component
 *
 * Usage: include this file, then call render_stars_component($rating, $reviewCount)
 */

/**
 * Render a star rating display
 *
 * @param int|float $rating Rating value 0-5
 * @param int|null $reviewCount Optional review count to display
 * @param bool $showEmpty Whether to show empty stars
 * @return void
 */
function render_stars_component(int|float $rating, ?int $reviewCount = null, bool $showEmpty = true): void
{
    $full = (int) round((float) $rating);
    $empty = 5 - $full;

    echo '<span class="rating-stars">';
    echo str_repeat('&#9733;', $full);
    if ($showEmpty) {
        echo '<span class="stars-empty">' . str_repeat('&#9734;', $empty) . '</span>';
    }
    echo '</span>';

    if ($reviewCount !== null && $reviewCount > 0) {
        echo ' <span class="media-voto">' . $rating . ' / 5</span>';
        echo ' <small class="text-muted">(' . $reviewCount . ' recensioni)</small>';
    }
}
