<?php

require "api/processOrderZN.php";
require "queries/getAllClientWorksheetsZN.php";

require "api/addCar.php";
require "api/getCarsByVIN.php";
require "queries/getAllClientCarsZN.php";

function processClientsZN($data) {
    $dataSize = count($data);
    $i = 1;

    foreach ($data as $row) {
        try {
            echo "\n[$i/$dataSize] " . $row['ЗаказНарядУИД'] . "\n";

            $allClientOrders = getClientOrders($row['Телефон']);
            $allClientWorksheets = getAllClientWorksheetsZN(substr($row['Телефон'], 1));

            if (!empty($allClientWorksheets)) {
                $row['Клиент'] = getLongestFullname($allClientWorksheets);
            }

            $regResult = registration($row['Телефон'], $row['ЗаказНарядЗаказчикЭлПочта'], $row['ЗаказНарядЗаказчик']) . "\n";

            if($regResult == 1) {
                $clientCarsVINs = getAllClientCarsZN(substr($row['Телефон'], 1));

                $clientCarsData = getCarsByVin($clientCarsVINs);
    
                if(isset($clientCarsData)) {
                    foreach($clientCarsData as $car) {
                        echo addCar($car);
                        $mindboxId = checkOrderAlreadyExists($row['ЗаказНарядУИД'], $allClientOrders);
    
                        echo processOrderZN($row, $car, $mindboxId);
                    }
                }
            }             

            $i++;
        } catch (Exception $e) {
            echo $e;
        } catch (Error $err) {
            echo "Error: " . $err->getMessage() . "\n";
            break;
        }
    }
}