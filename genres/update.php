<?php
require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$page_title = 'Edit Genre';
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
	include("../includes/header.php");

	if (isset($_POST['submitted'])) {
		#execute UPDATE statement
		$id = mysqli_real_escape_string($dbc, $_POST['id']);
		$genre_name = mysqli_real_escape_string($dbc, $_POST['genre_name']);
		$genre_color = mysqli_real_escape_string($dbc, $_POST['genre_color']);

		$query = "UPDATE genres SET genre_name='$genre_name', genre_color='$genre_color' WHERE genre_id='$id'";
		$result = query($query);

		if ($result) {
			echo "<center><p><b>The selected genre has been updated.</b></p>";
			echo "<a href=index.php>Genres</a></center>";
			log_event($_SESSION['user_id'], "Updated Genre $genre_name");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM genres WHERE genre_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such genre in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Genre Name: <input required name="genre_name" size=50 value="<?php echo $row['genre_name']; ?>">
			<p>
				Genre Color: <input name="genre_color" type="color" value="<?php echo $row['genre_color']; ?>">
			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['genre_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>