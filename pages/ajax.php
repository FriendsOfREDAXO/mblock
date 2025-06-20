<?php
/**
 * MBlock v3.5 - AJAX Handler for toggle requests
 * @author MBlock v3.5 Extension
 */

// Handle toggle requests
if (rex_request::get('func') === 'toggle_block' || rex_request::post('func') === 'toggle_block') {
    MBlockToggleHandler::handleToggleRequest();
}

// If we reach here, redirect back to prevent direct access
rex_response::sendRedirect(rex_url::currentBackendPage());
