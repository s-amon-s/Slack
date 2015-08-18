<?php 
require('blockspring.php');
$res = Blockspring::runParsed("interactive-google-map", array(
  "locations" => array(
    array("Latitude", "Longitude", "Tooltip"),
    array(37.4217542234, -122.100920271, "Somewhere"),
    array(41.895964876906, -87.632716978217, "Out"),
    array(28.58230778, 77.09399505, "There")
  )
));
print($res->params["map"]);?>