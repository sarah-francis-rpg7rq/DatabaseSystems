<?php
require_once __DIR__ . '/connect-db.php';
require_once __DIR__ . '/netflix-db.php';

session_start();

$allowedSorts = ['title_asc', 'title_desc', 'year_asc', 'year_desc', 'rating_desc', 'rating_asc'];
$sort = $_GET['sort'] ?? 'title_asc';
if (!in_array($sort, $allowedSorts, true)) {
    $sort = 'title_asc';
}

$filters = [
    'q' => isset($_GET['q']) ? trim((string) $_GET['q']) : '',
];

$ym = isset($_GET['year_min']) ? trim((string) $_GET['year_min']) : '';
$yx = isset($_GET['year_max']) ? trim((string) $_GET['year_max']) : '';
if ($ym !== '' && ctype_digit($ym)) {
    $filters['year_min'] = (int) $ym;
}
if ($yx !== '' && ctype_digit($yx)) {
    $filters['year_max'] = (int) $yx;
}

$ratingsList = getFilterContentRatings($db);
$crGet = isset($_GET['cr']) ? (string) $_GET['cr'] : '';
if ($crGet !== '' && in_array($crGet, $ratingsList, true)) {
    $filters['content_rating'] = $crGet;
}

$countries = getFilterCountries($db);
$allowedCids = array_map('intval', array_column($countries, 'CID'));
$cidGet = isset($_GET['cid']) ? (int) $_GET['cid'] : 0;
if ($cidGet > 0 && in_array($cidGet, $allowedCids, true)) {
    $filters['cid'] = $cidGet;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
}
$perPage = 18;
$total = searchMoviesCount($db, $filters);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$movies = searchMoviesPage($db, $filters, $sort, $perPage, $offset);

function search_query_params(array $overrides = [])
{
    $base = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($base[$k]);
        } else {
            $base[$k] = $v;
        }
    }
    return http_build_query($base);
}

$activeNav = 'search';
$pageTitle = 'Search Netflix — My Netflix Ratings';
require_once __DIR__ . '/app-shell-begin.php';
?>

<form method="get" action="search.php" class="d-flex flex-wrap gap-2 align-items-stretch mb-4" id="searchToolbarForm">
  <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
  <?php if (isset($filters['year_min'])): ?>
    <input type="hidden" name="year_min" value="<?php echo h((string) $filters['year_min']); ?>">
  <?php endif; ?>
  <?php if (isset($filters['year_max'])): ?>
    <input type="hidden" name="year_max" value="<?php echo h((string) $filters['year_max']); ?>">
  <?php endif; ?>
  <?php if (!empty($filters['content_rating'])): ?>
    <input type="hidden" name="cr" value="<?php echo h($filters['content_rating']); ?>">
  <?php endif; ?>
  <?php if (!empty($filters['cid'])): ?>
    <input type="hidden" name="cid" value="<?php echo h((string) $filters['cid']); ?>">
  <?php endif; ?>
<?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="admin.php">Go to Admin Panel</a>
<?php endif; ?>
  <div class="flex-grow-1" style="min-width: 200px;">
    <label class="visually-hidden" for="searchQ">Search the database by title</label>
    <input id="searchQ" type="search" name="q" value="<?php echo h($filters['q']); ?>"
           class="form-control toolbar-input w-100 h-100" placeholder="Search here">
  </div>
  <div class="dropdown align-self-stretch d-flex align-items-stretch">
    <button class="btn toolbar-btn-white dropdown-toggle h-100 rounded-2" type="button" id="sortMenuButton"
            data-bs-toggle="dropdown" aria-expanded="false" aria-haspopup="true" aria-controls="sortMenuList">
      Sort by
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" id="sortMenuList" aria-labelledby="sortMenuButton">
      <?php
      $sortMenu = [
          'title_asc' => 'Title (A–Z)',
          'title_desc' => 'Title (Z–A)',
          'year_desc' => 'Year (newest)',
          'year_asc' => 'Year (oldest)',
          'rating_desc' => 'Avg rating (high)',
          'rating_asc' => 'Avg rating (low)',
      ];
      foreach ($sortMenu as $sortKey => $sortLabel) {
          $href = 'search.php?' . search_query_params(['sort' => $sortKey, 'page' => null]);
          $active = $sort === $sortKey ? ' active' : '';
          echo '<li><a class="dropdown-item' . $active . '" href="' . h($href) . '">' . h($sortLabel) . '</a></li>';
      }
      ?>
    </ul>
  </div>
  <button class="btn toolbar-btn-white" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
    Add Filter
  </button>
  <button type="submit" class="visually-hidden" tabindex="-1">Search</button>
</form>

<p class="text-secondary small mb-3"><?php echo h((string) $total); ?> title<?php echo $total === 1 ? '' : 's'; ?> found.
  Average ratings are read-only (out of 5).</p>

