<?php
session_start();
require_once __DIR__ . '/connect-db.php';
require_once __DIR__ . '/netflix-db.php';

$prefill_movie = '';
$prefill_mid = 0;
if (isset($_GET['for_mid'])) {
    $forMid = (int) $_GET['for_mid'];
    if ($forMid > 0) {
        $ps = $db->prepare('SELECT title FROM movie WHERE MID = ? LIMIT 1');
        $ps->execute([$forMid]);
        $prow = $ps->fetch(PDO::FETCH_ASSOC);
        if ($prow) {
            $prefill_movie = $prow['title'];
            $prefill_mid = $forMid;
        }
    }
}

$username = isset($_SESSION['username']) ? trim((string) $_SESSION['username']) : "sarah";
$review_error = '';

$edit_review = null; //variable to holh review being edited 

//delete review
if (isset($_POST['delete_id'])) {
    $rid = $_POST['delete_id'];

    $del = db_delete_review_linked($db, $rid);
    if (!$del['ok']) {
        $review_error = $del['error'] ?? 'Delete failed.';
    }
}

//loading review into edit form
if (isset($_POST['edit_id'])) {
    $rid = $_POST['edit_id'];

    //fetching the review to put into "edit" form
    $stmt = $db->prepare("SELECT RID, movie, rating, review_text FROM review WHERE RID = ?");
    $stmt->execute([$rid]);
    $edit_review = $stmt->fetch(PDO::FETCH_ASSOC);
}

//update review
if (isset($_POST['update_id'])) {
    $rid = $_POST['update_id'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    //update rating and review message
    $stmt = $db->prepare("UPDATE review SET rating = ?, review_text = ? WHERE RID = ?");
    $stmt->execute([$rating, $review_text, $rid]);
}

//add review
if (isset($_POST['add_review'])) {
    $movie = $_POST['movie'];
    $rating = $_POST['rating'];
    $review_text = $_POST['review_text'];

    $movie_mid = isset($_POST['movie_mid']) ? (int) $_POST['movie_mid'] : 0;
    $res = db_add_review_linked($db, $username, $movie, $rating, $review_text, $movie_mid);
    if (!$res['ok']) {
        $review_error = $res['error'] ?? 'Could not add review.';
    }
}

//fetching all the reviews for this user
$stmt = $db->prepare("SELECT RID, movie, rating, review_text FROM review WHERE username = ?");
$stmt->execute([$username]);
$list_of_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$activeNav = 'ratings';
$pageTitle = 'My Ratings';
require_once __DIR__ . '/app-shell-begin.php';
?>

<hr/>
<div class="container">
<h3>My Reviews</h3>

<!-- add review form -->
<form method="POST" action="">
    <p>
        <?php if ($prefill_mid > 0): ?>
            <input type="hidden" name="movie_mid" value="<?php echo (int) $prefill_mid; ?>">
        <?php endif; ?>
        Movie: <input type="text" name="movie" required value="<?php echo h($prefill_movie); ?>" <?php echo $prefill_mid > 0 ? 'readonly' : ''; ?>>
    </p>
    <p>
        Rating (1-5): <input type="number" name="rating" min="1" max="5" required>
    </p>
    <p>
        Review: <input type="text" name="review_text" required>
    </p>
    <input type="submit" name="add_review" value="Add Review">
</form>

<?php if ($review_error !== ''): ?>
  <p style="color:#ff4d4d;"><strong><?php echo h($review_error); ?></strong></p>
<?php endif; ?>

<!-- edit review form (only shows if edit button is clicked) -->
<?php if ($edit_review): ?>
<hr/>
<h3>Edit Review</h3>
<form method="POST" action="">
    <input type="hidden" name="update_id" value="<?php echo $edit_review['RID']; ?>"> <!-- hidden ID so we know which review to update -->

    <p>
        Movie: <input type="text" value="<?php echo $edit_review['movie']; ?>" disabled>
    </p>

    <p>
        Rating (1-5):
        <input type="number" name="rating" min="1" max="5"
               value="<?php echo $edit_review['rating']; ?>" required>
    </p>

    <p>
        Review:
        <input type="text" name="review_text"
               value="<?php echo $edit_review['review_text']; ?>" required>
    </p>

    <input type="submit" value="Update Review">
</form>
<?php endif; ?>

<!-- reviews table -->
<div class="row justify-content-center">
<table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
  <thead>
  <tr style="background-color:#B0B0B0">
    <th><b>Movie</b></th>
    <th><b>Rating</b></th>
    <th><b>Review</b></th>
    <th><b>Edit</b></th>
    <th><b>Delete</b></th>
  </tr>
  </thead>

  <?php foreach ($list_of_reviews as $row): ?>
  <tr>
     <td><?php echo $row['movie']; ?></td>
     <td><?php echo $row['rating']; ?></td>
     <td><?php echo $row['review_text']; ?></td>

     <!-- edit button -->
     <td>
         <form method="POST" action="">
             <input type="hidden" name="edit_id" value="<?php echo $row['RID']; ?>">
             <input type="submit" value="Edit">
         </form>
     </td>

     <!-- delete button -->
     <td>
         <form method="POST" action="">
             <input type="hidden" name="delete_id" value="<?php echo $row['RID']; ?>">
             <input type="submit" value="Delete">
         </form>
     </td>
  </tr>
  <?php endforeach; ?>

</table>
</div>
</div>

<?php require_once __DIR__ . '/app-shell-end.php'; ?>