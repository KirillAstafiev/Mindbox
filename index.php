<?php

require "queries/getClients.php";
require "queries/getUniqueClients.php";
require "queries/getClientsZN.php";
require "queries/processClients.php";
require "queries/processClientsZN.php";
require "queries/getUniqueVins.php";
require "csv/convertClientsToCsv.php";
require "csv/convertCarsToCsv.php";
require "csv/convertOrdersToCsv.php";
require "csv/convertOrdersZNToCsv.php";
require "csv/convertAllClientsToCsv.php";

ini_set('memory_limit', '3G');

//$clients = getClientsZN();
//echo convertOrdersZNToCsv($clients);
//processClients($clients);

//$uniqueClients = getUniqueClients();
//echo convertAllClientsToCsv($uniqueClients);

//$clients = getClientsZN();
//processClientsZN($clients);

//$clients = getUniqueClients();
//echo convertClientsToCsv($clients);

$uniqueVins = getUniqueVins();
echo convertCarsToCsv(getCarsByVIN($uniqueVins));