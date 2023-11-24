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
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $_GET['customer_id']) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}
	//include the header
	include('../../includes/header.php');

	$pm_id = $_GET['ledger_id'];
	$ledger_id = $_GET['ledger_id'];
	$customer_id = $_GET['customer_id'];

	$query = "SELECT * FROM customer_ledger WHERE ledger_id=$ledger_id";
	$result = query($query);
	$num = mysqli_num_rows($result);
	if ($num > 0) { // If it ran OK, display all the records.
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		echo "Ledger Entry to Delete : " . $row['post_type'] . " - $" . $row['amount'] . "<p>";

		echo "Are you sure that you want to delete this Ledger Entry?<br>";
		echo "<a href=delete.php?ledger_id=$ledger_id&customer_id=$customer_id>YES</a>  ";
		echo "<a href=index.php?id=$customer_id>NO</a>";
	} else { // If it did not run OK.
		echo '<p>There is no such record. Please check value and try again.</p>';
	}
	mysqli_close($dbc); // Close the database connection.
	//include the footer
	include('../../includes/footer.php');
}

?>