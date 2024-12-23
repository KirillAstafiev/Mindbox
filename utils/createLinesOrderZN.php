<?php

include "config/dictionaries.php";

function createLinesOrderZN($queryData, $carVIN) {
    global $orderLineStatuses;

    $lines = [];
    $lineNumber = 1;

    foreach ($queryData as $row) {
        $lines[] = [
            "basePricePerItem" => "0",
            "quantity" => "1",
            "lineNumber" => "$lineNumber",
            "status" => "statusCarwork",
            "product" => [
                "ids" => [
                    "externalProductId" => $carVIN ? $carVIN : 0
                ]
            ],
            "customFields" => [
                "eventUID" => $row['АвтоработаУИД'],
                "serviceWorkshop" => $row['Цех'],
                "serviceNomenclature" => $row['Номенклатура'],
                "serviceCount" => $row['Количество']
            ]
        ];

        $lineNumber++;
    }

    return $lines;
}
