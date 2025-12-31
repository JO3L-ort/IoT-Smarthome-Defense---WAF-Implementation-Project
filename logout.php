<?php
session_start();
$_SESSION = [];         // kosongkan array session
session_unset();        // hapus semua session
session_destroy();      // hancurkan session
header("Location: login.php"); // arahkan ke halaman login
exit;
?>