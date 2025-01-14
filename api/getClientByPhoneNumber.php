<?php

// Получение пользователя по номеру телефона

include "config/settings.php";
include "config/headersJSON.php";

function getClientByPhoneNumber($mobilePhone){
    global $headers;
    global $endpointId;

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=GetUserByPhoneNumber";

    $data = [
        "customer" => [
            "mobilePhone" => $mobilePhone
        ],
    ];

    $ch = initCurl($headers, $url, $data);
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);
    
    if(isset($result['customer'])){
        switch ($result['customer']['processingStatus']) {
            case 'Found':
                return true;
                break;
            case 'NotFound':
                return false;
                break;
            case 'Ambiguous':
                echo "Найдено более одного клиента по переданным идентификаторам.\n";
                return false;
                break;
            default:
                echo "Неизвестный статус обработки клиента: $result\n";
                return false;
                break;
        }
    }

    return false;
}