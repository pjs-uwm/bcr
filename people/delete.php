<?php
session_start();
$page_title = 'Delete Actor Entity';

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
	$query = "DELETE FROM person WHERE person_id=$id";
	$result = query($query);
	if ($result) {
		echo "The selected record has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Actor Entity $id");
	} else {
		echo "The selected record could not be deleted.";
	}
	echo "<p><a href=index.php>Actor Entities</a>";
	mysqli_close($dbc);
	//include the footer
	include('../includes/footer.php');
}

?>