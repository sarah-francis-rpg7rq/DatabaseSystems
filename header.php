<?php // session_start(); ?>

<header>
  <nav class="navbar navbar-expand-md navbar-dark bg-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="#">MyNetflixRatings</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar" aria-controls="collapsibleNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="collapsibleNavbar">
        <ul class="navbar-nav ms-auto">
          <!-- check if currently logged in, display Log out button
               otherwise, display sign up and log in buttons -->
        
            <li class="nav-item">
              <a class="nav-link" href="search.php">All Movies</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="myReviews.php">My Reviews</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="signout.php">Sign out</a>
            </li>
         

         
        </ul>
      </div>
    </div>
  </nav>

</header>