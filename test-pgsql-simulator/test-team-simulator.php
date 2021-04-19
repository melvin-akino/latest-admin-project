<?php

$val = $_SERVER['argv'];

$sportId = $val[1];
$providerId = $val[2];
$teamCount = $val[3];
$randomFormatching = !empty($val[4]) ? $val[4] : 0;

$db = pg_connect("host=172.20.0.2 port=5432 dbname=multiline_coro user=postgres password=password");
//$result = pg_query($db, "SELECT * FROM book where book_id = '$_POST[bookid]'");
//$row = pg_fetch_assoc($result);
$totalRecordsToInsert = $teamCount - $randomFormatching;
for ($i=0; $i<$totalRecordsToInsert; $i++)
{
    $teamName = getName(random_int(16, 32));
    
    $result = pg_query($db, "insert into teams (name, provider_id, sport_id) values ('$teamName', $providerId, $sportId)");
    if ($result) {
        echo "Team Name: $teamName has been added into teams table.\n";
    }
}

if (!empty($randomFormatching)) {
    $result = pg_query($db, "SELECT name FROM teams order by random() limit $randomFormatching");
    while($row = pg_fetch_assoc($result))
    {
        $result = pg_query($db, "insert into teams (name, provider_id, sport_id) values ('{$row['name']}', $providerId, $sportId)");
        if ($result) {
            echo "Team Name: $teamName has been added into teams table.\n";
        }    
    }
}

function getName($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
  
    return $randomString;
}

?>