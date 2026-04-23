<!DOCTYPE html>

<?php
require("connect-db.php");
session_start();
require("header.php");

$username = $_SESSION['username'];

$stmt = $db->prepare("SELECT RID, movie, rating, review_text FROM review WHERE username = ?");
$stmt->execute([$username]);
$list_of_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<hr/>
<div class="container">
<h3>My Reviews</h3>
<div class="row justify-content-center">
<table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
  <thead>
  <tr style="background-color:#B0B0B0">
    <th><b>Movie</b></th>
    <th><b>Rating</b></th>
    <th><b>Review</b></th>
  </tr>
  </thead>

  <?php foreach ($list_of_reviews as $row): ?>
  <tr>
     <td><?php echo $row['movie']; ?></td>
     <td><?php echo $row['rating']; ?></td>
     <td><?php echo $row['review_text']; ?></td>
  </tr>
  <?php endforeach; ?>

</table>
</div>

</body>
</html>