<?php

include "config/settings.php";
include "config/headersJSON.php";
require "utils/createLinesOrder.php";
require "queries/getActionsByUid.php";

function processOrder($clientData, $carVIN, $orderMindboxId = null) {
    global $headers;
    global $endpointId;

    $operation = $orderMindboxId ? 'EditOrder' : 'CreateOrder';

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=$operation";

    $date = $clientData['ДатаСобытия'];
    $time = $clientData['ВремяСобытия'];
    $completeDateTimeUtc = formatDateTime($date, $time);

    $data = [
        "customer" => [
            "mobilePhone" => $clientData['Телефон'],
        ],
        "executionDateTimeUtc" => "$completeDateTimeUtc",
        "order" => [
            "ids" => [
                "externalOrderId" => $clientData['РабочийЛист'],
                "mindboxId" => $orderMindboxId
            ],
            "lines" => createLinesOrder(getActionsByUid($clientData['РабочийЛист']), $carVIN),
            "customFields" => [
                "orderStatus" => $clientData['РабочийЛистСтатус'],
                "workSheetUID" => $clientData['РабочийЛист'],
                "orderEventType" => $clientData['ВидСобытия'],
                "orderOrganization" => $clientData['Организация']
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
        return "Ошибка создания/обновления заказа\n";
    } else {
        return (isset($orderMindboxId) ? "Заказ обновлен" : "Заказ создан") . "\n";
    }
}