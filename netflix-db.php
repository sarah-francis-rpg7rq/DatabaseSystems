<?php


function getReviewsbyMID($MID)
{
    global $db;

    //from milestone code
    $query = "SELECT r.RID, u.username, m.title, r.rating, r.review_text 
    FROM review r
    JOIN writesReview wr ON r.RID = wr.RID
    JOIN users u ON wr.UID = u.UID
    JOIN movieHasReview mhr ON r.RID = mhr.RID
    JOIN movie m ON mhr.MID = m.MID
    WHERE m.MID=:MID"; //added this to search by a specific movie 


    
    $statement = $db->prepare($query);    
    $statement->bindValue(':MID', $MID);
    $statement->execute();              
    $result = $statement->fetchAll();     
    $statement->closeCursor();
    return $result;

}


function getReviewsbyMID_username($MID,$user)
{
    global $db;

    //from milestone code
    $query = "SELECT r.RID, u.username, m.title, r.rating, r.review_text 
    FROM review r
    JOIN writesReview wr ON r.RID = wr.RID
    JOIN users u ON wr.UID = u.UID
    JOIN movieHasReview mhr ON r.RID = mhr.RID
    JOIN movie m ON mhr.MID = m.MID
    WHERE m.MID=:MID
    AND u.username=:username";//and filter by user


    
    $statement = $db->prepare($query);    
    $statement->bindValue(':MID', $MID);
    $statement->bindValue(':username', $user);
    $statement->execute();              
    $result = $statement->fetchAll();     
    $statement->closeCursor();
    return $result;

}

?>