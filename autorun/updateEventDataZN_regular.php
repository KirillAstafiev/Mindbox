<?php

include_once("../utils/function1.php");

$now = new DateTime();
$now->modify('+3 hours');
$dateFrom = (new DateTime())->modify('-30 days')->format('Ymd');
$dateBefore = $now->format('Ymd');

$username = 'odata';
$password = 'Ghtwejhrjk4';

$dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
$dbUsername = "sa";
$dbPassword = "123aA123";

try {
    $conn = new PDO($dsn, $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Соединение с базой данных установлено.\n";
} catch (PDOException $e) {
    echo "Ошибка подключения к базе данных: " . $e->getMessage();
    exit;
}

$urls = [
    "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
    "Калининград" => "http://192.168.8.53/alfa/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
    "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
    "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
    "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
    "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/sales/service?DateFrom={$dateFrom}&DateBefore={$dateBefore}",
];

$resultArray = [];

foreach ($urls as $city => $url) {
    echo "Обработка URL: $city\n";
    echo $url."\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Ошибка: ' . curl_error($ch);
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'Ошибка декодирования JSON: ' . json_last_error_msg();
        continue;
    }

    foreach ($data as $event) {
        foreach($event['ЗаказНаряды'] as $worksheet) {
            if (!isset($worksheet['ЗаказНарядУИД'])) {
                echo "Отсутствует поле 'ЗаказНарядУИД' в записи.\n";
                continue;
            }

            if (isset($worksheet['ЗаказНарядДатаСоздания'])) {
                $date = DateTime::createFromFormat('d.m.Y', $worksheet['ЗаказНарядДатаСоздания']);
                if ($date) {
                    $worksheet['ЗаказНарядДатаСоздания'] = $date->format('Y-m-d');
                } else {
                    echo "Ошибка преобразования ЗаказНарядДатаСоздания: {$worksheet['ЗаказНарядДатаСоздания']} не соответствует формату d.m.Y.\n";
                }
            }
            
            if (isset($worksheet['ЗаказНарядВремяСоздания'])) {
                $time = DateTime::createFromFormat('H:i:s', $worksheet['ЗаказНарядВремяСоздания']);
                if ($time) {
                    $worksheet['ЗаказНарядВремяСоздания'] = $time->format('H:i:s.0000000');
                } else {
                    echo "Ошибка преобразования ЗаказНарядВремяСоздания: {$worksheet['ЗаказНарядВремяСоздания']} не соответствует формату H:i:s.\n";
                }
            }

            if (isset($worksheet['ЗаказНарядДатаЗакрытия'])) {
                $date = DateTime::createFromFormat('d.m.Y', $worksheet['ЗаказНарядДатаЗакрытия']);
                if ($date) {
                    $worksheet['ЗаказНарядДатаЗакрытия'] = $date->format('Y-m-d');
                } else {
                    echo "Ошибка преобразования ЗаказНарядДатаЗакрытия: {$worksheet['ЗаказНарядДатаСоздания']} не соответствует формату d.m.Y.\n";
                }
            }

            if (isset($worksheet['ЗаказНарядВремяЗакрытия'])) {
                $time = DateTime::createFromFormat('H:i:s', $worksheet['ЗаказНарядВремяЗакрытия']);
                if ($time) {
                    $worksheet['ЗаказНарядВремяЗакрытия'] = $time->format('H:i:s.0000000');
                } else {
                    echo "Ошибка преобразования ЗаказНарядВремяЗакрытия: {$worksheet['ЗаказНарядВремяЗакрытия']} не соответствует формату H:i:s.\n";
                }
            }

            if (isset($worksheet['ЗаказНарядПлановаяДатаВыдачи'])) {
                $date = DateTime::createFromFormat('d.m.Y', $worksheet['ЗаказНарядПлановаяДатаВыдачи']);
                if ($date) {
                    $worksheet['ЗаказНарядПлановаяДатаВыдачи'] = $date->format('Y-m-d');
                } else {
                    echo "Ошибка преобразования ЗаказНарядПлановаяДатаВыдачи: {$worksheet['ЗаказНарядПлановаяДатаВыдачи']} не соответствует формату d.m.Y.\n";
                }
            }

            if (isset($worksheet['ЗаказНарядПлановоеВремяВыдачи'])) {
                $time = DateTime::createFromFormat('H:i:s', $worksheet['ЗаказНарядПлановоеВремяВыдачи']);
                if ($time) {
                    $worksheet['ЗаказНарядПлановоеВремяВыдачи'] = $time->format('H:i:s.0000000');
                } else {
                    echo "Ошибка преобразования ЗаказНарядПлановоеВремяВыдачи: {$worksheet['ЗаказНарядПлановоеВремяВыдачи']} не соответствует формату H:i:s.\n";
                }
            }

            if (isset($worksheet['ЗаказНарядФактическаяДатаВыдачи'])) {
                $date = DateTime::createFromFormat('d.m.Y', $worksheet['ЗаказНарядФактическаяДатаВыдачи']);
                if ($date) {
                    $worksheet['ЗаказНарядФактическаяДатаВыдачи'] = $date->format('Y-m-d');
                } else {
                    $worksheet['ЗаказНарядФактическаяДатаВыдачи'] = '1900-01-01';
                }
            }
            
            if (isset($worksheet['ЗаказНарядФактическоеВремяВыдачи'])) {
                $time = DateTime::createFromFormat('H:i:s', $worksheet['ЗаказНарядФактическоеВремяВыдачи']);
                if ($time) {
                    $worksheet['ЗаказНарядФактическоеВремяВыдачи'] = $time->format('H:i:s.0000000');
                } else {
                    $worksheet['ЗаказНарядФактическоеВремяВыдачи'] = '00:00:00.0000000';
                }
            }

            if (
                empty($worksheet['ЗаказНарядФактическаяДатаВыдачи']) || 
                $worksheet['ЗаказНарядФактическаяДатаВыдачи'] === '1900-01-01' ||
                empty($worksheet['ЗаказНарядФактическоеВремяВыдачи'])
            ) {
                if (
                    !empty($worksheet['ЗаказНарядПлановаяДатаВыдачи']) &&
                    !empty($worksheet['ЗаказНарядПлановоеВремяВыдачи'])
                ) {
                    $worksheet['ЗаказНарядСтатус'] = 'Готово к выдаче';
                } else {
                    $worksheet['ЗаказНарядСтатус'] = 'В работе';
                }
            } elseif (
                !empty($worksheet['ЗаказНарядФактическаяДатаВыдачи']) &&
                $worksheet['ЗаказНарядФактическаяДатаВыдачи'] !== '1900-01-01' &&
                !empty($worksheet['ЗаказНарядФактическоеВремяВыдачи'])
            ) {
                $worksheet['ЗаказНарядСтатус'] = 'Выдано';
            } else {
                $worksheet['ЗаказНарядСтатус'] = 'Завершено';
            }     
         
            $worksheet['Автомобиль'] = $event['Автомобиль'];
            $worksheet['АвтомобильУИД'] = $event['АвтомобильУИД'];
            $worksheet['АвтомобильVIN'] = $event['АвтомобильVIN'];
            $worksheet['АвтомобильМарка'] = $event['АвтомобильМарка'];
            $worksheet['АвтомобильМодель'] = $event['АвтомобильМодель'];
            $worksheet['АвтомобильГодВыпуска'] = $event['АвтомобильГодВыпуска'];
            $worksheet['ЗаказНарядЗаказчикОтказОтРекламы'] = $worksheet['ЗаказНарядЗаказчикОтказОтРекламы'] == true ? 1 : 0;
            $worksheet['ЗаказНарядЗаказчикСогласиеНаПолучениеСМС'] = $worksheet['ЗаказНарядЗаказчикСогласиеНаПолучениеСМС'] == true ? 1 : 0;

            unset($worksheet['ЗаказНарядСуммаДокумента']);
            unset($worksheet['ЗаказНарядСуммаЗапчастей']);
            unset($worksheet['ЗаказНарядСуммаСкидкиЗапчастей']);
            unset($worksheet['ЗаказНарядСуммаРабот']);
            unset($worksheet['ЗаказНарядСуммаСкидкиРабот']);
            unset($worksheet['ЗаявкаНаРемонт_utm_source']);
            unset($worksheet['ЗаявкаНаРемонт_utm_medium']);
            unset($worksheet['ЗаявкаНаРемонт_utm_campaign']);
            unset($worksheet['ЗаявкаНаРемонт_utm_content']);
            unset($worksheet['ЗаявкаНаРемонт_utm_term']);
            unset($worksheet['Работы']);
            unset($worksheet['Запчасти']);

            $workList = $worksheet['ЗаказНарядУИД'];
            $query = "SELECT * FROM ZN_EventData WHERE ЗаказНарядУИД = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$workList]);
        
            $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
            $updateNeeded = false;

            if ($existingData) {
                // Сравнение полей записи
                foreach ($worksheet as $key => $value) {
                    if (array_key_exists($key, $existingData) && $key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                        if (trim($existingData[$key]) != trim($value)) {
                            echo "\n".$key."\n";
                            echo "$existingData[$key] -> $value \n";
                            $updateNeeded = true;
                            break;
                        }
                    }
                }
        
                // Выполнение обновления при необходимости
                if ($updateNeeded) {
                    $updateQuery = "UPDATE ZN_EventData SET ДатаОбновления = ?, ВремяОбновления = ?, ";
                    $updateFields = [];
                    $params = [$now->format('Y-m-d'), $now->format('H:i:s')];
                    
                    foreach ($worksheet as $key => $value) {
                        if ($key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                            $updateFields[] = "$key = ?";
                            $params[] = $value;
                        }
                    }
        
                    $updateQuery .= implode(', ', $updateFields) . " WHERE ЗаказНарядУИД = ?";
                    $params[] = $workList;
        
                    try {
                        $updateStmt = $conn->prepare($updateQuery);
                        $updateStmt->execute($params);
                        echo "Запись обновлена для ЗаказНарядУИД: $workList.\n";
                        $resultArray[] = $workList;
                    } catch (PDOException $e) {
                        echo "Ошибка обновления данных для ЗаказНарядУИД: $workList - " . $e->getMessage() . "\n";
                    }
                }
            } else {
                // Добавление новой записи
                $insertQuery = "INSERT INTO ZN_EventData (" . implode(', ', array_keys($worksheet)) . ", ДатаОбновления, ВремяОбновления)
                                VALUES (" . rtrim(str_repeat('?, ', count($worksheet) + 2), ', ') . ")";
                $params = array_merge(array_values($worksheet), [$now->format('Y-m-d'), $now->format('H:i:s')]);
        
                try {
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->execute($params);
                    echo "Новая запись добавлена для ЗаказНарядУИД: $workList.\n";
                    $resultArray[] = $workList;
                } catch (PDOException $e) {
                    echo "Ошибка вставки данных для ЗаказНарядУИД: $workList - " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

$conn = null;

echo "Обработка завершена. Итоговые ЗаказНаряды: " . implode(', ', $resultArray) . "\n";

return $resultArray;

?>
