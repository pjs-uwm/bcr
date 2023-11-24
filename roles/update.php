<?php
session_start();

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
$page_title = 'Edit Role';
$base_security_level = $ROLE_ADMIN;

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
		$role_description = mysqli_real_escape_string($dbc, $_POST['role_description']);

		$query = "UPDATE roles SET role_description='$role_description' WHERE role_id='$id'";
		$result = query($query);

		if ($result) {
			echo "<center><p><b>The selected role has been updated.</b></p>";
			echo "<a href=index.php>Roles</a></center>";
			log_event($_SESSION['user_id'], "Updated Role $role_description");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM roles WHERE role_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such role in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Role Description: <input name="role_description" size=50 value="<?php echo $row['role_description']; ?>">
			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['role_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>