<?php
/**
 * Alexandria Library Management System
 *
 * @package Alexandria
 * @subpackage Views\Components
 * @file Flash message display component
 */

/**
 * Render flash messages from session
 *
 * @return void
 */
function render_messages(): void
{
    $success = \flash_get('success');
    $error = \flash_get('error');

    if ($success !== null) {
        echo '<p class="successo">' . e($success) . '</p>';
    }
    if ($error !== null) {
        echo '<p class="errore">' . e($error) . '</p>';
    }
}

/**
 * Render legacy session messages (for pages not yet refactored)
 *
 * @return void
 */
function render_legacy_messages(): void
{
    if (isset($_SESSION['success_msg'])) {
        echo '<p class="successo">' . e($_SESSION['success_msg']) . '</p>';
        unset($_SESSION['success_msg']);
    }
    if (isset($_SESSION['error_msg'])) {
        echo '<p class="errore">' . e($_SESSION['error_msg']) . '</p>';
        unset($_SESSION['error_msg']);
    }
}
