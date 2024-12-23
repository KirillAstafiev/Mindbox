<?php

require "config/settings.php";
require "config/headersJSON.php";
require "utils/formatDateTime.php";
require "utils/createLinesOrderZN.php";
require "queries/getActionsByUidZN.php";

function processOrderZN($clientData, $carData, $orderMindboxId = null){
    global $headers;
    global $endpointId;

    $operation = $orderMindboxId ? 'EditOrderZN' : 'CreateOrderZN';

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=$operation";

    $date = $clientData['ЗаказНарядДатаСоздания'];
    $time = $clientData['ЗаказНарядВремяСоздания'];
    $completeDateTimeUtc = formatDateTime($date, $time);

    $data = [
        "customer" => [
            "mobilePhone" => $clientData['Телефон'],
        ],
        "executionDateTimeUtc" => "$completeDateTimeUtc",
        "order" => [
            "ids" => [
                "externalOrderId" => $clientData['ЗаказНарядУИД'],
                "mindboxId" => $orderMindboxId
            ],
            "lines" => createLinesOrderZN(getActionsByUidZN($clientData['ЗаказНарядУИД']), $carData['VIN']),
            "customFields" => [
                "orderStatus" => $clientData['ЗаказНарядСтатус'],
                "workSheetUID" => $clientData['ЗаказНарядУИД'],
                "orderManager" => $clientData['ЗаказНарядМенеджер'],
                "orderDatetimeCreated" => formatDateTime($clientData['ЗаказНарядДатаСоздания'], $clientData['ЗаказНарядВремяСоздания']),
                "orderDatetimeClosed" => formatDateTime($clientData['ЗаказНарядФактическаяДатаВыдачи'], $clientData['ЗаказНарядФактическоеВремяВыдачи']),
                "orderRepairType" => $clientData['ЗаказНарядТипРемонта'],
                "orderRepairVariety" => $clientData['ЗаказНарядВидРемонта'],
                "orderOrganization" => $clientData['Организация'],
            ]
        ],
    ];

    $ch = initCurl($headers, $url, $data);
    $response = json_decode(curl_exec($ch), true);

    if (curl_errno($ch)) {
        throw new Exception("Ошибка запроса: " . curl_error($ch));
    }

    curl_close($ch);

    if ($response['status'] != 'Success') {
        print_r($response);
        return "Ошибка создания/обновления ЗаказНаряд\n";
    } else {
        return isset($orderMindboxId) ? "ЗаказНаряд обновлен" : "ЗаказНаряд создан";
    }
}