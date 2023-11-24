<?php
session_start();
$page_title = 'Delete Payment Method Confirmation';
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
	} else if ($_SESSION['role_id'] == $ROLE_CUSTOMER && $_SESSION['customer_id'] != $_GET['customer_id']) {
		header("Location: $baseUrl/accessdenied.php");
		exit();
	}
	//include the header
	include('../../includes/header.php');

	$pm_id = $_GET['pm_id'];
	$customer_id = $_GET['customer_id'];
	$query = "SELECT * FROM payment_methods WHERE customer_id=$customer_id AND pm_id=$pm_id";
	$result = query($query);
	$num = mysqli_num_rows($result);
	if ($num > 0) { // If it ran OK, display all the records.
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		echo "Payment Method to Delete : " . $row['pm_description'] . "<p>";

		echo "Are you sure that you want to delete this Payment Method?<br>";
		echo "<a href=delete.php?pm_id=" . $pm_id . "&customer_id=" . $customer_id . ">YES</a> 
			<a href=index.php?id=$customer_id>NO</a>";
	} else { // If it did not run OK.
		echo '<p>There is no such record. Please check value and try again.</p>';
	}
	mysqli_close($dbc); // Close the database connection.
	//include the footer
	include('../../includes/footer.php');
}

?>