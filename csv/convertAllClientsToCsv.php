<?php

include "config/settings.php";

function convertAllClientsToCsv(array $clients): string
{
    $filename = 'unique_clients_data.csv';
    $file = fopen($filename, 'w');

    fputcsv($file, ['SourcePointOfContact', 'FullName', 'MobilePhone', 'CustomFieldNameForAds'], ';');

    foreach ($clients as $index => $client) {
        echo "Обработка клиента " . ($index + 1) . " из " . count($clients) . "\n";
        fputcsv($file, [
            $GLOBALS['endpointId'],
            $client['ФИО'] ?? '',
            $client['Телефон'] ?? '',
            checkFullname($client['ФИО'] ?? ''),
        ], ';');
    }

    fclose($file);

    return "Файл сохранен: $filename";
}
