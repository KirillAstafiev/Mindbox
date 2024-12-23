<?php

include "config/settings.php";
include "config/headersJSON.php";

// Клиент пришел на встречу
function clientMeeting($userId, $type = 'default')
{
    global $headers;
    global $endpointId;

    $url = match ($type) {
        'default' => "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=SetClientActionMeeting",
        '1c' => "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=SetClientActionMeeting1C",
        'trade-in' => "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=SetClientActionMeetingTradeIn",
        default => throw new Exception("Некорректный тип встречи"),
    };

    $data = [
        "customer" => [
            "ids" => [
                "mindboxId" => $userId
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

    return json_decode($response, true);
}