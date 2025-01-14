<?php

include "config/settings.php";

function convertCarsToCsv($cars, $batchSize = 100): string
{
    global $endpointId;

    $filename = 'cars_data.csv';
    $file = fopen($filename, 'w');

    $header = [
        'ExternalProductId',
        'Name',
        'Price',
        'VendorCode',
        'IsAvailable',
        'CustomFieldAttributeCarDetailBody',
        'CustomFieldAttributeCarDetailBrand',
        'CustomFieldAttributeCarDetailColor',
        'CustomFieldAttributeCarDetailEquipment',
        'CustomFieldAttributeCarDetailFuel',
        'CustomFieldAttributeCarDetailMilleage',
        'CustomFieldAttributeCarDetailModel',
        'CustomFieldAttributeCarDetailOwnersCount',
        'CustomFieldAttributeCarDetailTransmission',
        'CustomFieldAttributeCarDetailUID',
        'CustomFieldAttributeCarDetailVariety',
        'CustomFieldAttributeCarDetailVIN',
        'CustomFieldAttributeCarDetailWheelDrive',
        'CustomFieldAttributeCarDetailWhereIs',
        'CustomFieldAttributeCarDetailYearRelease',
    ];
    fputcsv($file, $header, ';');

    $totalCars = count($cars);

    foreach (array_chunk($cars, $batchSize) as $batchIndex => $batch) {
        echo "Обработка партии " . ($batchIndex + 1) . " из " . ceil($totalCars / $batchSize) . "\n";

        foreach ($batch as $carIndex => $car) {
            echo "Обработка автомобиля " . ($batchIndex * $batchSize + $carIndex + 1) . " из $totalCars\n";

            $row = [
                $car['VIN'],                               // ExternalProductId
                $car['Автомобиль'],                       // Name
                $car['Цена'],                             // Price
                $car['VIN'],                              // VendorCode
                'false',                                  // IsAvailable
                $car['ТипКузова'],                        // CustomFieldAttributeCarDetailBody
                $car['Марка'],                            // CustomFieldAttributeCarDetailBrand
                $car['Цвет'],                             // CustomFieldAttributeCarDetailColor
                $car['Комплектация'],                     // CustomFieldAttributeCarDetailEquipment
                $car['ТипТоплива'],                       // CustomFieldAttributeCarDetailFuel
                $car['Пробег'],                           // CustomFieldAttributeCarDetailMilleage
                $car['Модель'],                           // CustomFieldAttributeCarDetailModel
                $car['КоличествоВладельцевПоПТС'],        // CustomFieldAttributeCarDetailOwnersCount
                $car['ВидКПП'],                           // CustomFieldAttributeCarDetailTransmission
                $car['УИД'],                              // CustomFieldAttributeCarDetailUID
                $car['Вид'],                              // CustomFieldAttributeCarDetailVariety
                $car['VIN'],                              // CustomFieldAttributeCarDetailVIN
                $car['ТипПривода'],                       // CustomFieldAttributeCarDetailWheelDrive
                $car['Местоположение'],                   // CustomFieldAttributeCarDetailWhereIs
                $car['ГодВыпуска'],                       // CustomFieldAttributeCarDetailYearRelease
            ];
            fputcsv($file, $row, ';');
        }
    }

    fclose($file);

    return "Файл сохранен: $filename";
}
