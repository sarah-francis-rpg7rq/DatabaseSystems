<!DOCTYPE html>

<?php
require("connect-db.php");   
require("header.php");  
require("netflix-db.php");   

//https://www.geeksforgeeks.org/php/how-to-pass-form-variables-from-one-page-to-other-page-in-php/

// Initialize the session
//session_start();
     
// Store the submitted data sent
// via POST method, stored 

// Temporarily in $_POST structure.
//get user from previous page
//$_SESSION['user'] = $_POST['user_name'];

//get movie selected from previous page
//$_SESSION['movie_selected']
      //  = $_POST['movie_selected'];
     
      //for now I am testing with MID=6

$MID=1;

$limit = 2;


if (isset($_GET["page"])){
    $pn = ($_GET["page"]);
}
else{
        $pn=1;
};



$start_from = ($pn -1) *$limit;


?>




<!-- from POTD code for list of visitors table -->
<hr/>
<div class="container">
<h3>Reviews for (Movie Name would go here)</h3>
<div class="row justify-content-center">
<table class="w3-table w3-bordered w3-card-4 center" style="width:100%">
  <thead>
  <tr style="background-color:#B0B0B0">
    <th><b>user</b></th>
    <th><b>Rating</b></th>
    <th><b>Review</b></th>
  </tr>
  </thead>

  <form method="POST" action="">
  <pre>
        Filter By Username: 
        <input type="text" name="user_to_search">
    </pre>
  
    <input type="submit" value="See Reviews">
    </form>

    <?php 
    

    

    //default=not filtering by user
    $list_of_reviews = getReviewsbyMID($MID,$limit,$start_from); 
    $num_reviews = getCountReviews($MID); 

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $user = trim($_POST['user_to_search']);
        // filter by user if there was an input
        if (!empty($user)) {
           
            $list_of_reviews = getReviewsbyMID_username(1, $user,$limit, $start_from);
          
            $num_reviews = getCountReviews($MID,$user);
           

        }
    }

  

    $total_pages=ceil($num_reviews[0]['review_count']/$limit);
    $page_link="";
    //from geeks for geeks pagination article:https://www.geeksforgeeks.org/php/php-pagination-set-2/

 
    ?>

    


  <!-- iterate through review results -->
  <?php foreach ($list_of_reviews as $row): ?>
  <tr>
     <td><?php echo $row['username']; ?> </td>
     <td><?php echo $row['rating']; ?></td>
     <td><?php echo $row['review_text']; ?></td>

     
  </tr>
  <?php endforeach; 

  ?>


</table>
</div>

<?php


$total_pages=ceil($num_reviews[0]['review_count']/$limit);
$page_link="";
//from geeks for geeks pagination article:https://www.geeksforgeeks.org/php/php-pagination-set-2/

for ($i=1; $i<=$total_pages; $i++) {
    if($i==$pn) 
      $pagLink .= "<li class='active'><a href='reviewsByMovie.php?page=
                                      ".$i."'>".$i."</a></li>";
    else
      $pagLink .= "<li><a href='reviewsByMovie.php?page=".$i."'>
                                          ".$i."</a></li>";  
  };  
  echo $pagLink;

?>


</body>
</html>

