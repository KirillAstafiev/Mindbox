<?php

require "../queries/getClients.php";

function writeToCSV($data, $fileName = '../clients.csv') {
    $file = fopen($fileName, 'w');

    fputcsv($file, ['MobilePhone', 'FullName']);

    foreach ($data as $client) {
        $phoneNumber = '"' . $client['Телефон'] . '"';
        $fullName = $client['Клиент'];

        fputcsv($file, [$phoneNumber, $fullName]);
    }

    fclose($file);
}

writeToCSV(getClients());