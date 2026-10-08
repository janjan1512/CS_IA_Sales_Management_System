<?php

function require_login(): void {
    // start session only if not started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // debug log
    error_log("require_login: session_id=" . session_id() . " session=" . json_encode($_SESSION));

    
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php', true, 303);
        exit;
    }
}
?>