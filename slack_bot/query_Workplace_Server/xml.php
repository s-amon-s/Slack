<?php

// Send the headers
#header("Content-type: application/json; charset=utf-8");

$servername = "localhost";
$username = "yfaahoio_slack";
$password = "slack";
$dbname = "yfaahoio_slack";
// Create connection
try {
    mysql_connect($servername, $username, $password);
    mysql_select_db($dbname);
} catch (Exception $e) {
    die($e->__toString());
}


$result = mysql_query("SELECT * FROM kms");
$data = array();
while($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $data []= array(
        'id'=> htmlentities($row['id']),
        'keyword'=> htmlentities($row['keywords']),
        'code'=> htmlentities($row['code']),
        'ref'=> htmlentities($row['ref']),
    );
}

echo json_encode($data);
