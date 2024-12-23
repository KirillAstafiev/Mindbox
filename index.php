<?php

require "queries/getClients.php";
require "queries/getClientsZN.php";
require "queries/processClients.php";
require "queries/processClientsZN.php";

//$clients = getClients();
//processClients($clients);

$clients = getClientsZN();
processClientsZN($clients);