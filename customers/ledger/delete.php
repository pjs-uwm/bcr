<?php
session_start();
$page_title = 'Delete Customer Ledger Entry';

require_once('../../includes/config.php');
require_once('../../includes/entity_manager.php');
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
	//include the header
	include('../../includes/header.php');
	$ledger_id = $_GET['ledger_id'];
	$customer_id = $_GET['customer_id'];
	$query = "DELETE FROM customer_ledger WHERE ledger_id=$ledger_id";
	$result = query($query);
	if ($result) {
		echo "The selected customer ledger has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Customer Ledger $ledger_id for Customer $customer_id");
	} else {
		echo "The selected record could not be deleted.";
	}
	echo "<p><a href=index.php?id=$customer_id>View Customer's Ledger</a>";
	mysqli_close($dbc);
	//include the footer
	include('../../includes/footer.php');
}

?>