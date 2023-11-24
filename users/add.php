<?php
session_start();
require_once('../includes/config.php');
$page_title = 'Add User Record';
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
		$email = $_POST['email'];
		$role = $_POST['role'];
		$pepper_password = hash_hmac("sha256", mysqli_escape_string($dbc, $_POST['password1']), $pepper);
		$password = password_hash($pepper_password, PASSWORD_ARGON2ID);
		$active = $_POST['active'];
		$query = "INSERT INTO users (email, password, role_id, active)
			Values ('$email', '$password', $role, $active)";
		$result = query($query);
		if ($result) {
			echo "<center><p><b>New user $email has been added.</b></p>";
			echo "<a href=index.php>Show All Users</a></center>";
			log_event($_SESSION['user_id'], "Added New Role $role_description");
		} else {
			echo "<p>The record could not be added due to a system error - " . mysqli_error(connect()) . "</p>";
		}
	} // only if submitted by the form
	?>
	<form action="add.php" method="post">
		Email :<input name="email" id="email" type="email" size=20>
		<p>
			Password: <input name="password_1" id="password_1" type="password" size=20>
		<p>
			Confirm Password: <input name="password_2" id="password_2" type="password" size=20>
		<p>
			User Role :
			<?php

			$rolesResult = read('roles');
			echo '<select name="role" id="role">';
			echo '<option value="">-- Select Role --</option>';
			foreach ($rolesResult as $role) {
				$roleId = $role['role_id'];
				$roleDescription = $role['role_description'];
				echo '<option value="' . $roleId . '">' . $roleDescription . '</option>';
			}
			echo '</select>';
			?>
		<p>
			Active : <input type="checkbox" id='active' name='active'>
		<p>
			<input type=submit value=submit id="submit">
			<input type=reset value=reset>
			<input type=hidden name=submitted value=true>
	</form>
	<?php
	//include the footer
	include("../includes/footer.php");
}
?>

<script>
	document.getElementById('submit').addEventListener("submit", function (event) {
		event.preventDefault();

		console.log('submitting');
		console.log(validateEmail(document.getElementById('email').value));
		console.log(passwordCompare(document.getElementById('password_1').value, document.getElementById('password_2').value));
		console.log(roleSelected())


		if (validateEmail(document.getElementById('email').value) && passwordCompare(document.getElementById('password_1').value, document.getElementById('password_2').value) && roleSelected()) {
			this.submit();
		} else {
			console.log('error');
		}


	});

	const validateEmail = (email) => {
		// regex email format
		const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		return emailPattern.test(email);
	};

	const passwordCompare = (password1, password2) => {
		return password1 === password2
	};

	const roleSelected = () => {
		return document.getElementById('role').value.length > 0;
	}
</script>