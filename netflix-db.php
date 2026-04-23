<?php

/** JOIN to compute AVG(rating) from linked reviews when movieRating row is missing. */
function movieReviewAvgJoinSql()
{
    return 'LEFT JOIN (
        SELECT mhr.MID, AVG(r.rating) AS avg_from_reviews
        FROM movieHasReview mhr
        INNER JOIN review r ON r.RID = mhr.RID
        GROUP BY mhr.MID
    ) agg ON agg.MID = m.MID';
}

/** Effective average on 1–5 scale: movieRating table or live average from reviews. */
function movieEffectiveAvgSql()
{
    return 'COALESCE(mr.avg_rating, agg.avg_from_reviews)';
}

/** Whitelist sort keys for search page (prevents SQL injection in ORDER BY). */
function movieSearchOrderSql($sortKey)
{
    $avg = movieEffectiveAvgSql();
    $map = [
        'title_asc' => 'm.title ASC',
        'title_desc' => 'm.title DESC',
        'year_asc' => 'm.year ASC, m.title ASC',
        'year_desc' => 'm.year DESC, m.title ASC',
        'rating_desc' => 'CASE WHEN ' . $avg . ' IS NULL THEN 1 ELSE 0 END, ' . $avg . ' DESC, m.title ASC',
        'rating_asc' => 'CASE WHEN ' . $avg . ' IS NULL THEN 1 ELSE 0 END, ' . $avg . ' ASC, m.title ASC',
    ];
    return $map[$sortKey] ?? 'm.title ASC';
}

/**
 * Format average user rating (already on 1–5 scale). Whole numbers omit decimals.
 *
 * @return string|null null when no rating
 */
function format_movie_avg_rating_display($avg)
{
    if ($avg === null || $avg === '') {
        return null;
    }
    $v = round((float) $avg, 1);
    if (abs($v - (int) $v) < 0.001) {
        return (string) (int) $v;
    }
    return number_format($v, 1);
}

/**
 * @return array{sql:string,binds:array}
 */
function movieSearchFiltersSql(array $in)
{
    $sql = '';
    $binds = [];
    if (!empty($in['q'])) {
        $sql .= ' AND m.title LIKE :q ';
        $binds[':q'] = '%' . $in['q'] . '%';
    }
    if (isset($in['year_min']) && $in['year_min'] !== '' && $in['year_min'] !== null) {
        $sql .= ' AND m.year >= :year_min ';
        $binds[':year_min'] = (int) $in['year_min'];
    }
    if (isset($in['year_max']) && $in['year_max'] !== '' && $in['year_max'] !== null) {
        $sql .= ' AND m.year <= :year_max ';
        $binds[':year_max'] = (int) $in['year_max'];
    }
    if (!empty($in['content_rating'])) {
        $sql .= ' AND m.content_rating = :content_rating ';
        $binds[':content_rating'] = $in['content_rating'];
    }
    if (!empty($in['cid'])) {
        $sql .= ' AND m.CID = :cid ';
        $binds[':cid'] = (int) $in['cid'];
    }
    return ['sql' => $sql, 'binds' => $binds];
}

function searchMoviesCount(PDO $db, array $filters)
{
    $f = movieSearchFiltersSql($filters);
    $sql = 'SELECT COUNT(*) AS c FROM movie m
            JOIN director d ON m.DirID = d.DirID
            LEFT JOIN movieRating mr ON m.MID = mr.MID
            WHERE 1=1 ' . $f['sql'];
    $st = $db->prepare($sql);
    foreach ($f['binds'] as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    $st->closeCursor();
    return (int) ($row['c'] ?? 0);
}

function searchMoviesPage(PDO $db, array $filters, $sortKey, $limit, $offset)
{
    $f = movieSearchFiltersSql($filters);
    $order = movieSearchOrderSql($sortKey);
    $avgSql = movieEffectiveAvgSql();
    $sql = 'SELECT m.MID, m.title, m.year, m.type, m.content_rating, d.name AS director_name,
                    ' . $avgSql . ' AS avg_rating
            FROM movie m
            JOIN director d ON m.DirID = d.DirID
            LEFT JOIN movieRating mr ON m.MID = mr.MID
            ' . movieReviewAvgJoinSql() . '
            WHERE 1=1 ' . $f['sql'] . '
            ORDER BY ' . $order . '
            LIMIT :limit_num OFFSET :offset_num';
    $st = $db->prepare($sql);
    foreach ($f['binds'] as $k => $v) {
        $st->bindValue($k, $v);
    }
    $st->bindValue(':limit_num', (int) $limit, PDO::PARAM_INT);
    $st->bindValue(':offset_num', (int) $offset, PDO::PARAM_INT);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $st->closeCursor();
    return $rows;
}

function getMovieByMid(PDO $db, $mid)
{
    $avgSql = movieEffectiveAvgSql();
    $sql = 'SELECT m.MID, m.title, m.year, m.type, m.content_rating, d.name AS director_name,
                    ' . $avgSql . ' AS avg_rating
            FROM movie m
            JOIN director d ON m.DirID = d.DirID
            LEFT JOIN movieRating mr ON m.MID = mr.MID
            ' . movieReviewAvgJoinSql() . '
            WHERE m.MID = :mid LIMIT 1';
    $st = $db->prepare($sql);
    $st->bindValue(':mid', (int) $mid, PDO::PARAM_INT);
    $st->execute();
    $row = $st->fetch(PDO::FETCH_ASSOC);
    $st->closeCursor();
    return $row ?: null;
}

function getFilterCountries(PDO $db)
{
    $sql = 'SELECT DISTINCT c.CID, c.country_name
            FROM country c
            INNER JOIN movie m ON m.CID = c.CID
            ORDER BY c.country_name ASC';
    $st = $db->query($sql);
    return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
}

function getFilterContentRatings(PDO $db)
{
    $sql = 'SELECT DISTINCT m.content_rating FROM movie m ORDER BY m.content_rating ASC';
    $st = $db->query($sql);
    return $st ? $st->fetchAll(PDO::FETCH_COLUMN, 0) : [];
}

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

/**
 * Compact pagination: page numbers plus null for ellipsis gaps.
 *
 * @return list<int|null>
 */
function pagination_page_sequence($current, $last, $delta = 2)
{
    if ($last <= 1) {
        return [];
    }
    $current = max(1, min((int) $current, $last));
    if ($last <= 9) {
        return range(1, $last);
    }
    $left = $current - $delta;
    $right = $current + $delta;
    $pages = [];
    for ($i = 1; $i <= $last; $i++) {
        if ($i === 1 || $i === $last || ($i >= $left && $i <= $right)) {
            $pages[] = $i;
        }
    }
    $out = [];
    $prev = null;
    foreach ($pages as $i) {
        if ($prev !== null) {
            if ($i - $prev === 2) {
                $out[] = $prev + 1;
            } elseif ($i - $prev > 2) {
                $out[] = null;
            }
        }
        $out[] = $i;
        $prev = $i;
    }
    return $out;
}

/** Build query string for reviewsByMovie.php pagination links (GET). */
function reviews_movie_query_string($mid, $page, $user = '')
{
    $q = ['mid' => (int) $mid, 'page' => (int) $page];
    if ($user !== '') {
        $q['user'] = $user;
    }
    return http_build_query($q);
}

?>