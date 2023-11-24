<?php
session_start();
$page_title = 'Delete Customer and Deactivate User';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$base_security_level = $ROLE_MANAGER;


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
	include('../includes/header.php');
	$id = $_GET['id'];

	// get the customer record with user join
	$query = "SELECT u.first_name, u.last_name, u.user_id, c.* FROM customers c LEFT OUTER JOIN users u on c.user_id = u.user_id WHERE c.customer_id=$id";
	$result = query_arr($query);
	if (count($result) == 0) {
		echo "The selected Customer could not be found.";
	} else {
		$row = $result[0];
		$queryCustomer = "DELETE FROM customers WHERE customer_id=$id";
		$resultCustomer = query($queryCustomer);

		$queryUser = "UPDATE users SET active=0 WHERE user_id=" . $row['user_id'];
		$resultUser = query($queryUser);

		if ($resultCustomer && $resultUser) {
			echo "The selected Customer has been deleted and User Account Deactivated.";
			log_event($_SESSION['user_id'], "Deleted Customer $id");
			log_event($_SESSION['user_id'], "Deactivated User $row[user_id] for Customer $id");
		} else {
			echo "The selected Customer could not be deleted.";
		}
	}



	echo "<p><a href=index.php>Customers</a>";
	mysqli_close($dbc);
	//include the footer
	include('../includes/footer.php');
}

?>