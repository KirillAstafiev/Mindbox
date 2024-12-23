<?php
include "queries/getCarModels.php";
include "config/settings.php";
include "config/headersJSON.php";

function addCar($carData){
    global $headers;
    global $endpointId;

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=AddProduct";

    $data = [
        "product" => [
            "name" => $carData['Автомобиль'],
            "isAvailable" => false,
            "price" => $carData['Цена'],
            "vendorCode" => $carData['VIN'],
            "ids" => [
                "externalProductId" => $carData['VIN']
            ],
            "customFields" => [
                'carDetailUID' => $carData['УИД'],
                'carDetailVIN' => $carData['VIN'],
                'carDetailBrand' => $carData['Марка'],
                'carDetailModel' => $carData['Модель'],
                'carDetailBody' => $carData['ТипКузова'],
                'carDetailFuel' => $carData['ТипТоплива'],
                'carDetailWheelDrive' => $carData['ТипПривода'],
                'carDetailTransmission' => $carData['ВидКПП'],
                'carDetailEquipment' => $carData['Комплектация'],
                'carDetailColor' => $carData['Цвет'],
                'carDetailYearRelease' => $carData['ГодВыпуска'],
                'carDetailMilleage' => $carData['Пробег'],
                'carDetailVariety' => $carData['Вид'],
                'carDetailWhereIs' => $carData['Местоположение'],
                'carDetailOwnersCount' => $carData['КоличествоВладельцевПоПТС'],
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
        $errorMessage = isset($response['errorMessage']) ? $response['errorMessage'] : 'Неизвестная ошибка';
        return "Ошибка добавления машины: $errorMessage";
    } else {
        return "Автомобиль (" .$carData['Автомобиль']. ") создан\n";
    }
}