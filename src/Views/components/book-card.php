<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Book card display component for listing pages
 *
 * Usage: render_book_card(array $book, array $availability, array $rating, string $imgPrefix = '../')
 */

/**
 * Render a single book card for the catalog listing
 *
 * @param array $book Book data with keys: id, Copertina, Nome, Autore, CasaEditrice, ISBN, Genere, Descrizione
 * @param array $availability Availability data with keys: disponibilita, color
 * @param array $rating Rating stats with keys: media, totale
 * @param string $imgPrefix Path prefix for book images (e.g., '../' or '../../')
 * @param int $descMaxLen Maximum description length before truncation
 * @return void
 */
function render_book_card(array $book, array $availability, array $rating, string $imgPrefix = '../', int $descMaxLen = 150): void
{
    $id = (int) ($book['id'] ?? 0);
    $cover = e($book['Copertina'] ?? '');
    $title = e($book['Nome'] ?? '');
    $author = e($book['Autore'] ?? '');
    $publisher = e($book['CasaEditrice'] ?? '');
    $isbn = e($book['ISBN'] ?? '');
    $genre = e($book['Genere'] ?? '');
    $description = e($book['Descrizione'] ?? '');
    $truncatedDesc = strlen($description) > $descMaxLen ? substr($description, 0, $descMaxLen) . '...' : $description;
    $mediaRounded = (int) round((float) ($rating['media'] ?? 0));
    $reviewCount = (int) ($rating['totale'] ?? 0);
    $dispColor = $availability['color'] ?? 'inherit';
    $dispText = e($availability['disponibilita'] ?? '');
?>
    <a href="<?php echo $imgPrefix; ?>libro/libro.php?id=<?php echo $id; ?>">
        <div class="book-list hvr-float data-single-book shadow-sm mb-3">
            <img src="<?php echo $imgPrefix; ?>img/books/<?php echo $cover; ?>" width="113" height="171" class="book-img" style="object-fit: cover;" alt="<?php echo $title; ?>">
            <div class="container-book">
                <span class="book-link trunctitle" style="font-weight: bold; font-size: 1.2em; display: block; margin-bottom: 5px;"><?php echo $title; ?></span>
                <div class="rating-stars" style="margin-bottom: 5px; font-size: 0.9rem;">
                    <span style="color: var(--color-accent);"><?php echo render_stars($mediaRounded); ?></span>
                    <small style="font-size: 0.75rem; color: var(--color-text-muted);"> (<?php echo $reviewCount; ?>)</small>
                </div>
                <p class="book-authors"><?php echo $author . ' | ' . $publisher . ' | ' . $isbn . ' | ' . $genre; ?></p>
                <p class="desc"><?php echo $truncatedDesc; ?></p>
                <span style="color: <?php echo $dispColor; ?>; font-weight: bold;" class="disponibilita"><?php echo $dispText; ?></span>
            </div>
        </div>
    </a>
<?php
}
