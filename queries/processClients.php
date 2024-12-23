<?php

require "api/processOrder.php";
require "api/getClientOrders.php";
require "queries/getAllClientWorksheets.php";
require "utils/getLongestFullname.php";
require "utils/checkOrderAlreadyExists.php";
require "api/registration.php";

function processClients($data) {
    $dataSize = count($data);
    $i = 1;

    foreach ($data as $row) {
        echo "\n[$i/$dataSize] " . $row['РабочийЛист'] . "\n";

        $allClientOrders = getClientOrders($row['Телефон']);
        $allClientWorksheets = getAllClientWorksheets(substr($row['Телефон'], 1));

        if (!empty($allClientWorksheets)) {
            $row['Клиент'] = getLongestFullname($allClientWorksheets);
        }

        registration($row['Телефон'], $row['ЭлПочты'], $row['Клиент']) . "\n";
        $carModelId = $row['ModelId'];
        $mindboxId = checkOrderAlreadyExists($row['РабочийЛист'], $allClientOrders);

        echo processOrder($row, $carModelId, $mindboxId);
    
        $i++;
    }
}