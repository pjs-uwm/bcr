<?php
session_start();
require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$page_title = 'Delete User';
$base_security_level = $ROLE_ADMIN;


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
	$query = "DELETE FROM roles WHERE role_id=$id";
	$result = query($query);
	if ($result) {
		echo "The selected record has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Role $id");
	} else {
		echo "The selected record could not be deleted.";
	}
	echo "<p><a href=index.php>Roles List</a>";
	mysqli_close($dbc);
	//include the footer
	include('../includes/footer.php');
}

?>