<?php
session_start();
$page_title = 'Brew City Rentals - Movie Catalog';

require_once('../includes/config.php');

//include the header
require_once('../includes/header.php');
require_once('../includes/entity_manager.php');
log_event($_SESSION['user_id'] ?? 0, "Viewed Movies Catalog");

if (isset($_SESSION['customer_id']) && (isset($_GET['action']) || isset($_POST['action']))) {

	$action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];
	switch ($action) {
		case "add_movie":
			$movie_id = $_GET['movie_id'];
			add_cart_item($movie_id);
			break;
		case "remove_movie":
			$movie_id = $_GET['movie_id'];
			remove_cart_item($movie_id);
			break;
		case "rate_movie":
			$movie_id = $_POST['movie_id'];
			$rating = $_POST['rating'];
			$customer_id = $_SESSION['customer_id'];
			add_rating($customer_id, $movie_id, $rating);
			break;
	}
}

echo ("<center>");
echo ("<h3>BCR Movie Catalog</h3><p>");
if (isset($_SESSION['customer_id'])) {
	echo (generate_cart_icon());
}
//Set the number of records to display per page
$display = 8; // pull from audit report
//Count the number of records;
$query = 'SELECT COUNT(movie_id) FROM movies';
$result = query($query);
$row = @mysqli_fetch_array($result, MYSQLI_NUM);
$records = $row[0]; //get the number of records	

echo "Total Movies - $records <br>";

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
$query = "SELECT * FROM movies LIMIT $start, $display";
$result = query($query);

echo '<div class="movie-grid">';
foreach ($result as $movie) {
	$genreQuery = 'SELECT * FROM genres WHERE genre_id IN (SELECT genre_id FROM movie_genres WHERE movie_id = ' . $movie['movie_id'] . ')';
	$genres = query($genreQuery);

	$ratings = read('movie_ratings', $movie['movie_id'], 'movie_id');
	// available copies
	$availableCopies = available_movie_copies($movie['movie_id']);


	echo '<div class="movie-tile">';
	echo $movie['rental_category_id'] ? '<div class="movie-category">' . get_category($movie['rental_category_id']) . '</div>' : '';
	$thumbnail = $movie['thumbnail_location'] ?: 'notn.png';
	echo '<img src="' . $thumbnailBaseUrl . '/' . $thumbnail . '" alt="' . htmlspecialchars($movie['title']) . '">';
	echo '<h2>' . htmlspecialchars($movie['title']) . '</h2>';

	if (isset($_SESSION['customer_id'])) {
		$movieInCart = is_movie_in_cart($movie['movie_id']);
		if ($availableCopies > 0) {
			echo "<p><b>Available Copies -</b> $availableCopies <br>";
			if ($movieInCart) {
				echo "In Cart <a href='?action=remove_movie&movie_id={$movie['movie_id']}'>Remove</a>";
			} else {

				echo "<a href='?action=add_movie&movie_id={$movie['movie_id']}'>Add to Cart</a>";
			}
		} else {
			echo "<p><b>Currently Out of Stock</b>";
		}
	} else {
		echo "<p><a href='$baseUrl/login.php'>Log In</a> or <a href='$baseUrl/register.php'>Register</a> <br> to <br>Rent Movies";
	}
	echo '<p><b>Length : </b>' . htmlspecialchars($movie['length']) . ' minutes </p>';

	// average rating
	echo '<p><b>Average Rating </b><br>';
	if ($ratings) {

		$rating = array_sum(array_column($ratings, 'rating')) / count($ratings);
		echo '<div class="rating">';

		// full stars
		for ($i = 0; $i < floor($rating); $i++) {
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFD700" class="bi bi-star-fill" viewBox="0 0 16 16">
								<path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.283.95l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z"/>
							</svg>';
		}

		// half stars
		if ($rating - floor($rating) >= 0.5) {
			echo '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#FFD700" class="bi bi-star-half" viewBox="0 0 16 16">
								<path d="M5.354 5.119L7.538.792A.516.516 0 0 1 8 .5c.183 0 .366.097.465.292l2.184 4.327 4.898.696a.537.537 0 0 1 .277.92l-3.523 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256a.519.519 0 0 1-.146.05c-.341.06-.668-.254-.6-.642l.83-4.73L.354 6.32a.534.534 0 0 1 .277-.92l4.898-.696L5.354 5.12zm3.182 2.682l-2.71 2.59.646-3.77-2.738-2.64 3.795-.55L8 2.223 8.708 6.37l3.795.55-2.738 2.64.646 3.77-2.71-2.59z"/>
							</svg>';
		}

		echo '</div>';
	} else {
		echo 'No Ratings Yet<p>';
	}


	// customer rating section
	if (isset($_SESSION['customer_id'])) {
		echo "<form action='index.php' method='POST'>";
		echo "<input type='hidden' name='movie_id' value='{$movie['movie_id']}'>";
		echo "<input type='hidden' name='action' value='rate_movie'>";

		$customer_rating = get_rating($movie['movie_id'], $_SESSION['customer_id'] ?? 0);
		echo "<p><b>Your Rating</b><br>";
		echo "<select name='rating' onchange='this.form.submit();'>";
		if ($customer_rating) {
			echo "<option value='-1'>Remove Rating</option>";
		} else {
			echo "<option value='-1'>Rate Movie!</option>";
		}
		for ($i = 5; $i >= 1; $i--) {
			echo "<option value='$i' " . ($i == $customer_rating ? 'selected' : '') . ">$i Stars</option>";
		}
		echo "</select>";
		echo "</form>";
	}

	// get language
	if ($movie['language']) {
		echo "<p><b>Language: </b>" . $movie['language'] . "<br>";
	}

	// get credits
	$credits = get_credits($movie['movie_id']);
	echo "<div id='credits_{$movie['movie_id']}' class='credits'>";
	echo "<p><b><u>Credits</u></b><br>";
	if ($credits) {
		foreach ($credits as $credit) {
			if ($credit['leading_role'] == 1) {
				echo "<i>{$credit['first_name']} {$credit['last_name']} - {$credit['role']}</i><br>";
			} else {
				echo "{$credit['first_name']} {$credit['last_name']} - {$credit['role']}<br>";
			}
		}
	} else {
		echo "No Credits Found";
	}

	echo "</div>";
	foreach ($genres as $genre) {
		echo '<p><span class="tag" style="background:' . $genre['genre_color'] . '">' . htmlspecialchars($genre['genre_name']) . '</span>';
	}






	echo '</div>';
}
echo '</div>';


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

	echo '</tr></table>'; //Close the table.
} //End of pages links
//include the footer
include('../includes/footer.php');
?>