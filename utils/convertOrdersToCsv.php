<?php

require 'vendor/autoload.php';

include "config/settings.php";
include "queries/checkFullname.php";
include "config/dictionaries.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

function convertOrdersToCsv($data, $phoneNumber) {
    global $endpointId;
    global $orderLineStatuses;

    $spreadsheet = new Spreadsheet();
    
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'OrderIdsExternalOrderId');
    $sheet->setCellValue('B1', 'OrderCreationDateTimeUtc');
    $sheet->setCellValue('C1', 'OrderTotalPrice');
    $sheet->setCellValue('D1', 'OrderCustomFieldOrderDetailDialogComment');
    $sheet->setCellValue('E1', 'OrderCustomFieldOrderDetailOrderEventType');
    $sheet->setCellValue('F1', 'OrderCustomFieldOrderDetailOrderOrganization');
    $sheet->setCellValue('G1', 'OrderCustomFieldOrderDetailOrderStatus');
    $sheet->setCellValue('H1', 'OrderCustomFieldOrderDetailserviceExecutor');
    $sheet->setCellValue('I1', 'OrderCustomFieldOrderDetailWorkSheetUID');
    $sheet->setCellValue('J1', 'PointOfContact');
    $sheet->setCellValue('K1', 'CustomerMobilePhone');
    $sheet->setCellValue('L1', 'OrderLineProductIdsExternalProductId');//
    $sheet->setCellValue('M1', 'OrderLineCostPricePerItem'); //
    $sheet->setCellValue('N1', 'OrderLineQuantity'); //
    $sheet->setCellValue('O1', 'OrderLinePriceOfLine');//
    $sheet->setCellValue('P1', 'OrderLineStatus'); //
    $sheet->setCellValue('Q1', 'OrderLineId'); //
    $sheet->setCellValue('R1', 'OrderLineCustomFieldPurchaseDetailEventManager'); //
    $sheet->setCellValue('S1', 'OrderLineCustomFieldPurchaseDetailEventName'); //
    $sheet->setCellValue('T1', 'OrderLineCustomFieldPurchaseDetailEventUID'); //
    $sheet->setCellValue('U1', 'OrderLineCustomFieldPurchaseDetailserviceMaster');

    $result = [];

    foreach ($data as $row) {
        $allClientWorksheets = getAllClientWorksheets(substr($row['Телефон'], 1));
        $carModelId = 0;

        if (!empty($allClientWorksheets)) {
            $row['Клиент'] = getLongestFullname($allClientWorksheets);
            $lines = [];

            foreach ($allClientWorksheets as $worksheet => $i) {
                $datetime = $worksheet['ДатаНачала']."T".$worksheet['ВремяНачала']."Z";

                $lines[] = [
                    'OrderLineProductIdsExternalProductId' => $carModelId ? $carModelId : 0,
                    'OrderLineCostPricePerItem' => 0,
                    'OrderLineQuantity' => 0,
                    'OrderLinePriceOfLine' => 0,
                    'OrderLineId' => $i,
                    'OrderLineStatus' => $worksheet['Состояние'],
                    'OrderLineCustomFieldPurchaseDetailEventManager' => $worksheet['Менеджер'],
                    'OrderLineCustomFieldPurchaseDetailEventName' => $orderLineStatuses[$worksheet['ВидСобытия']],
                    'OrderLineCustomFieldPurchaseDetailEventUID' => $worksheet['EventUID'],
                ];
            }
        }
    }

    $row = 2;

    foreach ($data as $record) {
        $sheet->setCellValue('A' . $row, $endpointId);
        $sheet->setCellValue('B' . $row, $record[0]);
        $sheet->setCellValue('C' . $row, $record[1]);
        $sheet->setCellValue('D' . $row, $record[2]);
        $sheet->setCellValue('E' . $row, $record[3]);
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
