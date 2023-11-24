<?php
session_start();
$page_title = 'Add Movie Role';

require_once('../../includes/config.php');
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
	include("../../includes/header.php");

	require_once('../../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$movie_id = mysqli_real_escape_string($dbc, $_POST['movie']);
		$role = array();
		$role['movie_id'] = $movie_id;
		$role['person_id'] = mysqli_real_escape_string($dbc, $_POST['person']);
		$role['role'] = mysqli_real_escape_string($dbc, $_POST['role']);
		// convert to boolean
		if (isset($_POST['leading_role']))
			$role['leading_role'] = 1;
		else {
			$role['leading_role'] = 0;
		}


		$insert = create("movie_person_roles", $role);
		if ($insert) {

			echo "<center><p><b>New Role has been added with ID $movie_id.</b></p>";
			echo "<a href=index.php?id=$movie_id>Show Credits for Movie</a></center>";
			log_event($_SESSION['user_id'], "Added Movie Role " . $role['person_id'] . " to Movie $movie_id");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>" . $query;
		}

	} // only if submitted by the form
	mysqli_close($dbc);

	if (isset($_GET['id'])) {
		$movie_id = $_GET['id'];
	} elseif (isset($_POST['movie'])) {
		$movie_id = $_POST['movie'];
	} else {
		echo '<p class="error">No movie ID was provided.</p>';
		exit();
	}
	?>


	<form action="add.php" method="post" enctype="multipart/form-data">
		Person:
		<?php
		$personQuery = "SELECT person_id, first_name, last_name FROM person WHERE person_id NOT IN (SELECT person_id FROM movie_person_roles WHERE movie_id = $movie_id) ORDER BY last_name";
		$personResult = query_arr($personQuery);
		if ($personResult) {
			echo '<select name="person" id="person">';
			foreach ($personResult as $person) {
				$personID = $person['person_id'];
				$personName = $person['first_name'] . " " . $person['last_name'];
				echo '<option value="' . $personID . '">' . $personName . '</option>';
			}
			echo '</select>';
		} else {
			echo '<p>No people available to add to this movie.</p>';
		}
		?>
		<p>

			Role: <input type="text" name="role" size="30" maxlength="30" />

			Leading Role: <input type="checkbox" name="leading_role" value="0" />

			<input type="hidden" name="movie" value="<?php echo $movie_id; ?>" />

		<p>
			<input type=submit value=submit>
			<input type=reset value=reset>
			<input type=hidden name=submitted value=true>
	</form>
	<?php
	//include the footer
	include("../../includes/footer.php");
}

?>