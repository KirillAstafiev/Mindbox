<?php

require "utils/formatDateTimeCSV.php";

function convertOrdersToCsv(array $clientsData, int $batchSize = 100): string
{
    global $orderLineStatuses;

    $filename = 'orders_data.csv';
    $file = fopen($filename, 'w');

    $header = [
        'OrderIdsExternalOrderId', 'OrderLastUpdateDateTimeUtc', 'OrderCustomFieldOrderDetailOrderEventType',
        'OrderCustomFieldOrderDetailOrderOrganization', 'OrderCustomFieldOrderDetailOrderStatus',
        'CustomerMobilePhone', 'OrderLineProductIdsExternalProductId', 'OrderLineCostPricePerItem',
        'OrderLineQuantity', 'OrderLineStatus', 'OrderLineId',
        'OrderLineCustomFieldPurchaseDetailEventDateTime', 'OrderLineCustomFieldPurchaseDetailEventManager',
        'OrderLineCustomFieldPurchaseDetailEventUID', 'OrderLineBasePricePerItem'
    ];
    fputcsv($file, $header, ';');

    $totalClients = count($clientsData);

    foreach (array_chunk($clientsData, $batchSize) as $batchIndex => $batch) {
        echo "Обработка партии " . ($batchIndex + 1) . " из " . ceil($totalClients / $batchSize) . "\n";

        foreach ($batch as $clientIndex => $client) {
            echo "Обработка клиента " . ($batchIndex * $batchSize + $clientIndex + 1) . " из $totalClients\n";

            $datetime = formatDateTimeCSV($client['ДатаСобытия'], $client['ВремяСобытия']);
            $lines = getActionsByUid($client['РабочийЛист']);

            foreach ($lines as $lineCounter => $line) {
                fputcsv($file, [
                    $client['РабочийЛист'],
                    $datetime,
                    $client['ВидСобытия'],
                    $client['Организация'],
                    $client['РабочийЛистСтатус'],
                    $client['Телефон'],
                    $client['АвтомобильVIN'] ?? 0,
                    0,
                    1,
                    $orderLineStatuses[$line['ВидСобытия']] ?? '',
                    $lineCounter + 1,
                    formatDateTimeCSV($line['ДатаНачала'], $line['ВремяНачала']),
                    $line['Менеджер'],
                    $line['EventUID'],
                    0
                ], ';');
            }
        }
    }

    fclose($file);

    return "Файл сохранен: $filename";
}
