<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Pagination controls display component
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
