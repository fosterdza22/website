<?php
/**
 * Auto-detects the "base path" the app is installed under, so every
 * internal link works whether you access the site at:
 *   http://localhost/index.php                (installed at the web root)
 * or
 *   http://localhost/hostel-agency/index.php   (installed in a subfolder)
 *
 * You should NOT normally need to edit this file. If auto-detection ever
 * gets it wrong on your server, uncomment the line below and set it to
 * match your folder name exactly (no trailing slash).
 */

// define('BASE_URL', '/hostel-agency'); // <-- manual override, uncomment if auto-detection fails

if (!defined('BASE_URL')) {
    $basePath = '';

    // --- Primary method: compare the currently executing script's filesystem
    // path against the app root's filesystem path, then apply that same
    // relative depth to the script's URL path (SCRIPT_NAME). This works
    // regardless of DOCUMENT_ROOT, symlinks, or virtual host quirks.
    $appRoot = str_replace('\\', '/', dirname(__DIR__));                          // e.g. C:/xampp/htdocs/hostel-agency
    $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME'] ?? '');       // e.g. C:/xampp/htdocs/hostel-agency/student/hostel_detail.php
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');           // e.g. /hostel-agency/student/hostel_detail.php

    if ($scriptFile !== '' && stripos($scriptFile, $appRoot) === 0) {
        $relative = substr($scriptFile, strlen($appRoot));      // e.g. /student/hostel_detail.php
        if ($relative !== '' && substr($scriptName, -strlen($relative)) === $relative) {
            $basePath = substr($scriptName, 0, strlen($scriptName) - strlen($relative));
        }
    }

    // --- Fallback method: compare against DOCUMENT_ROOT.
    if ($basePath === '') {
        $docRoot = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/\\'));
        if ($docRoot !== '' && stripos($appRoot, $docRoot) === 0) {
            $basePath = substr($appRoot, strlen($docRoot));
        }
    }

    define('BASE_URL', rtrim($basePath, '/')); // e.g. '/hostel-agency' or '' if installed at the web root
}
