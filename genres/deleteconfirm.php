<?php
require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$page_title = 'Delete Genre Confirmation';
$base_security_level = $ROLE_MANAGER;
session_start();

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
	$query = "SELECT * FROM genres WHERE genre_id=$id";
	$result = query($query);
	$num = mysqli_num_rows($result);
	if ($num > 0) { // If it ran OK, display all the records.
		$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		echo "Genere to Delete : " . $row['genre_name'] . "<p>";

		echo "Are you sure that you want to delete this genre?<br>";
		echo "<a href=delete.php?id=" . $id . ">YES</a> 
			<a href=index.php>NO</a>";
	} else { // If it did not run OK.
		echo '<p>There is no such record. Please check value and try again.</p>';
	}
	mysqli_close($dbc); // Close the database connection.
	//include the footer
	include('../includes/footer.php');
}

?>