<?php
session_start();
$page_title = 'Delete Movie Role';

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
	$movie_id = $_GET['movie_id'];
	$person_id = $_GET['person_id'];
	$deleteQuery = "DELETE FROM movie_person_roles WHERE movie_id = $movie_id AND person_id = $person_id";

	$delete = query($deleteQuery);
	if ($delete) {
		echo "The selected Movie Role has been deleted.";
		log_event($_SESSION['user_id'], "Deleted Movie Role $movie_id - $person_id");
	} else {
		echo "The selected movie role could not be could not be deleted.";
	}
	echo "<p><a href=index.php?id=$movie_id>Movie Role List</a>";
	mysqli_close($dbc);
	//include the footer
	include('../../includes/footer.php');
}

?>