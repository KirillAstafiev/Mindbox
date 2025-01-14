<?php

include "config/dictionaries.php";

function createLinesOrder($queryData, $carVin = null) {
    global $orderLineStatuses;

    $lines = [];
    $lineNumber = 1;

    foreach ($queryData as $row) {
        $lines[] = [
            "basePricePerItem" => "0",
            "quantity" => "1",
            "lineNumber" => "$lineNumber",
            "status" => $orderLineStatuses[$row['ВидСобытия']],
            "product" => [
                "ids" => [
                    "externalProductId" => isset($carVin) ? $carVin : 0
                ]
            ],
            "customFields" => [
                "eventUID" => $row['EventUID'],
                "eventManager" => $row['Менеджер'],
                "eventDateTime" => formatDateTime($row['ДатаНачала'], $row['ВремяНачала'])
            ]
        ];

        $lineNumber++;
    }

    return $lines;
}
