<?php
session_start();
$page_title = 'DVD Editor';

require_once('../includes/config.php');
$base_security_level = $ROLE_EMPLOYEE;

//check session first
if (!isset($_SESSION['email'])) {
    header("Location: $baseUrl/login.php");
    exit();
} else {
    if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] > $base_security_level) {
        header("Location: $baseUrl/accessdenied.php");
        exit();
    }
    require_once('../includes/header.php');
    require_once('../includes/entity_manager.php');

    log_event($_SESSION['user_id'], "Viewed Report List");
    echo "<h2>Reports</h2>";
    echo "<div>";
    if ($_SESSION['role_id'] == $ROLE_ADMIN) {
        echo '<a href="audit.php">Audit Report</a> - Report that shows all the actions that have been taken on the site.';
    } else {
        echo '<b>No Reports Available For Current User Role</b>';
    }

    echo "</div>";
    ?>

    <?php
}
include('../includes/footer.php');
?>