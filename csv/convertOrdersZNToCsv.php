<?php

include "config/settings.php";

function convertOrdersZNToCsv(array $clientsZNData, int $batchSize = 100): string
{
    $filename = 'orders_zn_data.csv';
    $file = fopen($filename, 'w');

    // Запись заголовков CSV
    $header = [
        'OrderIdsExternalOrderId',
        'OrderLastUpdateDateTimeUtc', 
        'OrderCustomFieldOrderDetailOrderDatetimeCreated', 
        'OrderCustomFieldOrderDetailOrderDatetimeClosed', 
        'OrderCustomFieldOrderDetailOrderDatetimeIssuanceFact',
        'OrderCustomFieldOrderDatetimeIssuancePlan', 
        'OrderCustomFieldOrderDetailOrderOrganization', 
        'OrderCustomFieldOrderDetailOrderStatus',
        'OrderCustomFieldOrderDetailOrderManaget', 
        'CustomerMobilePhone', 
        'OrderLineProductIdsExternalProductId',
        'OrderLineCostPricePerItem', 
        'OrderLineQuantity', 
        'OrderLineId',
        'OrderLineCustomFieldOrderDetailOrderEventName',
        'OrderLineCustomFieldPurchaseDetailEventUID',
        'OrderLineCustomFieldPurchaseDetailServiceWorkshop'
    ];
    fputcsv($file, $header, ';');

    $totalClients = count($clientsZNData);
    $batchCount = ceil($totalClients / $batchSize);

    foreach (array_chunk($clientsZNData, $batchSize) as $batchIndex => $batch) {
        echo "Обработка партии " . ($batchIndex + 1) . " из " . $batchCount . "\n";

        foreach ($batch as $clientIndex => $client) {
            echo "Обработка клиента " . ($batchIndex * $batchSize + $clientIndex + 1) . " из $totalClients\n";

            $datetimeOpened = formatDateTimeCSV($client['ЗаказНарядДатаСоздания'], $client['ЗаказНарядВремяСоздания']);
            $datetimeClosed = formatDateTimeCSV($client['ЗаказНарядДатаЗакрытия'], $client['ЗаказНарядВремяЗакрытия']);
            $datetimePlan = formatDateTimeCSV($client['ЗаказНарядПлановаяДатаВыдачи'], $client['ЗаказНарядПлановоеВремяВыдачи']);
            $datetimeFact = formatDateTimeCSV($client['ЗаказНарядФактическаяДатаВыдачи'], $client['ЗаказНарядФактическоеВремяВыдачи']);

            $lines = getActionsByUidZN($client['ЗаказНарядУИД']);

            foreach ($lines as $lineCounter => $line) {
                fputcsv($file, [
                    $client['ЗаказНарядУИД'],
                    $datetimeOpened, // историческая дата заказа
                    $datetimeOpened, // дата самого открытия
                    $datetimeClosed, // дата закрытия заказа
                    $datetimeFact, // фактическая дата
                    $datetimePlan, // плановая дата
                    $client['Организация'],
                    trim($client['ЗаказНарядСтатус']),
                    trim($client['ЗаказНарядМенеджер']),
                    $client['Телефон'],
                    $client['АвтомобильVIN'] ?? 0,
                    0,
                    1,
                    $lineCounter + 1,
                    $line['Авторабота'],
                    $line['АвтоработаУИД'],
                    $line['Цех']
                ], ';');
            }
        }
    }

    fclose($file);

    return "Файл сохранен: $filename";
}

