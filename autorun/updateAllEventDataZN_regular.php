<?php

include_once("../utils/function1.php");

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
            foreach($worksheet['Работы'] as $alleventdata) {
                if (!isset($alleventdata['АвтоработаУИД'])) {
                    echo "Отсутствует поле 'АвтоработаУИД' в записи.\n";
                    continue;
                }

                $alleventdata['ЗаказНарядУИД'] = $worksheet['ЗаказНарядУИД'];
                unset($alleventdata['ПериодДата']);
                unset($alleventdata['ПериодВремя']);
                unset($alleventdata['КоличествоНормочасов']);
                unset($alleventdata['КоличествоНормочасовИсполнителя']);
                unset($alleventdata['ВыручкаСНДС']);
                unset($alleventdata['ВыручкаБезНДС']);
                unset($alleventdata['СебестоимостьСНДС']);
                unset($alleventdata['СебестоимостьБезНДС']);
                unset($alleventdata['СуммаСкидки']);
                $alleventdata['ЭтоСубподряд'] = $alleventdata['ЭтоСубподряд'] == true ? 1 : 0;
                unset($alleventdata['Исполнители']);

                $workUid = $alleventdata['АвтоработаУИД'];
                $worksheetUid = $worksheet['ЗаказНарядУИД'];
                $query = "SELECT * FROM ZN_AllEventData WHERE АвтоработаУИД = ? AND ЗаказНарядУИД = ?";
                $stmt = $conn->prepare($query);
                $stmt->execute([$workUid, $worksheetUid]);
            
                $existingData = $stmt->fetch(PDO::FETCH_ASSOC);
                $updateNeeded = false;

                if ($existingData) {
                    // Сравнение полей записи
                    foreach ($alleventdata as $key => $value) {
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
                        $updateQuery = "UPDATE ZN_AllEventData SET ДатаОбновления = ?, ВремяОбновления = ?, ";
                        $updateFields = [];
                        $params = [$now->format('Y-m-d'), $now->format('H:i:s')];
                        
                        foreach ($alleventdata as $key => $value) {
                            if ($key !== 'ДатаОбновления' && $key !== 'ВремяОбновления') {
                                $updateFields[] = "$key = ?";
                                $params[] = $value;
                            }
                        }
            
                        $updateQuery .= implode(', ', $updateFields) . " WHERE АвтоработаУИД = ? AND ЗаказНарядУИД = ?";
                        $params[] = $workUid;
                        $params[] = $worksheetUid;
            
                        try {
                            $updateStmt = $conn->prepare($updateQuery);
                            $updateStmt->execute($params);
                            echo "Запись обновлена для АвтоработаУИД: $workUid + $worksheetUid.\n";
                        } catch (PDOException $e) {
                            echo "Ошибка обновления данных для АвтоработаУИД: $workUid + $worksheetUid - " . $e->getMessage() . "\n";
                            echo "\n".$updateQuery;
                        }
                    }
                } else {
                    // Добавление новой записи
                    $insertQuery = "INSERT INTO ZN_AllEventData (" . implode(', ', array_keys($alleventdata)) . ", ДатаОбновления, ВремяОбновления)
                                    VALUES (" . rtrim(str_repeat('?, ', count($alleventdata) + 2), ', ') . ")";
                    $params = array_merge(array_values($alleventdata), [$now->format('Y-m-d'), $now->format('H:i:s')]);
            
                    try {
                        $insertStmt = $conn->prepare($insertQuery);
                        $insertStmt->execute($params);
                        echo "Новая запись добавлена для АвтоработаУИД: $workUid.\n";
                    } catch (PDOException $e) {
                        echo "Ошибка вставки данных для АвтоработаУИД: $workUid - " . $e->getMessage() . "\n";
                    }
                }
            }
        }
    }
}

$conn = null;

echo "Обработка завершена.";

return $resultArray;

?>
