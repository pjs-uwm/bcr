<?php
session_start();
$page_title = 'Edit Employee';

require_once('../includes/config.php');
require_once('../includes/entity_manager.php');
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
	include("../includes/header.php");

	if (isset($_POST['submitted'])) {
		#execute UPDATE statement
		$employee = array();
		$id = mysqli_real_escape_string($dbc, $_POST['id']);

		$employee['employee_title'] = mysqli_real_escape_string($dbc, $_POST['employee_title']);
		$employee['phone_number'] = mysqli_real_escape_string($dbc, $_POST['phone_number']);
		$employee['user_id'] = mysqli_real_escape_string($dbc, $_POST['user']);
		$employee['active'] = isset($_POST['active']) ? 1 : 0;

		$result = update('employees', 'employee_id', $id, $employee);

		if ($result) {
			echo "<center><p><b>The Selected Employee been updated.</b></p>";
			echo "<a href=index.php>Employees</a></center>";
			log_event($_SESSION['user_id'], "Updated Employee $id");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM employees WHERE employee_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such Employee in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			Employee Title: <input required name="employee_title" size=50 value="<?php echo $row['employee_title']; ?>">
			<p>
				Phone Number: <input name="phone_number" size=50 value="<?php echo $row['phone_number']; ?>">
			<p>
				Active: <input type="checkbox" name="active" <?php if ($row['active'] == 1)
					echo "checked"; ?>>
			<p>
				User Association:
				<?php


				$usersQuery = "SELECT * FROM users WHERE user_id NOT IN (SELECT user_id FROM employees) OR user_id = " . $row['user_id'] . "";
				$usersResult = query_arr($usersQuery);
				echo '<select name="user" id="user">';

				echo '<option value="">-- Select User to Associate--</option>';
				foreach ($usersResult as $user) {
					$userID = $user['user_id'];
					$userDisplay = $user['user_id'] . ' - ' . $user['email'];
					$selected = ($userID == $row['user_id']) ? 'selected' : '';
					if ($user['active'] == 1) {
						echo '<option ' . $selected . ' value="' . $userID . '">' . $userDisplay . '</option>';
					}
				}
				;
				echo '</select>';
				?>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['employee_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>