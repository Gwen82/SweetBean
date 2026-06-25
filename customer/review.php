<?php

session_start();

include("../config/database.php");

if(isset($_POST['submit']))
{
    $user_id = $_SESSION['user_id'];

    $menu_id = $_POST['menu_id'];

    $rating = $_POST['rating'];

    $review = $_POST['review'];

    mysqli_query($conn,

    "INSERT INTO reviews

    (user_id,
    menu_id,
    rating,
    review)

    VALUES

    ('$user_id',
    '$menu_id',
    '$rating',
    '$review')");

    echo "Review Submitted";
}
?>

<form method="POST">

Menu ID

<input type="number"
name="menu_id">

<br><br>

Rating (1-5)

<input type="number"
name="rating"
min="1"
max="5">

<br><br>

<textarea
name="review">
</textarea>

<br><br>

<button name="submit">
Submit Review
</button>

</form>