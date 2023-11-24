<?php
session_start();
$page_title = 'Add Movie';

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
		$movie = array();
		$movie['title'] = mysqli_real_escape_string($dbc, $_POST['title']);
		$movie['year_of_release'] = mysqli_real_escape_string($dbc, $_POST['year_of_release']);
		$movie['language'] = mysqli_real_escape_string($dbc, $_POST['language']);
		$movie['length'] = mysqli_real_escape_string($dbc, $_POST['length']);
		if (mysqli_real_escape_string($dbc, $_POST['rental_category'])) {
			$movie['rental_category_id'] = mysqli_real_escape_string($dbc, $_POST['rental_category']);
		}

		$insert = create_id_return("movies", $movie);
		if ($insert) {
			$movie_id = $insert;

			// add genres
			if (isset($_POST['genres'])) {
				$genres = $_POST['genres'];
				foreach ($genres as $genre) {
					$genre_id = mysqli_real_escape_string($dbc, $genre);
					$query = "INSERT INTO movie_genres (genre_id, movie_id) VALUES ($genre_id, $movie_id)";
					$result = mysqli_query($dbc, $query);
					if (!$result) {
						echo "<p>The genre could not be assigned to the movie - " . mysqli_error($dbc) . "</p>" . $query;
					}
					log_event($_SESSION['user_id'], "Added Genre $genre_id to Movie $movie_id");
				}
			}

			if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
				$errors = array();
				$file_name = $_FILES['thumbnail']['name'];
				$file_size = $_FILES['thumbnail']['size'];
				$file_tmp = $_FILES['thumbnail']['tmp_name'];
				$file_type = $_FILES['thumbnail']['type'];
				$file_ext = strtolower(end(explode('.', $_FILES['thumbnail']['name'])));

				$extensions = array("jpeg", "jpg", "png");

				if (in_array($file_ext, $extensions) === false) {
					$errors[] = "extension not allowed, please choose a JPEG or PNG file.";
				}

				if (empty($errors) == true) {
					move_uploaded_file($file_tmp, "../media/thumbnails/" . $file_name);

					$src = imagecreatefromstring(file_get_contents("../media/thumbnails/" . $file_name));
					$dst = imagecreatetruecolor(130, 200);
					imagecopyresampled($dst, $src, 0, 0, 0, 0, 130, 200, imagesx($src), imagesy($src));
					imagedestroy($src);
					$newFileName = "mv" . $movie_id . ".png";
					imagepng($dst, "../media/thumbnails/" . $newFileName); // adjust format as needed
					imagedestroy($dst);
					unlink("../media/thumbnails/" . $file_name);
					$movieUpdate = array();
					$movieUpdate['thumbnail_location'] = $newFileName;
					$update = update("movies", "movie_id", $movie_id, $movieUpdate);
					log_event($_SESSION['user_id'], "Added Thumbnail to Movie $movie_id");
				} else {
					print_r($errors);
				}
			}


			echo "<center><p><b>New Movie " . $movie['title'] . " has been added with ID $movie_id.</b></p>";
			echo "<a href=index.php>Show All Movies</a></center>";
			log_event($_SESSION['user_id'], "Added Movie " . $movie['title'] . " with ID $movie_id");
		} else {
			echo "<p>The record could not be added due to a system error" . mysqli_error($dbc) . "</p>" . $query;
		}

	} // only if submitted by the form
	mysqli_close($dbc);
	?>
	<form action="add.php" method="post" enctype="multipart/form-data">
		Title: <input name="title" size=100>
		<p>
			Year of Release: <select name="year_of_release" id="year_of_release">
				<?php

				for ($i = date("Y"); $i >= 1920; $i--) {
					echo "<option value=$i>$i</option>";
				}
				?>
			</select>
		<p>
			Length of Movie (Minutes): <input type="number" name="length">
		<p>
			Language: <input type="text" name="language">
		<p>
			Rental Category:
			<?php

			$rentalResult = read("rental_category");
			echo '<select name="rental_category" id="rental_category">';

			if (count($rentalResult) < 1) {
				echo '<option value="">-- No Rental Categories Available --</option>';
			} else {
				echo '<option value="">-- Select Rental Category --</option>';
				foreach ($rentalResult as $rentalCategory) {
					$rentalCategoryID = $rentalCategory['rental_category_id'];
					$rentalCategoryDisplay = $rentalCategory['rental_category'];

					echo '<option value="' . $rentalCategoryID . '">' . $rentalCategoryDisplay . '</option>';

				}
			}
			echo '</select>';
			?>
		<p>
			Genre(s):
			<?php

			$genreResult = read("genres");
			echo '<select name="genres[]" id="genres" multiple>';

			if (count($genreResult) < 1) {
				echo '<option value="">-- No Genres Available --</option>';
			} else {
				foreach ($genreResult as $genre) {
					$genreID = $genre['genre_id'];
					$genreName = $genre['genre_name'];

					echo '<option value="' . $genreID . '">' . $genreName . '</option>';

				}
			}
			echo '</select>';
			?>

			<label for="thumbnail">Thumbnail Image:</label>
			<input type="file" id="thumbnail" name="thumbnail">

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