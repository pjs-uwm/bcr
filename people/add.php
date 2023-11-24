<?php
session_start();
$page_title = 'Add Person Entity';

require_once('../includes/config.php');
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
	include("../includes/header.php");

	require_once('../includes/entity_manager.php');
	if (isset($_POST['submitted'])) {
		$first_name = $_POST['first_name'];
		$last_name = $_POST['last_name'];
		$middle_name = isset($_POST['middle_name']) ? $_POST['middle_name'] : "";
		$sag_status = isset($_POST['sag_status']) ? 1 : 0;

		$sag_check_errors = sag_compliance_check(strtolower($first_name), strtolower($last_name), strtolower($middle_name));
		if ($sag_check_errors) {
			$result = NULL;

		} else {

			$query = "INSERT INTO person (first_name, last_name, middle_name, sag_status)
			Values ('$first_name', '$last_name', '$middle_name', $sag_status)";
			$result = query($query);
		}
		if ($result) {
			echo "<center><p><b>New Person Entity has been added.</b></p>";
			echo "<a href=index.php>Show All Person Entities</a></center>";
			log_event($_SESSION['user_id'], "Added New Person Entity $first_name $last_name");
		} else {
			if ($sag_check_errors) {
				echo "<p><h4><u>SAG Compliance Check Failed</u></h4></p>";
				foreach ($sag_check_errors as $error) {
					echo "<p>$error</p>";
				}
			} else {
				echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>";
			}
		}
	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post">
		First Name: <input name="first_name" size=50 required>
		<p>
			Middle Name: <input name="middle_name" size=50 id="middle_name">
		<p>
			Last Name: <input name="last_name" size=50 required>
		<p>
			SAG Member: <input type="checkbox" name="sag_status" id="sag_status">
		<p>
		<p>
			<input type=submit value=submit>
			<input type=reset value=reset>
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


		});
	</script>

	<?php
	//include the footer
	include("../includes/footer.php");
}
?>