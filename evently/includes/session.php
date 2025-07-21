<?php 
session_start();

if(empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function isLoggedIn() {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}
?>