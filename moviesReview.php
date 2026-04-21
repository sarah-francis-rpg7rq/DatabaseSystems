<!DOCTYPE html>

<?php
require("connect-db.php");   
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




//testing 6 for now but it should get the MID from the previous page
$list_of_reviews = getReviewsbyMID(6);

//$list_of_reviews = getReviewsbyMID_username(6,'amy');
//this is just to see if the function by username worked I havent worked on the filter button yet
?>




<form method="POST" action="">
     
    <pre>
        User(optional): 
        <input type="text" name="user_to_search">
    </pre>
    

    <pre>
        See: 
        <input type="radio" name="byUser"
                value="Show Reviews by User">Show Reviews by User
        
        <input type="radio" name="allReviews"
                value="See All Reviews">See All Reviews
    </pre>
     

    <input type="submit" value="Show Reviews">
 
</form>


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

  <!-- iterate array of results, display the existing visitor information -->
  <?php foreach ($list_of_reviews as $row): ?>
  <tr>
     <td><?php echo $row['username']; ?> </td>
     <td><?php echo $row['rating']; ?></td>
     <td><?php echo $row['review_text']; ?></td>

     
  </tr>
  <?php endforeach; ?>
  <!-- end loop -->

</table>
</div>


</body>
</html>

