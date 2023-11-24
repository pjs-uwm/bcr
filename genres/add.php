<?php
require_once('../includes/config.php');
$page_title = 'Add Genre';
$base_security_level = $ROLE_EMPLOYEE;
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
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$genre_name = mysqli_real_escape_string($dbc, $_POST['genre_name']);
		$genre_color = mysqli_real_escape_string($dbc, $_POST['genre_color']);

		$query = "INSERT INTO genres (genre_name, genre_color)
			Values ('$genre_name', '$genre_color')";
		$result = query($query);
		if ($result) {
			echo "<center><p><b>New genre of $genre_name has been added.</b></p>";
			echo "<a href=index.php>Show All Genres</a></center>";
			log_event($_SESSION['user_id'], "Added New Genre $genre_name");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		Genre Name: <input required name="genre_name" size=50>
		<p>
			Genre Color: <input name="genre_color" type="color">
		<p>
		<p>
			<input type=submit value=submit>
			<input type=reset value=reset>
			<input type=hidden name=submitted value=true>
	</form>
	<?php
	//include the footer
	include("../includes/footer.php");
}
?>