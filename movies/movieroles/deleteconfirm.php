<?php
session_start();
$page_title = 'Delete Movie Role Confirmation';
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

	$movie_id = $_GET['movie_id'];
	$person_id = $_GET['person_id'];

	$query = "SELECT p.first_name, p.last_name, mpr.* FROM movie_person_roles mpr LEFT OUTER JOIN person p on mpr.person_id = p.person_id WHERE mpr.movie_id=$movie_id AND mpr.person_id=$person_id";

	$result = query_arr($query);
	if (count($result) == 1) { // If it ran OK, display all the records.
		$result = $result[0];
		echo "Role to Delete - " . $result['role'] . " for " . $result['first_name'] . " " . $result['last_name'] . "<p>";

		echo "Are you sure that you want to delete this Role from this Movie?<br>";
		echo "<a href=delete.php?movie_id=" . $movie_id . "&person_id=" . $person_id . ">YES</a> 
			<a href=index.php>NO</a>";
	} else { // If it did not run OK.
		echo '<p>There is no such record. Please check value and try again.</p>';
	}
	mysqli_close($dbc); // Close the database connection.
	//include the footer
	include('../../includes/footer.php');
}

?>