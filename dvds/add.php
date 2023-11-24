<?php
session_start();
$page_title = 'Add Physical DVD Asset';

require_once('../includes/config.php');
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
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$serial_number = $_POST['serial_number'];
		$dvd = array();
		$dvd['dvd_serial_number'] = $serial_number;
		$dvd['date_received'] = date("Y-m-d", strtotime($_POST['date_received']));
		$dvd['movie_id'] = $_POST['movie_id'];
		$insert = create('dvds', $dvd);
		if ($insert) {
			echo "<center><p><b>New DVD $serial_number has been added.</b></p>";
			echo "<a href=index.php>Show All DVD Assets</a></center>";
			log_event($_SESSION['user_id'], "Added New Physical DVD Asset $serial_number");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		DVD Serial Number: <input name="serial_number" size=50>
		<p>
			Date Received: <input type="date" name="date_received">
		<p>
			Associated Movie:
			<?php

			$movieList = read("movies");
			echo '<select name="movie_id" id="movie_id">';

			if (count($movieList) < 1) {
				echo '<option value="">-- No Movies Available --</option>';
			} else {
				echo '<option value="">-- Select Movie --</option>';
				foreach ($movieList as $movie) {
					$movieID = $movie['movie_id'];
					$movieTitle = $movie['title'];
					echo '<option value="' . $movieID . '">' . $movieTitle . '</option>';
				}
			}
			echo '</select>';
			?>
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