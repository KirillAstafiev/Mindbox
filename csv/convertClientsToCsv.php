<?php

include "config/settings.php";
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

function convertClientsToCsv($clients) {
    global $endpointId;

    $spreadsheet = new Spreadsheet();
    
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'SourcePointOfContact');
    $sheet->setCellValue('B1', 'FullName');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'MobilePhone');
    $sheet->setCellValue('E1', 'CustomFieldNameForAds');

    $dataSize = count($clients);
    $row = 2;

    foreach ($clients as $client) {
        $sheet->setCellValue('A' . $row, $endpointId);
        $sheet->setCellValue('B' . $row, $client['ФИО']);
        $sheet->setCellValue('C' . $row, $client['ЭлПочта']);
        $sheet->setCellValue('D' . $row, $client['Телефон']);
        $sheet->setCellValue('E' . $row, checkFullname($client['ФИО']));
        echo "$row / $dataSize Добавлено\n";
        $row++;
    }

    $writer = new Csv($spreadsheet);
    $writer->setDelimiter(';');
    $writer->setEnclosure('"');
    $writer->setLineEnding("\r\n");
    $writer->setSheetIndex(0);

    $filename = 'clients_data.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    $writer->save($filename);

    return "Файл сохранен: " . $filename;
}
