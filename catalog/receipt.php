<?php
session_start();
echo "<title>Brew City Rentals - Your Receipt</title>";

require_once('../includes/config.php');
?>
<style>
    .receipt {
        text-align: center;
    }
</style>
<?php

$base_security_level = $ROLE_CUSTOMER;

//check session first
if (!isset($_SESSION['email'])) {
    header("Location: $baseUrl/login.php");
    exit();
} else {
    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
        header("Location: $baseUrl/accessdenied.php");
        exit();
    }
    log_event($_SESSION['user_id'] ?? 0, "Viewed Receipt");

    require_once('../includes/entity_manager.php');
    if (isset($_GET['ledger_id'])) {
        $ledger_id = $_GET['ledger_id'];
    } else {
        echo "<p>Invalid Ledger ID</p>";
        echo "<a href='javascript:window.close();'>Close Receipt</a>";
    }
    echo ("<center><img src='$baseUrl/media/logo.png' alt='Brew City Rentals Logos' title='Brew City Rentals Logo' class='logo-img' width='150px' height='150px'/><br>");
    echo generate_receipt($ledger_id);
    echo "<p><a href='javascript:window.print();'>Print Receipt</a>";
    echo "<p><a href='javascript:window.close();'>Close Receipt</a></center>";


}
?>