<?php
$sportId = $_REQUEST['sport'];
$providerId = $_REQUEST['provider'];
$leagueCount = $_REQUEST['league_count'];

$db = pg_connect("host=localhost port=5432 dbname=postgres user=postgres password=password");
//$result = pg_query($db, "SELECT * FROM book where book_id = '$_POST[bookid]'");
//$row = pg_fetch_assoc($result);

for ($i=0, $i<$leagueCount, $i++)
{
    $leagueName = getName(random_int(16, 155));

    $result = pg_query($db, "insert into leagues (name, provider_id, sport_id) values ('$leagueName', $providerId, $sportId)");
    if ($result) {
        echo "League Name: $leagueName has been added into leagues table.\n";
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
}
?>