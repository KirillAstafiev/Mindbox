<?php

include_once("../utils/function1.php");

$now = new DateTime();
$now->modify('+3 hours');
$dateFrom = (new DateTime())->modify('-21 days')->format('Ymd');
$dateBefore = $now->format('Ymd');

$username = 'odata';
$password = 'Ghtwejhrjk4';

$dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
$dbUsername = "sa";
$dbPassword = "123aA123";

try {
    $conn = new PDO($dsn, $dbUsername, $dbPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "[EventData] Соединение с базой данных установлено.\n";
} catch (PDOException $e) {
    echo "[EventData] Ошибка подключения к базе данных: " . $e->getMessage();
    exit;
}

$urls = [
    "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Калининград" => "http://192.168.8.53/alfa/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
    "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/worksheets?datefrom={$dateFrom}&datebefore={$dateBefore}",
];

$resultArray = [];

foreach ($urls as $city => $url) {
    echo "[EventData] Обработка URL: $city\n";
    echo $url . "\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo '[EventData] Ошибка: ' . curl_error($ch);
        curl_close($ch);
        continue;
    }

    curl_close($ch);

    $data = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo '[EventData] Ошибка декодирования JSON: ' . json_last_error_msg();
        continue;
    }

    foreach ($data as $event) {
        if (!isset($event['РабочийЛист'])) {
            echo "[EventData] Отсутствует поле 'РабочийЛист' в записи.\n";
            continue;
        }

        if (isset($event['ДатаСобытия'])) {
            $date = DateTime::createFromFormat('d.m.Y', $event['ДатаСобытия']);
            if ($date) {
                $event['ДатаСобытия'] = $date->format('Y-m-d');
            } else {
                echo "[EventData] Ошибка преобразования ДатаСобытия: {$event['ДатаСобытия']} не соответствует формату d.m.Y.\n";
            }
        }

        if (isset($event['ВремяСобытия'])) {
            $time = DateTime::createFromFormat('H:i:s', $event['ВремяСобытия']);
            if ($time) {
                $event['ВремяСобытия'] = $time->format('H:i:s.0000000');
            } else {
                echo "[EventData] Ошибка преобразования ВремяСобытия: {$event['ВремяСобытия']} не соответствует формату H:i:s.\n";
            }
        }

        $event['НаселенныйПункт'] = $city;
        $event['ПричинаОтказа'] = $event['РабочийЛистПричинаОтказа'];
        $event['Телефон'] = $event['РабочийЛистТелефон'];
        $event['МодельАвто'] = $event['АвтомобильМодель'];
        $event['Организация'] = $event['РабочийЛистОрганизация'];
        $event['ТипСвязи'] = $event['Тип'];
        $event['NB'] = $event['АсП'] ? "АСП" : "ОПНА";
        $event['МаркаАвто'] = $event['АвтомобильМарка'];
        $event['МодельАвто'] = $event['АвтомобильМодель'];
        unset($event['АсП']);
        unset($event['Тип']);
        unset($event['РабочийЛистПричинаОтказа']);
        unset($event['РабочийЛистТелефон']);
        unset($event['КлиентТип']);
        unset($event['АвтомобильМарка']);
        unset($event['АвтомобильМодель']);
        unset($event['РабочийЛист_utm_source']);
        unset($event['РабочийЛист_utm_medium']);
        unset($event['РабочийЛист_utm_campaign']);
        unset($event['РабочийЛист_utm_content']);
        unset($event['РабочийЛист_utm_term']);
        unset($event['Событие_utm_source']);
        unset($event['Событие_utm_medium']);
        unset($event['Событие_utm_campaign']);
        unset($event['Событие_utm_content']);
        unset($event['Событие_utm_term']);
        unset($event['АсП']);
        unset($event['РабочийЛистОрганизация']);
        unset($event['Тип']);

        $workList = $event['РабочийЛист'];
        $query = "SELECT * FROM EventData WHERE РабочийЛист = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$workList]);

        $existingData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Проверка необходимости обновления
        $updateNeeded = false;

        if ($existingData) {
            // Обработка массива ВариантыАвтомобилей
            if (isset($event['ВариантыАвтомобилей']) && is_array($event['ВариантыАвтомобилей'])) {
                foreach ($event['ВариантыАвтомобилей'] as $variant) {
                    if (isset($variant['АвтомобильУИД']) && isset($variant['АвтомобильVIN'])) {
                        if ($existingData['АвтомобильУИД'] != $variant['АвтомобильУИД'] || $existingData['АвтомобильVIN'] != $variant['АвтомобильVIN']) {
                            echo "АвтомобильУИД: {$existingData['АвтомобильУИД']} -> {$variant['АвтомобильУИД']}\n";
                            echo "АвтомобильVIN: {$existingData['АвтомобильVIN']} -> {$variant['АвтомобильVIN']}\n";

                            $event['АвтомобильУИД'] = $variant['АвтомобильУИД'];
                            $event['АвтомобильVIN'] = $variant['АвтомобильVIN'];
                            $updateNeeded = true;
                        }
                        break;
                    }
                }
            }
        }

        unset($event['ВариантыАвтомобилей']);

        if ($existingData) {
            // Сравнение полей записи
            foreach ($event as $key => $value) {
                if (array_key_exists($key, $existingData) && $key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                    if ($existingData[$key] != $value) {
                        echo "\n" . $key . "\n";
                        echo "$existingData[$key] -> $value \n";
                        $updateNeeded = true;
                        break;
                    }
                }
            }

            // Выполнение обновления при необходимости
            if ($updateNeeded) {
                $updateQuery = "UPDATE EventData SET ДатаОбновления = ?, ВремяОбновления = ?, ";
                $updateFields = [];
                $params = [$now->format('Y-m-d'), $now->format('H:i:s')];

                foreach ($event as $key => $value) {
                    if ($key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                        $updateFields[] = "$key = ?";
                        $params[] = $value;
                    }
                }

                $updateQuery .= implode(', ', $updateFields) . " WHERE РабочийЛист = ?";
                $params[] = $workList;

                try {
                    $updateStmt = $conn->prepare($updateQuery);
                    $updateStmt->execute($params);
                    echo "[EventData] Запись обновлена для РабочийЛист: $workList.\n";
                    $resultArray[] = $workList;
                } catch (PDOException $e) {
                    echo "[EventData] Ошибка обновления данных для РабочийЛист: $workList - " . $e->getMessage() . "\n";
                }
            }
        } else {
            // Добавление новой записи
            $insertQuery = "INSERT INTO EventData (" . implode(', ', array_keys($event)) . ", ДатаОбновления, ВремяОбновления)
                            VALUES (" . rtrim(str_repeat('?, ', count($event) + 2), ', ') . ")";
            $params = array_merge(array_values($event), [$now->format('Y-m-d'), $now->format('H:i:s')]);

            try {
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->execute($params);
                echo "[EventData] Новая запись добавлена для РабочийЛист: $workList.\n";
                $resultArray[] = $workList;
            } catch (PDOException $e) {
                echo "[EventData] Ошибка вставки данных для РабочийЛист: $workList - " . $e->getMessage() . "\n";
            }
        }
    }
}

$conn = null;

echo "[EventData] Обработка завершена.\n";

return $resultArray;
