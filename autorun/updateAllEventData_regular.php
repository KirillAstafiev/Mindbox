<?php

include_once("../utils/function1.php");

function formatDateTimeField(array &$event, string $field, string $inputFormat, string $outputFormat, string $fieldType = 'date'): void {
    if (isset($event[$field])) {
        $formattedValue = DateTime::createFromFormat($inputFormat, $event[$field]);
        if ($formattedValue) {
            $event[$field] = $formattedValue->format($outputFormat);
        } else {
            echo "Ошибка преобразования {$field}: {$event[$field]} не соответствует формату {$inputFormat}.\n";
        }
    }
}

$now = new DateTime();
$now->modify('+3 hours');
$dateFrom = (new DateTime())->modify('-3 days')->format('Ymd');
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
    "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Калининград" => "http://192.168.8.53/alfa/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/events?datefrom={$dateFrom}&datebefore={$dateBefore}",
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
        if (!isset($event['РабочийЛистУИД'])) {
            echo "Отсутствует поле 'РабочийЛистУИД' в записи.\n";
            continue;
        }
        
        formatDateTimeField($event, 'ДатаНачала', 'd.m.Y', 'Y-m-d');
        formatDateTimeField($event, 'ВремяНачала', 'H:i:s', 'H:i:s.0000000');

        $event['EventUID'] = $event['СобытиеУИД'];

        unset($event['РабочийЛистНомерТелефона']);
        unset($event['СобытиеУИД']);
        unset($event['МенеджерУИД']);
        unset($event['РабочийЛистНаПродажуНовогоАвтомобиля']);
        unset($event['РабочийЛистЭлПочта']);
        unset($event['ХозОперация']);
        unset($event['Содержание']);
        unset($event['Результат']);
        unset($event['Комментарий']);
        unset($event['ДатаДокумента']);
        unset($event['СсылкаНаЗвонок']);
        unset($event['ВремяДокумента']);
        unset($event['РабочийЛистТрейдИнНомер']);
        unset($event['РабочийЛистТрейдИнУИД']);

        $workList = $event['РабочийЛистУИД'];
        $eventUid = $event['EventUID'];
        $query = "SELECT * FROM AllEventData WHERE РабочийЛистУИД = ? AND EventUID = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$workList, $eventUid]);
    
        $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
    
        // Проверка необходимости обновления
        $updateNeeded = false;
    
        if ($existingData) {
            // Сравнение полей записи
            foreach ($event as $key => $value) {
                if (array_key_exists($key, $existingData) && $key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                    if ($existingData[$key] != $value) {
                        echo "\n".$key."\n";
                        echo "$existingData[$key] -> $value \n";
                        $updateNeeded = true;
                        break;
                    }
                }
            }
    
            // Выполнение обновления при необходимости
            if ($updateNeeded) {
                $updateQuery = "UPDATE AllEventData SET ДатаОбновления = ?, ВремяОбновления = ?, ";
                $updateFields = [];
                $params = [$now->format('Y-m-d'), $now->format('H:i:s')];
    
                foreach ($event as $key => $value) {
                    if ($key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                        $updateFields[] = "$key = ?";
                        $params[] = $value;
                    }
                }
    
                $updateQuery .= implode(', ', $updateFields) . " WHERE РабочийЛистУИД = ? AND EventUID = ?";
                $params[] = $workList;
                $params[] = $eventUid;
    
                try {
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->execute($params);
                    echo "Запись обновлена для EventUID: $eventUid.\n";
                    $resultArray[] = $workList;
                } catch (PDOException $e) {
                    echo "Ошибка обновления данных для EventUID: $eventUid - " . $e->getMessage() . "\n";
                }
            } 
        } else {
            // Добавление новой записи
            $insertQuery = "INSERT INTO AllEventData (" . implode(', ', array_keys($event)) . ", ДатаОбновления, ВремяОбновления)
                            VALUES (" . rtrim(str_repeat('?, ', count($event) + 2), ', ') . ")";
            $params = array_merge(array_values($event), [$now->format('Y-m-d'), $now->format('H:i:s')]);
    
            try {
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute($params);
                echo "Новая запись добавлена для EventUID: $eventUid.\n";
            } catch (PDOException $e) {
                echo "Ошибка вставки данных для EventUID: $eventUid - " . $e->getMessage() . "\n";
            }
        }
    }
}

$conn = null;

echo "Обработка завершена";

return $resultArray;