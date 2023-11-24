<?php
session_start();
$page_title = 'Edit Physical DVD Asset';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
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
	include("../includes/header.php");

	if (isset($_POST['submitted'])) {
		# execute UPDATE statement
		$dvd = array();
		$id = mysqli_real_escape_string($dbc, $_POST['id']);

		$dvd['dvd_serial_number'] = $_POST['serial_number'];
		$dvd['date_received'] = date("Y-m-d", strtotime($_POST['date_received']));
		$dvd['movie_id'] = $_POST['movie_id'];
		$update = update('dvds', 'dvd_id', $id, $dvd);
		if ($update) {
			echo "<center><p><b>The selected DVD has been updated.</b></p>";
			echo "<a href=index.php>DVD Assets</a></center>";
			log_event($_SESSION['user_id'], "Updated DVD $id");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM dvds WHERE dvd_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such DVD Asset in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			DVD Serial Number: <input name="serial_number" size=50 value="<?php echo $row['dvd_serial_number']; ?>">
			<p>
				Date Received: <input type="date" name="date_received" value="<?php echo $row['date_received']; ?>">
			<p>
				Associated Movie:
				<?php

				$movieList = read("movies");
				echo '<select name="movie_id" id="movie_id">';

				if (count($movieList) < 1) {
					echo '<option value="">-- No Movies Available --</option>';
				} else {
					echo '<option value="0">-- Select Movie --</option>';
					foreach ($movieList as $movie) {
						$movieID = $movie['movie_id'];
						$movieTitle = $movie['title'];
						$selected = ($movieID == $row['movie_id']) ? 'selected' : '';
						echo '<option value="' . $movieID . '" ' . $selected . ' >' . $movieTitle . '</option>';
					}
				}
				echo '</select>';
				?>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['dvd_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>