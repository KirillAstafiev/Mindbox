<?php

ini_set('memory_limit', '3G');

require 'queries/processClients.php';
require 'queries/processClients.php';
require 'queries/getEventDataByUid.php';
require 'queries/getEventDataZNByUid.php';

$eventData = require 'updateEventData_regular.php';
$eventDataZN = require 'updateEventDataZN_regular.php';
require "updateAllEventData_regular.php";
require "updateAllEventDataZN_regular.php";

foreach($eventData as $worksheet) {
    $data = getEventDataByUid($worksheet);
    processClients($data);
}

foreach ($eventDataZN as $worksheet) {
    $data = getEventDataZNByUid($worksheet);
    processClientsZN($data);
}
