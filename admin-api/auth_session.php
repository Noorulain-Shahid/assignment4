<?php
session_start();

// 1. Check if user is logged in
if(!isset($_SESSION["email"])) {
    header("Location: admin-login.php");
    exit();
}

// 2. Security: Check for Session Hijacking (User Agent Mismatch)
if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
    session_unset();
    session_destroy();
    header("Location: admin-login.php?error=session_hijack");
    exit();
}

// 3. Security: Session Timeout (30 Minutes)
$timeout_duration = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
    session_unset();
    session_destroy();
    header("Location: admin-login.php?timeout=true");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
