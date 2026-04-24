<?php
session_start();
require_once __DIR__ . '/connect-db.php';
require_once __DIR__ . '/netflix-db.php';

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$review_error = '';

$mid = isset($_GET['mid']) ? (int) $_GET['mid'] : 0;
$movie = $mid > 0 ? getMovieByMid($db, $mid) : null;

//admin delete review
if (isset($_POST['delete_review_id'])) {
    if ($isAdmin) {
        $rid = (int) $_POST['delete_review_id'];

        $del = db_delete_review_linked($db, $rid);
        if (!$del['ok']) {
            $review_error = $del['error'] ?? 'Delete failed.';
        }
    } else {
        $review_error = 'Access denied. Admins only.';
    }
}

if (!$movie) {
    $activeNav = '';
    $pageTitle = 'Movie not found';
    require_once __DIR__ . '/app-shell-begin.php';
    echo '<p class="text-secondary">No movie exists for that link. <a href="search.php" class="link-light">Back to search</a></p>';
    require_once __DIR__ . '/app-shell-end.php';
    exit;
}

$MID = (int) $movie['MID'];
$limit = 10;

$userFilter = isset($_GET['user']) ? trim((string) $_GET['user']) : '';

$pn = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$start_from = ($pn - 1) * $limit;

if ($userFilter !== '') {
    $list_of_reviews = getReviewsbyMID_username($MID, $userFilter, $limit, $start_from);
    $num_reviews = getCountReviews($MID, $userFilter);
} else {
    $list_of_reviews = getReviewsbyMID($MID, $limit, $start_from);
    $num_reviews = getCountReviews($MID);
}

$countVal = isset($num_reviews[0]['review_count']) ? (int) $num_reviews[0]['review_count'] : 0;
$total_pages = max(1, (int) ceil($countVal / $limit));

if ($pn > $total_pages) {
    $pn = $total_pages;
    $start_from = ($pn - 1) * $limit;
    if ($userFilter !== '') {
        $list_of_reviews = getReviewsbyMID_username($MID, $userFilter, $limit, $start_from);
    } else {
        $list_of_reviews = getReviewsbyMID($MID, $limit, $start_from);
    }
}

$activeNav = '';
$pageTitle = $movie['title'] . ' — Reviews';
require_once __DIR__ . '/app-shell-begin.php';
?>

<div class="mb-3">
  <a href="search.php" class="link-secondary text-decoration-none small">← Back to search</a>
</div>

<div class="row g-3 mb-3">
  <div class="col">
    <h1 class="h2" style="color: #ff6b6b;"><?php echo h($movie['title']); ?></h1>
    <p class="text-secondary mb-0">
      <?php echo h($movie['director_name']); ?> · <?php echo h((string) $movie['year']); ?>
      · <?php echo h($movie['content_rating']); ?>
    </p>
  </div>
  <div class="col-auto text-end">
    <div class="text-white-50 small">Average rating (read-only, out of 5)</div>
    <div class="fs-3 fw-bold text-white">
      <?php
      $avgDisp = format_movie_avg_rating_display($movie['avg_rating'] ?? null);
      if ($avgDisp !== null) {
          echo h($avgDisp) . ' / 5';
      } else {
          echo '—';
      }
      ?>
    </div>
  </div>
</div>

<hr class="border-secondary"/>

<div class="bg-light text-dark p-4 rounded">
  <h3 class="h5 mb-3">Reviews</h3>
  
  <?php if ($review_error !== ''): ?>
    <p class="text-danger"><strong><?php echo h($review_error); ?></strong></p>
  <?php endif; ?>

  <form method="get" action="reviewsByMovie.php" class="mb-3">
    <input type="hidden" name="mid" value="<?php echo (int) $MID; ?>">
    <input type="hidden" name="page" value="1">
    <div class="d-flex flex-wrap gap-2 align-items-center">
      <label for="user_to_search" class="mb-0">Filter by username</label>
      <input type="text" class="form-control" style="max-width: 16rem;" id="user_to_search" name="user"
             value="<?php echo h($userFilter); ?>">
      <button type="submit" class="btn btn-dark">Apply</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-sm mb-0">
      <thead class="table-secondary">
      <tr>
        <th>User</th>
        <th>Rating</th>
        <th>Review</th>
        <?php if ($isAdmin): ?>
          <th>Admin Action</th>
        <?php endif; ?>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($list_of_reviews as $row): ?>
        <tr>
          <td><?php echo h($row['username']); ?></td>
          <td><?php echo h((string) $row['rating']); ?></td>
          <td><?php echo h($row['review_text']); ?></td>

          <?php if ($isAdmin): ?>
            <td>
              <form method="POST" action="">
                <input type="hidden" name="delete_review_id" value="<?php echo (int) $row['RID']; ?>">
                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
              </form>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (count($list_of_reviews) === 0): ?>
    <p class="text-muted mt-3 mb-0">No reviews yet for this title.</p>
  <?php endif; ?>

  <?php if ($total_pages > 1): ?>
    <?php $reviewPageSeq = pagination_page_sequence($pn, $total_pages); ?>
    <nav class="compact-pagination mt-3 mb-0" aria-label="Review pages">
      <ul class="pagination pagination-sm flex-wrap justify-content-center gap-1 mb-0">
        <li class="page-item <?php echo $pn <= 1 ? 'disabled' : ''; ?>">
          <?php if ($pn <= 1): ?>
            <span class="page-link">Prev</span>
          <?php else: ?>
            <a class="page-link" href="<?php echo h('reviewsByMovie.php?' . reviews_movie_query_string($MID, $pn - 1, $userFilter)); ?>">Prev</a>
          <?php endif; ?>
        </li>
        <?php foreach ($reviewPageSeq as $item): ?>
          <?php if ($item === null): ?>
            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
          <?php else: ?>
            <li class="page-item <?php echo $item === $pn ? 'active' : ''; ?>">
              <?php if ($item === $pn): ?>
                <span class="page-link"><?php echo (int) $item; ?></span>
              <?php else: ?>
                <a class="page-link" href="<?php echo h('reviewsByMovie.php?' . reviews_movie_query_string($MID, $item, $userFilter)); ?>"><?php echo (int) $item; ?></a>
              <?php endif; ?>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
        <li class="page-item <?php echo $pn >= $total_pages ? 'disabled' : ''; ?>">
          <?php if ($pn >= $total_pages): ?>
            <span class="page-link">Next</span>
          <?php else: ?>
            <a class="page-link" href="<?php echo h('reviewsByMovie.php?' . reviews_movie_query_string($MID, $pn + 1, $userFilter)); ?>">Next</a>
          <?php endif; ?>
        </li>
      </ul>
      <p class="text-muted text-center small mt-2 mb-0">Page <?php echo (int) $pn; ?> of <?php echo (int) $total_pages; ?></p>
    </nav>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/app-shell-end.php'; ?>
