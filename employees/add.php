<?php
session_start();
$page_title = 'Add Employee Record';

require_once('../includes/config.php');
$base_security_level = $ROLE_MANAGER;


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
		$employee = array();

		$employee['employee_title'] = mysqli_escape_string($dbc, $_POST['employee_title']);
		$employee['phone_number'] = mysqli_escape_string($dbc, $_POST['phone_number']);
		$employee['user_id'] = mysqli_escape_string($dbc, $_POST['user']);
		$employee['active'] = isset($_POST['active']) ? 1 : 0;



		$result = create_id_return('employees', $employee);
		if ($result) {
			echo "<center><p><b>New Employee has been added.</b></p>";
			echo "<a href=index.php>Show All Employees</a></center>";
			log_event($_SESSION['user_id'], "Added New Employee ID $result");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		Job Title: <input required name="employee_title" size=100>
		<p>
			Phone Number: <input name="phone_number" size=50>
		<p>
			Active: <input type="checkbox" name="active" checked>
		<p>
			User Association:
			<?php
			$usersQuery = "SELECT * FROM users WHERE user_id NOT IN (SELECT user_id FROM employees)";
			$usersResult = query($usersQuery);
			echo '<select name="user" id="user">';

			if (mysqli_num_rows($usersResult) < 1) {
				echo '<option value="">-- No Active Users Available --</option>';
			} else {
				echo '<option value="">-- Select User to Associate--</option>';
				foreach ($usersResult as $user) {
					$userID = $user['user_id'];
					$userDisplay = $user['user_id'] . ' - ' . $user['email'];
					if ($user['active'] == 1) {
						echo '<option value="' . $userID . '">' . $userDisplay . '</option>';
					}
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