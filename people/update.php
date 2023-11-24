<?php
session_start();
$page_title = 'Edit Actor Entity';

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
		#execute UPDATE statement
		$id = mysqli_real_escape_string($dbc, $_POST['id']);
		$first_name = mysqli_real_escape_string($dbc, $_POST['first_name']);
		$last_name = mysqli_real_escape_string($dbc, $_POST['last_name']);
		$middle_name = isset($_POST['middle_name']) ? mysqli_real_escape_string($dbc, $_POST['middle_name']) : "";
		$sag_status = isset($_POST['sag_status']) ? 1 : 0;

		$sag_check_errors = sag_compliance_check(strtolower($first_name), strtolower($last_name), strtolower($middle_name), $id);
		if ($sag_check_errors) {
			$result = NULL;

		} else {

			$query = "UPDATE person SET first_name='$first_name', last_name='$last_name', middle_name='$middle_name', sag_status = $sag_status WHERE person_id='$id'";
			$result = query($query);
		}
		echo "<a href=index.php>Actor Entities</a></center>";
		if ($result) {
			echo "<center><p><b>The selected Actor Entity has been updated.</b></p>";
			log_event($_SESSION['user_id'], "Updated Actor Entity $first_name $last_name");
		} else {
			if ($sag_check_errors) {
				echo "<p><h4><u>SAG Compliance Check Failed</u></h4></p>";
				foreach ($sag_check_errors as $error) {
					echo "<p>$error</p>";
				}
			} else {
				echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
			}
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM person WHERE person_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such Actor Entity in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post">
			First Name: <input required name="first_name" size=50 value="<?php echo $row['first_name']; ?>">
			<p>
				Middle Name: <input name="middle_name" id="middle_name" size=50 value="<?php echo $row['middle_name']; ?>">
			<p>
				Last Name: <input required name="last_name" size=50 value="<?php echo $row['last_name']; ?>">
			<p>
				SAG Member: <input type="checkbox" id="sag_status" name="sag_status" <?php if ($row['sag_status'] == 1)
					echo "checked"; ?>>
			<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['person_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				document.getElementById("sag_status").addEventListener("change", function () {
					if (this.checked) {
						// SAG Member
						this.value = 1;
						let middle_name_element = document.getElementById("middle_name");
						middle_name_element.disabled = true;
						middle_name_element.value = "";
						middle_name_element.placeholder = "SAG Members Cannot Have Middle Name / Initial";

					} else {
						// Not SAG Member
						this.value = 0;
						let middle_name_element = document.getElementById("middle_name");
						middle_name_element.disabled = false;
						middle_name_element.placeholder = "";
					}
				});
				// trigger to enforce middle name on loading person to edit
				let event = new Event("change");
				document.getElementById("sag_status").dispatchEvent(event);

			});
		</script>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>