<?php
include "queries/getCarModels.php";
include "config/settings.php";
include "config/headersJSON.php";

function addProduct($productName, $productId){
    global $headers;
    global $endpointId;

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=AddProduct";

    $data = [
        "product" => [
            "name" => $productName,
            "isAvailable" => true,
            "ids" => [
                "externalProductId" => $productId
            ]
        ]
    ];

    $ch = initCurl($headers, $url, $data);
    $response = json_decode(curl_exec($ch), true);

    if (curl_errno($ch)) {
        throw new Exception("Ошибка запроса: " . curl_error($ch));
    }

    curl_close($ch);

    if ($response['status'] != 'Success') {
        print_r($response);
        $errorMessage = isset($response['errorMessage']) ? $response['errorMessage'] : 'Неизвестная ошибка';
        return "Ошибка добавления продукта: $errorMessage";
    } else {
        return "Продукт создан\n";
    }
}