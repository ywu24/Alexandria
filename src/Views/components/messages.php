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
