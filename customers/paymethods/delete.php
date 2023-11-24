<?php
session_start();
$page_title = 'Delete DVD Physical Asset';

require_once('../../includes/config.php');
require_once('../../includes/entity_manager.php');
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
	//include the header
	include('../../includes/header.php');
	$customer_id = $_GET['customer_id'];
	$pm_id = $_GET['pm_id'];
	$query = "DELETE FROM payment_methods WHERE pm_id=$pm_id AND customer_id=$customer_id";
	$result = query($query);
	if ($result) {
		echo "The selected payment methods has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Payment Method $pm_id for Customer $customer_id");
	} else {
		echo "The selected record could not be deleted.";
	}
	echo "<p><a href=index.php?id=$customer_id>View Payment Methods</a>";
	mysqli_close($dbc);
	//include the footer
	include('../../includes/footer.php');
}

?>