<?php

$val = $_SERVER['argv'];

$sportId = $val[1];
$providerId = $val[2];
$leagueCount = $val[3];
$randomFormatching = !empty($val[4]) ? $val[4] : 0;

$db = pg_connect("host=172.20.0.2 port=5432 dbname=multiline_coro user=postgres password=password");
//$result = pg_query($db, "SELECT * FROM book where book_id = '$_POST[bookid]'");
//$row = pg_fetch_assoc($result);
$totalRecordsToInsert = $leagueCount - $randomFormatching;
for ($i=0; $i<$totalRecordsToInsert; $i++)
{
    $leagueName = getName(random_int(16, 155));
    
    $result = pg_query($db, "insert into leagues (name, provider_id, sport_id) values ('$leagueName', $providerId, $sportId)");
    if ($result) {
        echo "League Name: $leagueName has been added into leagues table.\n";
    }
}

if (!empty($randomFormatching)) {
    $result = pg_query($db, "SELECT name FROM leagues order by random() limit $randomFormatching");
    while($row = pg_fetch_assoc($result))
    {
        $result = pg_query($db, "insert into leagues (name, provider_id, sport_id) values ('{$row['name']}', $providerId, $sportId)");
        if ($result) {
            echo "League Name: $leagueName has been added into leagues table.\n";
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