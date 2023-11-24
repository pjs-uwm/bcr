<?php
session_start();
require_once('../includes/config.php');
$page_title = 'Update User Record';
$base_security_level = $ROLE_CUSTOMER;

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
		$user = array();
		$user_id = $_POST['id'];
		$user['first_name'] = $_POST['first_name'];
		$user['last_name'] = $_POST['last_name'];
		$user['email'] = $_POST['email'];
		if ($_SESSION['role_id'] == $ROLE_ADMIN) {
			$user['role_id'] = $_POST['role'];
			$user['active'] = isset($_POST['active']) ? 1 : 0;
		}

		if (isset($_POST['password1']) && isset($_POST['password2'])) {
			if (strlen($_POST['password1']) > 0) {
				$pepper_password = hash_hmac("sha256", mysqli_escape_string($dbc, $_POST['password1']), $pepper);
				$hashed_password = password_hash($pepper_password, PASSWORD_ARGON2ID);
				$user['password'] = $hashed_password;
			}
		}
		$result = update('users', 'user_id', $user_id, $user);

		if ($result) {
			echo "<center><p><b>User Updated $user_id has been added.</b></p>";
			echo "<a href=index.php>Show All Users</a></center>";
			log_event($_SESSION['user_id'], "Updated User $user_id");
		} else {
			echo "<p>The record could not be added due to a system error - " . mysqli_error(connect()) . "</p>";
		}
	} else {

		if ($_SESSION['role_id'] == $ROLE_CUSTOMER && isset($_SESION['user_id'])) {
			$id = $_SESSION['user_id'];
		} else {
			$id = $_GET['id'];
		}
		$result = read('users', $id, 'user_id');
		$num = $result != null ? count($result) : 0;
		if ($num != 1) {
			echo "<p>There is no such User in the database.</p>";
			exit();
		} else {
			$row = $result[0];
		}
		?>
		<form action="update.php" method="post">
			First Name: <input required type="text" name="first_name" size="15" maxlength="15"
				value="<?php echo $row['first_name']; ?>" />
			<p>
				Last Name: <input required type="text" name="last_name" size="15" maxlength="30"
					value="<?php echo $row['last_name']; ?>" />
			<p>
				Email Address: <input required type="text" name="email" size="20" maxlength="40"
					value="<?php echo $row['email']; ?>" />
			<p>
				New Password: <input type="password" name="password1" size="10" maxlength="20" />
			<p>
				Confirm Password: <input type="password" name="password2" size="10" maxlength="20" />
			<p>



				<?php
				if ($_SESSION['role_id'] == $ROLE_ADMIN) {
					echo "User Role :";
					$rolesResult = read('roles');
					echo '<select required name="role" id="role">';
					echo '<option value="">-- Select Role --</option>';
					foreach ($rolesResult as $role) {
						$roleId = $role['role_id'];
						$roleDescription = $role['role_description'];
						$selected = $roleId == $row['role_id'] ? 'selected' : '';
						echo '<option value="' . $roleId . '" ' . $selected . '>' . $roleDescription . '</option>';
					}
					echo '</select>';
					$checked = $row['active'] == 1 ? 'checked' : '';
					echo "<p>";
					echo "Active : <input type='checkbox' id='active' name='active' $checked>";
				}
				?>
			<p>


			<p>
				<input type=submit value=update id="submit">
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $id; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
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