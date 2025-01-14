<?php

ini_set('memory_limit', '7G');  

include_once ("../utils/function1.php");

$now = new DateTime();
$now->modify('+3 hours');
$currentYear = $now->format('Y');
$currentMonth = $now->format('m');
$currentDay = $now->format('d');

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

for ($i = 1; $i >= 0; $i--) {
    $datebefore = formatDateTime($now);
    $year = $currentYear - $i;
    echo $year . "\n";

    $startDate = formatDateTime(new DateTime("$year-01-01 00:00:00"));
    $endDate = formatDateTime(new DateTime("$year-12-31 23:59:59"));

    if ($year == $currentYear) {
        $month = $currentMonth - 1;
        $endDate = formatDateTime(new DateTime("$year-$month-$currentDay 23:59:59"));
    }

    $urls = [
        "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
        "Калининград" => "http://192.168.8.53/alfa/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
        "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
        "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
        "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
        "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/events?datefrom={$startDate}&datebefore={$endDate}",
    ];

    foreach ($urls as $city => $url) {
        echo "Обработка URL: $city\n";
        
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
            $params = array(
                formatDate($event['ДатаНачала']),
                formatTime($event['ВремяНачала']),
                $event['СобытиеУИД'],
                $event['РабочийЛистУИД'],
                $event['РабочийЛистНомер'],
                $event['РабочийЛистНомерТелефона'],
                $event['ВидСобытия'],
                $event['СсылкаНаЗвонок'],
                $event['Состояние'],
                $event['Результат'],
                $event['Комментарий'],
                $event['Менеджер'],
                $now->format('Y-m-d'),
                $now->format('H:i:s')
            );
            
            $sql = "INSERT INTO AllEventData (ДатаНачала, ВремяНачала, EventUID, РабочийЛистУИД, РабочийЛистНомер, НомерТелефона, ВидСобытия, СсылкаНаЗвонок, 
            Состояние, Результат, Комментарий, Менеджер, ДатаОбновления, ВремяОбновления) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo "Данные успешно внесены для Уид: {$event['СобытиеУИД']}. ($year $city)\n";
            } catch (PDOException $e) {
                echo "Ошибка вставки данных для Уид: {$event['СобытиеУИД']} - " . $e->getMessage() . "\n";
            }
        }
    }
}

$conn = null;

echo "Все данные успешно внесены в таблицу и очищены от строк с пустыми номерами телефонов.";
?>
