<?php
if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';

    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }

    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    session_start();
}
