<?php
session_start();
$page_title = 'Delete Configuration Item';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
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
	$query = "DELETE FROM config_items WHERE config_id=$id";
	$result = query($query);
	if ($result) {
		echo "The selected configuration item has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Configuration Item $id");
	} else {
		echo "The selected Configuration Item could not be deleted.";
	}
	echo "<p><a href=index.php>Configuration Items</a>";
	mysqli_close($dbc);
	//include the footer
	include('../includes/footer.php');
}

?>