<?php
include "config/settings.php";
include "config/headersJSON.php";

function editClient($clientId){
    global $headers;
    global $endpointId;

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=EditClient";

    $data = [
        "customer" => [
            "ids" => [
                "mindboxId" => $clientId
            ],
            "customFields" => [
                "clientUUID" => "001dcac6-aa1f-11ee-80ea-101f742f15e2",
            ]
        ],
    ];

    $ch = initCurl($headers, $url, $data);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception("Ошибка запроса: " . curl_error($ch));
    }

    curl_close($ch);

    return json_decode($response, true);
}