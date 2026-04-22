<?php


function getReviewsbyMID($MID, $limit_num,$offset_num)
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
    LIMIT :limit_num OFFSET :offset_num "; //added this to search by a specific movie 


    $statement = $db->prepare($query);    
    $statement->bindValue(':MID', $MID);
    $statement->bindValue(':limit_num', $limit_num, PDO::PARAM_INT);
    $statement->bindValue(':offset_num', $offset_num,PDO::PARAM_INT);
    $statement->execute();             
    $result = $statement->fetchAll();     
    $statement->closeCursor();
    return $result;

}


function getReviewsbyMID_username($MID,$user, $limit_num,$offset_num)
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
    AND u.username=:username
    LIMIT :limit_num OFFSET :offset_num";//and filter by user


    
    $statement = $db->prepare($query);    
    $statement->bindValue(':MID', $MID);
    $statement->bindValue(':username', $user);
    $statement->bindValue(':limit_num', $limit_num, PDO::PARAM_INT);
    $statement->bindValue(':offset_num', $offset_num,PDO::PARAM_INT);
    $statement->execute();              
    $result = $statement->fetchAll();     
    $statement->closeCursor();
    return $result;

}

function getCountReviews($MID,$user=-1)
{
    
    global $db;

    //from milestone code

    if ($user!=-1){
        $query = "SELECT COUNT(r.RID) AS review_count
        FROM review r
        JOIN writesReview wr ON r.RID = wr.RID
        JOIN users u ON wr.UID = u.UID
        JOIN movieHasReview mhr ON r.RID = mhr.RID
        JOIN movie m ON mhr.MID = m.MID
        WHERE m.MID=:MID
        AND u.username=:username ";// filter by user
    }
    else{
        $query = "SELECT COUNT(r.RID) AS review_count
        FROM review r
        JOIN movieHasReview mhr ON r.RID = mhr.RID
        JOIN movie m ON mhr.MID = m.MID
        WHERE m.MID=:MID ";//no filter by user
    }
   

    $statement = $db->prepare($query);    
    $statement->bindValue(':MID', $MID);

    if ($user != -1){
        $statement->bindValue(':username', $user);
    }
   
    $statement->execute();              
    $result = $statement->fetchAll();     
    $statement->closeCursor();
    return $result;

}




?>