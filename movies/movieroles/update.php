<?php
session_start();
$page_title = 'Update Movie Role';

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
	include("../../includes/header.php");

	if (isset($_POST['update'])) {
		#execute UPDATE statement
		$movie_id = mysqli_real_escape_string($dbc, $_POST['movie_id']);
		$orig_person_id = mysqli_real_escape_string($dbc, $_POST['person_id']);
		$role = mysqli_real_escape_string($dbc, $_POST['role']);
		$person = mysqli_real_escape_string($dbc, $_POST['person']);
		$leading_role = isset($_POST['leading_role']) ? 1 : 0;

		$movieRoleQuery = "UPDATE movie_person_roles SET person_id=$person, role='$role', leading_role=$leading_role WHERE movie_id=$movie_id AND person_id=$orig_person_id";
		$movieRoleUpdate = query_bool($movieRoleQuery);

		if ($movieRoleUpdate) {
			echo "<center><p><b>The Movie Role has been updated.</b></p>";
			echo "<a href=index.php?id=$movie_id>Movies Roles</a></center>";
			log_event($_SESSION['user_id'], "Updated Movie Role $movie_id for $person");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>" . $query;
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$orig_movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : $movie_id;
		$orig_person_id = isset($_GET['person_id']) ? $_GET['person_id'] : $person;
		$query = "SELECT * FROM movie_person_roles WHERE movie_id=$orig_movie_id AND person_id=$orig_person_id";
		print($query);
		$result = query_arr($query);
		if (count($result) != 1) {
			echo "<p>There is no such movie with selected role in the database.</p>";
			exit();
		} else {
			$row = $result[0];
		}

		?>

		<form action="update.php" method="post" enctype="multipart/form-data">
			Person:
			<?php
			$personQuery = "SELECT person_id, first_name, last_name FROM person WHERE person_id NOT IN (SELECT person_id FROM movie_person_roles WHERE movie_id = $orig_movie_id) or person_id = $orig_person_id ORDER BY last_name";
			$personResult = query_arr($personQuery);
			if ($personResult) {
				echo '<select name="person" id="person">';
				foreach ($personResult as $person) {
					$personID = $person['person_id'];
					$personName = $person['first_name'] . " " . $person['last_name'];
					$selected = $person_id == $orig_person_id ? 'selected' : '';
					echo '<option ' . $selected . ' value="' . $personID . '">' . $personName . '</option>';
				}
				echo '</select>';
			} else {
				echo '<p>No people available to add to this movie.</p>';
			}
			?>
			<p>

				<?php
				$role = $row['role'];
				$leading_role = $row['leading_role'] ? 'checked' : '';
				?>
				Role: <input type="text" name="role" size="30" maxlength="30" value="<?php echo $role ?>" />

				Leading Role: <input type="checkbox" name="leading_role" <?php echo $leading_role ?> />

				<input type="hidden" name="movie_id" value="<?php echo $orig_movie_id; ?>" />
				<input type="hidden" name="person_id" value="<?php echo $orig_person_id; ?>" />

			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name=update value=true>
		</form>
		<?php
	}
	//include the footer
	include("../../includes/footer.php");
}
?>