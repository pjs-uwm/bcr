<?php
session_start();
$page_title = 'Genre Editor';

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
	include('../includes/header.php');
	require_once('../includes/entity_manager.php');
	log_event($_SESSION['user_id'], "Viewed Genre List");
	echo ("<center>");
	echo ("<h3>Genres</h3><p>");
	echo ("<a href=add.php>Add Genre</a><p>");
	//Set the number of records to display per page
	$display = 5;
	//Count the number of records;
	$query = 'SELECT COUNT(genre_id) FROM genres';
	$result = query($query);
	$row = @mysqli_fetch_array($result, MYSQLI_NUM);
	$records = $row[0]; //get the number of records	

	echo "Total Genres - $records <br>";

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
	$query = "SELECT * FROM genres LIMIT $start, $display";
	$result = query($query);


	//Table header:
	echo "<table cellpadding=5 cellspacing=5 border=1 class=table-list><tr>
	<th>Genre ID</th><th>Genre Name</th><th>*</th><th>*</th></tr>";

	//Fetch and print all the records...
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		echo "<tr><td>" . $row['genre_id'] . "</td>";
		echo "<td>" . $row['genre_name'] . "</td>";
		// hide links longer than 20 characters to not mess up table flow		 
		echo "<td><a href=deleteconfirm.php?id=" . $row['genre_id'] . ">Delete</a></td>";
		echo "<td><a href=update.php?id=" . $row['genre_id'] . ">Update</a></td></tr>";
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
			echo '<td><a href="index.php?s=' . ($start - $display) . '&p=' . $pages . '"> Previous </a></td>';
		}
		//Make all the numbered pages:
		for ($i = 1; $i <= $pages; $i++) {
			if ($i != $current_page) { // if not the current pages, generates links to that page
				echo '<td><a href="index.php?s=' . (($display * ($i - 1))) . '&p=' . $pages . '"> ' . $i . ' </a></td>';
			} else { // if current page, print the page number
				echo '<td>' . $i . '</td>';
			}
		} //End of FOR loop
		//If it is not the last page, make a Next button:
		if ($current_page != $pages) {
			echo '<td><a href="index.php?s=' . ($start + $display) . '&p=' . $pages . '"> Next </a></td>';
		}

		echo '</tr></table>';  //Close the table.
	} //End of pages links
	//include the footer
	include('../includes/footer.php');
}
?>