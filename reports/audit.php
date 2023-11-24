<?php
session_start();
$page_title = 'System Audit Report';

require_once('../includes/config.php');
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
	include('../includes/header.php');
	require_once('../includes/entity_manager.php');
	log_event($_SESSION['user_id'], "Accessed Audit Report");
	echo ("<center>");
	echo ("<h3>Audit of BCR System Activity</h3><p>");


	$display = isset($_GET['display_limit']) ? $_GET['display_limit'] : 25;

	// search filters
	$activity = isset($_GET['activity']) ? $_GET['activity'] : '';
	$userID = isset($_GET['user']) ? $_GET['user'] : '';
	$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
	$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';

	echo "<form action='audit.php' method='get'>";
	echo "<label for='display_limit'>Records to Display: </label><input type='range' id='display_limit' min='5' max='100' value='$display' step='5' name='display_limit'>";
	echo '<output id="display_limit_output">' . $display . '</output><br>';
	echo "<label for'activity'>Activity: </label><input type='text' id='activity' name='activity' value='" . $activity . "'><br>";
	$users = read('users');
	echo '<select name="user" id="user">';

	if (!$users) {
		echo '<option value="">-- No Active Users Available --</option>';
	} else {
		echo '<option value="">-- Select User to Filter On --</option>';
		foreach ($users as $user) {
			$luUser = $user['user_id'];
			$userDisplay = $user['user_id'] . ' - ' . $user['email'];
			if ($user['active'] == 1) {
				$selected = ($userID == $luUser) ? 'selected' : '';
				echo '<option value="' . $luUser . '" ' . $selected . '>' . $userDisplay . '</option>';
			}
		}
	}
	echo '</select><br>';

	echo "<label for='start_date'>Start Date: </label><input type='date' id='start_date' name='start_date' value='" . $startDate . "'>";
	echo "<label for='end_date'>End Date: </label><input type='date' id='end_date' name='end_date' value='" . $endDate . "'>";

	echo "<input type='submit' value='Submit'>";
	echo "</form><p>";

	echo '<script>
	var slider = document.getElementById("display_limit");
	var output = document.getElementById("display_limit_output");
	output.innerHTML = slider.value;
	slider.oninput = function() {
	  output.innerHTML = this.value;
	}
	</script>';

	//Set the number of records to display per page
	$display = isset($_GET['display_limit']) ? $_GET['display_limit'] : 25;

	$filters = [];

	if ($activity != '') {
		$filters[] = ['activity', "%" . $_GET['activity'] . "%", 'LIKE', "'"];
	}
	if ($userID != '') {
		$filters[] = ['user_id', $_GET['user'], '=', ""];
	}

	if ($startDate != '') {
		$filters[] = ['event_time', $_GET['start_date'], '>=', "'"];
	}

	if ($endDate != '') {
		$endDate = date('Y-m-d', strtotime($_GET['end_date'] . ' + 1 days'));
		$filters[] = ['event_time', $endDate, '<=', "'"];
	}

	$whereClause = generateWhereClause($filters);

	//Count the number of records;
	$query = "SELECT COUNT(audit_id) FROM audit_log " . $whereClause;
	$result = query($query);
	$row = @mysqli_fetch_array($result, MYSQLI_NUM);
	$records = $row[0]; //get the number of records	

	echo "Total Audited Activities - $records <br>";

	//Check if the number of required pages has been determined
	if (isset($_GET['p']) && is_numeric($_GET['p'])) { //Already been determined
		$pages = $_GET['p'];
	} else { //Need to determine


		//Calculate the number of pages ...
		if ($records > $display) { //More than 1 page is needed
			$pages = ceil($records / $display);
		} else {
			$pages = 1;
		}

	} // End of p IF.



	//Determine where in the database to start returning results
	if (isset($_GET['s']) && is_numeric($_GET['s'])) {
		$start = $_GET['s'];
	} else {
		$start = 0;
	}





	//Make the paginated query;
	$query = "SELECT * FROM audit_log " . $whereClause . " LIMIT $start, $display";
	$result = query($query);


	//Table header:
	echo "<table cellpadding=5 cellspacing=5 border=1 class=table-list><tr>
	<th>Activity</th><th>User</th><th>Date</th></tr>";

	//Fetch and print all the records...
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		echo "<tr><td>" . $row['activity'] . "</td>";
		echo "<td>" . $row['user_id'] . "</td>";
		echo "<td>" . date('m/d/Y H:i', strtotime($row['event_time'])) . "</td>";
	} // End of While statement
	echo "</table>";
	mysqli_close($dbc); // Close the database connection.

	//Make the links to other pages if necessary.
	if ($pages > 1) {
		echo '<br/><table><tr>';
		//Determine what page the script is on:
		$current_page = ($start / $display) + 1;
		//If it is not the first page, make a Previous button:
		if ($current_page != 1) {
			echo '<td><a href="?s=' . ($start - $display) . '&p=' . $pages . '"> Previous </a></td>';
		}
		//Make all the numbered pages:
		for ($i = 1; $i <= $pages; $i++) {
			if ($i != $current_page) { // if not the current pages, generates links to that page
				echo '<td><a href="?s=' . (($display * ($i - 1))) . '&p=' . $pages . '"> ' . $i . ' </a></td>';
			} else { // if current page, print the page number
				echo '<td>' . $i . '</td>';
			}
		} //End of FOR loop
		//If it is not the last page, make a Next button:
		if ($current_page != $pages) {
			echo '<td><a href="?s=' . ($start + $display) . '&p=' . $pages . '"> Next </a></td>';
		}

		echo '</tr></table>';  //Close the table.
	} //End of pages links
	//include the footer
	include('../includes/footer.php');
}
?>