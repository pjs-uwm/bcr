<?php
session_start();
$page_title = 'Update Movie';

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
		$movie = array();
		$movie_id = mysqli_real_escape_string($dbc, $_POST['id']);
		$movie['title'] = mysqli_real_escape_string($dbc, $_POST['title']);
		$movie['year_of_release'] = mysqli_real_escape_string($dbc, $_POST['year_of_release']);
		$movie['length'] = mysqli_real_escape_string($dbc, $_POST['length']);
		$movie['language'] = mysqli_real_escape_string($dbc, $_POST['language']);
		$movie['rental_category_id'] = mysqli_real_escape_string($dbc, $_POST['rental_category']);
		if (!$movie['rental_category_id']) {
			$movie['rental_category_id'] = NULL;
		}


		$update = update("movies", "movie_id", $movie_id, $movie);
		delete_entity("movie_genres", "movie_id", $movie_id);
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

		if (isset($_FILES['thumbnail'])) {
			$errors = array();
			$file_name = $_FILES['thumbnail']['name'];
			$file_size = $_FILES['thumbnail']['size'];
			$file_tmp = $_FILES['thumbnail']['tmp_name'];
			$file_type = $_FILES['thumbnail']['type'];
			$file_ext = strtolower(end(explode('.', $_FILES['thumbnail']['name'])));
		
			$extensions = array("jpeg", "jpg", "png");
		
			if ($file_ext && in_array($file_ext, $extensions) === false) {
				$errors[] = "extension not allowed, please choose a JPEG or PNG file.";
			}
		
			if ($file_name && empty($errors)) {
				move_uploaded_file($file_tmp, "../media/thumbnails/" . $file_name);

				$src = imagecreatefromstring(file_get_contents("../media/thumbnails/" . $file_name));
				$dst = imagecreatetruecolor(130, 200);
				imagecopyresampled($dst, $src, 0, 0, 0, 0, 130, 200, imagesx($src), imagesy($src));
				imagedestroy($src);
				$newFileName = "mv" .$movie_id.".png";
				imagepng($dst, "../media/thumbnails/".$newFileName); // adjust format as needed
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

		if ($update) {
			echo "<center><p><b>The Movie - " . $movie['title'] . " has been updated.</b></p>";
			echo "<a href=index.php>Movies</a></center>";
			log_event($_SESSION['user_id'], "Updated Movie $movie_id");
		} else {
			echo "<p>The record could not be updated due to a system error" . mysqli_error($dbc) . "</p>";
		}

		mysqli_close($dbc);
	} // only if submitted by the form	
	else {
		$id = $_GET['id'];
		$query = "SELECT * FROM movies WHERE movie_id=$id";
		$result = query($query);
		$num = mysqli_num_rows($result);
		if ($num != 1) {
			echo "<p>There is no such movie in the database.</p>";
			exit();
		} else {
			$row = mysqli_fetch_array($result, MYSQLI_ASSOC);
		}

		?>

		<form action="update.php" method="post" enctype="multipart/form-data">
			Title: <input name="title" size=100 value='<?php echo $row['title']; ?>'>
			<p>
				Year of Release: <select name="year_of_release" id="year_of_release">
					<?php

					for ($i = date("Y"); $i >= 1920; $i--) {
						if ($i == $row['year_of_release'])
							echo "<option value=$i selected>$i</option>";
						else
							echo "<option value=$i>$i</option>";
					}
					?>
				</select>
			<p>
				Length of Movie (Minutes): <input type="number" name="length" value='<?php echo $row['length']; ?>'>
			<p>
				Language: <input name="language" size=50 value='<?php echo $row['language']; ?>'>
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
						if ($rentalCategoryID == $row['rental_category_id'])
							echo '<option value="' . $rentalCategoryID . '" selected>' . $rentalCategoryDisplay . '</option>';
						else {
							echo '<option value="' . $rentalCategoryID . '">' . $rentalCategoryDisplay . '</option>';
						}
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
					$assignedGenres = read("movie_genres", "movie_id", $id);
					$assignedGenreIDs = array();
					if ($assignedGenres) {
						foreach ($assignedGenres as $assignedGenre) {
							$assignedGenreIDs[] = $assignedGenre['genre_id'];
						}
					}
					foreach ($genreResult as $genre) {
						$genreID = $genre['genre_id'];
						$genreName = $genre['genre_name'];
						$selected = in_array($genreID, $assignedGenreIDs) ? 'selected' : '';
						echo '<option value="' . $genreID . '" ' . $selected . ' >' . $genreName . '</option>';
					}
				}
				echo '</select>';
				?>

			<p>
				Current Thumbnail:
				<?php
					// thumbnail display and upload
					$thumbnail_file = $row['thumbnail_location'];
					if ($thumbnail_file) {
						$thumbnailUrl = $thumbnailBaseUrl . $thumbnail_file;
						echo "<img src='$thumbnailUrl' width=100 height=100>";
					}

					
				?>
				<p>
				<label for="thumbnail">New Thumbnail Image:</label>
				<input type="file" id="thumbnail" name="thumbnail">
				<p>
				<input type=submit value=update>
				<input type=reset value=reset>
				<input type=hidden name="id" value="<?php echo $row['movie_id']; ?>">
				<input type=hidden name=submitted value=true>
		</form>
		<?php
	}
	//include the footer
	include("../includes/footer.php");
}
?>