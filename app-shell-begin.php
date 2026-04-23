<?php
if (!isset($activeNav)) {
    $activeNav = '';
}
if (!isset($pageTitle)) {
    $pageTitle = 'My Netflix Ratings';
}
if (!function_exists('h')) {
    function h($s)
    {
        return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo h($pageTitle); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    :root {
      --app-bg: #000;
      --sidebar-bg: #1a1a1a;
      --card-bg: #d3d3d3;
      --accent: #e50914;
    }
    body {
      margin: 0;
      min-height: 100vh;
      background: var(--app-bg);
      color: #e8e8e8;
    }
    .app-wrap {
      display: flex;
      min-height: 100vh;
    }
    .app-sidebar {
      width: 220px;
      flex-shrink: 0;
      background: var(--sidebar-bg);
      padding: 2rem 1.25rem;
      border-right: 1px solid #333;
    }
    .app-sidebar .brand {
      font-size: 1.25rem;
      font-weight: 700;
      color: var(--accent);
      line-height: 1.2;
      margin-bottom: 2rem;
      letter-spacing: -0.02em;
    }
    .app-sidebar nav a {
      display: block;
      padding: 0.55rem 0;
      color: #bbb;
      text-decoration: none;
      font-size: 0.95rem;
    }
    .app-sidebar nav a:hover {
      color: #fff;
    }
    /* Active page: red accent (not white), same family as My Ratings */
    .app-sidebar nav a.nav-active {
      color: var(--accent);
      font-weight: 600;
    }
    .app-sidebar nav a.nav-accent {
      color: var(--accent);
    }
    .app-sidebar nav a.nav-accent.nav-active {
      color: #ff4d4d;
    }
    .app-main {
      flex: 1;
      padding: 1.5rem 2rem calc(3.5rem + env(safe-area-inset-bottom, 0px));
      min-width: 0;
    }
    .compact-pagination {
      max-width: 100%;
      padding-bottom: 1.5rem;
    }
    .compact-pagination .pagination {
      row-gap: 0.35rem;
    }
    .compact-pagination .page-link {
      border-radius: 0.25rem;
    }
    .movie-card {
      background: var(--card-bg);
      color: #111;
      border-radius: 4px;
      padding: 1rem 1.25rem;
      margin-bottom: 1rem;
      display: flex;
      align-items: stretch;
      gap: 1rem;
      transition: filter 0.15s ease;
    }
    .movie-card:hover {
      filter: brightness(1.03);
    }
    a.movie-card-main {
      flex: 1;
      min-width: 0;
      text-decoration: none;
      color: #111;
    }
    a.movie-card-main:hover {
      color: #000;
    }
    .movie-card-body {
      min-width: 0;
    }
    .movie-card-title {
      font-weight: 700;
      font-size: 1.35rem;
      margin-bottom: 0.35rem;
    }
    .movie-card-meta {
      font-size: 0.85rem;
      color: #333;
    }
    .movie-card-meta .meta-row {
      display: block;
      margin-bottom: 0.2rem;
    }
    .movie-card-meta .meta-row:last-child {
      margin-bottom: 0;
    }
    .movie-card-rating {
      font-size: 1.35rem;
      font-weight: 700;
      color: #111;
      white-space: nowrap;
      align-self: flex-start;
    }
    a.movie-card-plus {
      align-self: flex-end;
      width: 36px;
      height: 36px;
      background: #fff;
      border: 1px solid #999;
      border-radius: 2px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
      font-weight: 300;
      line-height: 1;
      color: #000;
      flex-shrink: 0;
      text-decoration: none;
    }
    a.movie-card-plus:hover {
      background: #f5f5f5;
      color: #000;
    }
    .toolbar-input {
      background: #d0d0d0;
      border: 1px solid #999;
      color: #111;
    }
    .toolbar-input::placeholder {
      color: #555;
    }
    .toolbar-btn {
      background: #d0d0d0;
      border: 1px solid #999;
      color: #111;
    }
    .toolbar-btn:hover {
      background: #e0e0e0;
      color: #111;
    }
    .toolbar-btn-white {
      background: #fff;
      border: 1px solid #000;
      color: #000;
      font-weight: 700;
      padding: 0.45rem 1rem;
      border-radius: 2px;
    }
    .toolbar-btn-white:hover {
      background: #f0f0f0;
      color: #000;
    }
    a.toolbar-btn-white {
      display: inline-block;
      text-align: center;
      text-decoration: none;
      line-height: 1.5;
    }
  </style>
</head>
<body>
<div class="app-wrap">
  <aside class="app-sidebar">
    <div class="brand">My Netflix Ratings</div>
    <nav>
      <a href="search.php" class="<?php echo $activeNav === 'search' ? 'nav-active' : ''; ?>">Search Netflix</a>
      <a href="myReviews.php" class="nav-accent <?php echo $activeNav === 'ratings' ? 'nav-active' : ''; ?>">My Ratings</a>
      <a href="signout.php" class="mt-4 text-secondary small">Sign out</a>
    </nav>
  </aside>
  <main class="app-main">
