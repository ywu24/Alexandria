<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Single review card template
 *
 * Usage: render_review_card(array $review, string $imgPrefix = '../')
 */

/**
 * Render a single review card
 *
 * @param array $review Review data with keys: Voto, propic, userEmail, Titolo, Messaggio
 * @param string $imgPrefix Path prefix for user images (e.g., '../' or '../../')
 * @return void
 */
function render_review_card(array $review, string $imgPrefix = '../'): void
{
    $voto = (int) ($review['Voto'] ?? 0);
    $propic = $review['propic'] ?? '';
    $userEmail = $review['userEmail'] ?? '';
    $titolo = $review['Titolo'] ?? '';
    $messaggio = $review['Messaggio'] ?? '';
    $imgPath = $imgPrefix . 'img/users/';
?>
<div class="review-card mb-4">
    <div class="review-header">
        <div class="user-info">
            <div class="user-avatar">
                <?php if (!empty($propic) && file_exists($imgPath . $propic)): ?>
                    <img src="<?php echo $imgPath . e($propic); ?>" alt="Avatar"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?php echo strtoupper(substr($userEmail, 0, 1)); ?>
                <?php endif; ?>
            </div>
            <div>
                <h5 class="m-0 fw-bold"><?php echo e($titolo); ?></h5>
                <small class="text-muted"><?php echo e($userEmail); ?></small>
            </div>
        </div>
        <div class="review-rating">
            <?php echo render_stars($voto); ?>
        </div>
    </div>
    <div class="review-body">
        <p><?php echo nl2br(e($messaggio)); ?></p>
    </div>
</div>
<?php
}