<?php foreach ($movies as $m): ?>
  <div class="movie-card">
    <a class="movie-card-main" href="reviewsByMovie.php?mid=<?php echo (int) $m['MID']; ?>">
      <div class="movie-card-body">
        <div class="movie-card-title"><?php echo h($m['title']); ?></div>
        <div class="movie-card-meta">
          <span class="meta-row"><strong>Director</strong> <?php echo h($m['director_name']); ?></span>
          <span class="meta-row"><strong>Release Date</strong> <?php echo h((string) $m['year']); ?></span>
        </div>
      </div>
    </a>
    <div class="d-flex flex-column align-items-end justify-content-between gap-2">
      <div class="movie-card-rating" aria-label="Average user rating out of 5 (read only)">
        <?php
        $avgDisp = format_movie_avg_rating_display($m['avg_rating'] ?? null);
        if ($avgDisp !== null) {
            echo h($avgDisp) . '<span class="fs-6 fw-normal">/5</span>';
        } else {
            echo '—';
        }
        ?>
      </div>
      <a class="movie-card-plus" href="myReviews.php?for_mid=<?php echo (int) $m['MID']; ?>"
         title="Add a review" aria-label="Add a review for this title">+</a>
    </div>
  </div>
<?php endforeach; ?>

<?php if (count($movies) === 0): ?>
  <p class="text-secondary mt-4">No movies match your search and filters.</p>
<?php endif; ?>

<?php if ($totalPages > 1): ?>
  <?php $pageSeq = pagination_page_sequence($page, $totalPages); ?>
  <nav class="compact-pagination mt-4 mb-0" aria-label="Results pages">
    <ul class="pagination pagination-sm flex-wrap justify-content-center gap-1 mb-0">
      <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
        <?php if ($page <= 1): ?>
          <span class="page-link bg-dark text-white border-secondary">Prev</span>
        <?php else: ?>
          <a class="page-link bg-dark text-white border-secondary"
             href="search.php?<?php echo h(search_query_params(['page' => $page - 1])); ?>">Prev</a>
        <?php endif; ?>
      </li>
      <?php foreach ($pageSeq as $item): ?>
        <?php if ($item === null): ?>
          <li class="page-item disabled"><span class="page-link bg-dark text-white border-secondary">&hellip;</span></li>
        <?php else: ?>
          <li class="page-item <?php echo $item === $page ? 'active' : ''; ?>">
            <?php if ($item === $page): ?>
              <span class="page-link bg-danger border-danger text-white"><?php echo (int) $item; ?></span>
            <?php else: ?>
              <a class="page-link bg-dark text-white border-secondary"
                 href="search.php?<?php echo h(search_query_params(['page' => $item])); ?>"><?php echo (int) $item; ?></a>
            <?php endif; ?>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
      <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
        <?php if ($page >= $totalPages): ?>
          <span class="page-link bg-dark text-white border-secondary">Next</span>
        <?php else: ?>
          <a class="page-link bg-dark text-white border-secondary"
             href="search.php?<?php echo h(search_query_params(['page' => $page + 1])); ?>">Next</a>
        <?php endif; ?>
      </li>
    </ul>
    <p class="text-secondary text-center small mt-2 mb-0">Page <?php echo (int) $page; ?> of <?php echo (int) $totalPages; ?></p>
  </nav>
<?php endif; ?>

<div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="filterOffcanvasLabel">Filters</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <p class="small text-secondary mb-3">Filter by country, TV rating, and release year. Title search uses the bar above.</p>
    <form method="get" action="search.php" class="vstack gap-3">
      <input type="hidden" name="sort" value="<?php echo h($sort); ?>">
      <input type="hidden" name="q" value="<?php echo h($filters['q']); ?>">
      <div class="row g-2">
        <div class="col-6">
          <label class="form-label">Release year from</label>
          <input type="number" name="year_min" class="form-control" min="1900" max="2100"
                 value="<?php echo isset($filters['year_min']) ? h((string) $filters['year_min']) : ''; ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Release year to</label>
          <input type="number" name="year_max" class="form-control" min="1900" max="2100"
                 value="<?php echo isset($filters['year_max']) ? h((string) $filters['year_max']) : ''; ?>">
        </div>
      </div>
      <div>
        <label class="form-label">TV rating</label>
        <select name="cr" class="form-select">
          <option value="">Any</option>
          <?php foreach ($ratingsList as $r): ?>
            <option value="<?php echo h($r); ?>" <?php echo (!empty($filters['content_rating']) && $filters['content_rating'] === $r) ? 'selected' : ''; ?>>
              <?php echo h($r); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label">Country</label>
        <select name="cid" class="form-select">
          <option value="">Any</option>
          <?php foreach ($countries as $c): ?>
            <option value="<?php echo (int) $c['CID']; ?>" <?php echo (!empty($filters['cid']) && (int) $filters['cid'] === (int) $c['CID']) ? 'selected' : ''; ?>>
              <?php echo h($c['country_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn toolbar-btn-white w-100 border-secondary">Apply filters</button>
      <a href="search.php" class="btn btn-outline-light">Clear all</a>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/app-shell-end.php'; ?>
