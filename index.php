<?php
session_start();
//check session first
$page_title = 'Welcome';

require_once('includes/config.php');

if (!isset($_SESSION['email'])) {
    include('includes/header.php');
} else {
    include('includes/header.php');
}

?>

<div>
    <?php
    if (!isset($_SESSION['email'])) {
        ?>
        <h2>Welcome to Brew City Rentals - Your Local Video Store!</h2>

        Welcome to Brew City Rentals, your neighborhood's go-to spot for movie magic and entertainment. Whether you're
        craving a classic film night or on the hunt for the latest blockbuster, we've got your cinematic desires covered.<p>

            At Brew City Rentals, we take pride in curating an extensive movie catalog that spans genres, from heartwarming
            classics to pulse-pounding thrillers. Our shelves are stocked with the latest releases, timeless favorites, and
            hidden gems waiting to be discovered.
        <p>

            How to Get Started:
        <ul>
            <li><a href="https://maps.app.goo.gl/CKWJgoL3kA7T91Eo6" target="_blank">Visit Us</a>: Swing by our store to
                experience the nostalgia of a local video rental spot.</li>
            <li><a href="catalog/">Browse Online</a>: Can't make it in person? Explore our catalog online and reserve your
                picks for pickup.</li>
            <li><a href="register.php">Sign Up</a>: Join Brew City Rentals by creating your account. It's quick, easy, and
                opens the door to a world
                of movie magic.</li>
        </ul>
        <p>
            If you are an existing member, welcome back! Please <a href="login/">log in</a> to access your account.
        <p>
            Brew City Rentals is more than a video rental store; it's a cinematic experience waiting for you. Come on in,
            grab your favorite snacks, and let the movie marathon begin!
            <?php
    } else {
        ?>
        <h2>Welcome to Brew City Rentals,
            <?php echo $_SESSION['customer_name']; ?>!
        </h2>
        Please use the navigation menu to access the various features of the site.

        <?php
    }
    ?>

</div>
<?php
include('includes/footer.php');
?>