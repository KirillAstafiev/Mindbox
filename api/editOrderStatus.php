<?php

require 'vendor/autoload.php';

use Ramsey\Uuid\Uuid;

include "config/settings.php";
include "config/headersJSON.php";
include "config/dictionaries.php";

function editOrderStatus($orderId, $statusId){
    global $headers;
    global $endpointId;
    global $orderStatuses;

    try {
        $transactionId = Uuid::uuid4()->toString();
    } catch (Exception $e) {
        die("Ошибка создания UUID: ". $e->getMessage());
    }

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=editOrder&transactionId=$transactionId";

    $data = [
        "order" => [
            "ids" => [
                "mindboxId" => $orderId,
            ],
            "customFields" => [
                "orderStatus" => $orderStatuses[$statusId]
            ]
        ]
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_CAINFO, 'C:/Certificates/cacert.pem');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Ошибка запроса: " . curl_error($ch));
    }

    curl_close($ch);

    return $response;
}