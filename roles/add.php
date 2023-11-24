<?php
session_start();
require_once('../includes/config.php');
$page_title = 'Add Role';
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

	//include the header
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$role_description = $_POST['role_description'];
		$role_id = $_POST['role_id'];
		$query = "INSERT INTO roles (role_id, role_description)
			Values ('$role_id', '$role_description')";
		$result = query($query);
		if ($result) {
			echo "<center><p><b>New role of $role_description has been added.</b></p>";
			echo "<a href=index.php>Show All Roles</a></center>";
			log_event($_SESSION['user_id'], "Added New Role $role_description");
		} else {
			echo "<p>The record could not be added due to a system error - " . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		Role ID: <input name="role_id" size=2>
		<p>
			Role Description: <input name="role_description" size=50>
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