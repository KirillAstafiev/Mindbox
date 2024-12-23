<?php

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

for ($i = 4; $i >= 0; $i--) {
    $datebefore = formatDateTime($now);
    $year = $currentYear - $i;
    echo $year . "\n";

    $startDate = formatDateTime(new DateTime("$year-01-01 00:00:00"));
    $endDate = formatDateTime(new DateTime("$year-12-31 23:59:59"));

    if($year == $currentYear){
        $month = $currentMonth - 1;
        $endDate = formatDateTime(new DateTime("$year-$month-$currentDay 23:59:59"));
    }

    $urls = [
        "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
        "Калининград" => "http://192.168.8.53/alfa/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
        "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
        "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
        "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
        "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/worksheets?datefrom={$startDate}&datebefore={$endDate}",
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
            if (!isset($event['ДатаСобытия'], $event['ВремяСобытия'], $event['Уид'], $event['ВидСобытия'], $event['РабочийЛист'])) {
                echo 'Некоторые данные отсутствуют в записи.';
                continue;
            }
    
            $phone = decodeIncorrectEncoding($event['Телефон']);
            $workListPhone = decodeIncorrectEncoding($event['РабочийЛистТелефон']);
        
            $utm_sourсe = $event['РабочийЛист_utm_source'];
            $utm_sourсeSob = $event['Событие_utm_source'];
            if (!empty($utm_sourсeSob) && $utm_sourсeSob !== $utm_sourсe) {
                $utm_sourсe = $utm_sourсeSob;
            }

            $utm_medium = $event['РабочийЛист_utm_medium'];
            $utm_mediumSob = $event['Событие_utm_medium'];
            if (!empty($utm_mediumSob) && $utm_mediumSob !== $utm_medium) {
                $utm_medium = $utm_mediumSob;
            }

            if (!empty($workListPhone) && $workListPhone !== $phone) {
                $phone = $workListPhone;
            }

            $result = $utm_sourсe . '+' . $utm_medium;
            $ASPID = $event['АсП'] ? 1 : 0;
        
            $number1 = crc32($city);
        
            if ($ASPID === 1) {
                // Действия, если $ASPID равно 1 (true)
                $number2 = "000";
                $brend =  "АСП";
                $NB =  "АСП";
            } else {
                $number2 = crc32($event['АвтомобильМарка']);
                $brend =  $event['АвтомобильМарка'];
                $NB =  "ОПНА";
            }
        
            $siteID = $number1 .$ASPID . $number2;

            $carVIN = $event['ВариантыАвтомобилей'][0]['АвтомобильVIN'] ?? null;
            $carUID = $event['ВариантыАвтомобилей'][0]['АвтомобильУИД'] ?? null;
    
            $params = array(
                formatDate($event['ДатаСобытия']),
                formatTime($event['ВремяСобытия']),
                $event['Уид'],
                $event['ВидСобытия'],
                $event['РабочийЛист'],
                $event['Клиент'],
                $event['РабочийЛистНомер'],
                $event['РабочийЛистСтатус'],
                $event['РабочийЛистПричинаОтказа'],
                $phone, // Используем обновленное значение телефона
                $event['АвтомобильМарка'],
                $event['АвтомобильМодель'],
                $event['РабочийЛистОрганизация'],
                $event['Тип'],
                $event['Менеджер'],
                $event['Автор'],
                $event['ДиалогИД'],
                $event['ЭлПочты'],
                $event['РабочийЛистПодразделение'],
                $event['МенеджерПодразделение'],
                $result,
                $siteID,
                $NB,  // Направление бизнеса
                $carUID,
                $carVIN,
                $city,
                $event['КлиентСогласиеНаПолучениеСМС'],
                $event['КлиентОтказОтРекламы'],
                $now->format('Y-m-d'),
                $now->format('H:i:s')
            );

            $sql = "INSERT INTO EventData (ДатаСобытия, ВремяСобытия, Уид, ВидСобытия, РабочийЛист, Клиент, РабочийЛистНомер, РабочийЛистСтатус, ПричинаОтказа, Телефон,
            МаркаАвто, МодельАвто, Организация, ТипСвязи, Менеджер, Автор, ДиалогИД, ЭлПочты, РабочийЛистПодразделение, МенеджерПодразделение,
            result,  site_id, NB, АвтомобильУИД, АвтомобильVIN, НаселенныйПункт, КлиентСогласиеНаПолучениеСМС, КлиентОтказОтРекламы, ДатаОбновления, ВремяОбновления) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
            try {
                $stmt = $conn->prepare($sql);
                $stmt->execute($params);
                echo "Данные успешно внесены для Уид: {$event['Уид']}.\n";
            } catch (PDOException $e) {
                echo "Ошибка вставки данных для Уид: {$event['Уид']} - " . $e->getMessage() . "\n";
            }
        }
    }
}

$conn = null;

echo "Данные добавлены";

?>