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

    $startDate = formatDateTime(new DateTime("$year-01-01 00:00:00"));
    $endDate = formatDateTime(new DateTime("$year-12-31 23:59:59"));

    if($year == $currentYear) {
        $month = $currentMonth - 1;
        $endDate = formatDateTime(new DateTime("$year-$month-$currentDay 23:59:59"));
    }

    $urls = [
        "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
        "Калининград" => "http://192.168.8.53/alfa/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
        "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
        "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
        "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
        "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/sales/service?DateFrom={$startDate}&DateBefore={$endDate}",
    ];

    foreach ($urls as $city => $url) {
        echo "Обработка: $city $year \n";
        echo "$url \n";
        
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

        foreach ($data as $item) {
            if (!isset($item['АвтомобильУИД'], $item['АвтомобильVIN'])) {
                echo "Некоторые данные отсутствуют для города: $city\n";
                continue;
            }

            $vehicle = $item['Автомобиль'];
            $vehicleUID = $item['АвтомобильУИД'];
            $vehicleVIN = $item['АвтомобильVIN'];
            $vehicleBrand = $item['АвтомобильМарка'];
            $vehicleModel = $item['АвтомобильМодель'];
            $vehicleYear = $item['АвтомобильГодВыпуска'];
            $orderCount  = $item['КоличествоЗаказНарядов'];

            foreach ($item['ЗаказНаряды'] as $ZN) {
                if (!isset($ZN['ЗаказНарядУИД'])) {
                    echo "Некоторые данные отсутствуют для ZN: $auto\n";
                    continue;
                }

                $orderStatus = 'В работе';

                // ZN_EventData
                $organization = $ZN['Организация'] ?? null;
                $organizationUID = $ZN['ОрганизацияУИД'] ?? null;
                $department = $ZN['Подразделение'] ?? null;
                $departmentUID = $ZN['ПодразделениеУИД'] ?? null;
                $orderNumber = $ZN['ЗаказНарядНомер'] ?? null;
                $orderUID = $ZN['ЗаказНарядУИД'] ?? null; // вот он
                $orderCreationDate = $ZN['ЗаказНарядДатаСоздания'] ?? null;
                $orderCreationTime = $ZN['ЗаказНарядВремяСоздания'] ?? null;
                $orderClosingDate = $ZN['ЗаказНарядДатаЗакрытия'] ?? null;
                $orderClosingTime = $ZN['ЗаказНарядВремяЗакрытия'] ?? null;
                $orderRepairType = $ZN['ЗаказНарядВидРемонта'] ?? null;
                $orderRepairCategory = $ZN['ЗаказНарядТипРемонта'] ?? null;
                $orderMaintenanceType = $ZN['ЗаказНарядВидТО'] ?? null;
                $orderMaster = $ZN['ЗаказНарядМастер'] ?? null;
                $orderMasterUID = $ZN['ЗаказНарядМастерУИД'] ?? null;
                $orderManager = $ZN['ЗаказНарядМенеджер'] ?? null;
                $orderManagerUID = $ZN['ЗаказНарядМенеджерУИД'] ?? null;
                $orderPlannedDeliveryDate = $ZN['ЗаказНарядПлановаяДатаВыдачи'] ?? null;
                $orderPlannedDeliveryTime = $ZN['ЗаказНарядПлановоеВремяВыдачи'] ?? null;
                $orderActualDeliveryDate = $ZN['ЗаказНарядФактическаяДатаВыдачи'] ?? null;
                $orderActualDeliveryTime = $ZN['ЗаказНарядФактическоеВремяВыдачи'] ?? null;
                $orderCustomer = $ZN['ЗаказНарядЗаказчик'] ?? null;
                $orderCustomerUID = $ZN['ЗаказНарядЗаказчикУИД'] ?? null;
                $orderCustomerType = $ZN['ЗаказНарядЗаказчикТип'] ?? null;
                $orderCustomerPhone = $ZN['ЗаказНарядЗаказчикТелефон'] ?? null;
                $orderCustomerEmail = $ZN['ЗаказНарядЗаказчикЭлПочта'] ?? null;
                $orderCustomerSMSConsent = $ZN['ЗаказНарядЗаказчикСогласиеНаПолучениеСМС'] ?? null;
                $orderCustomerAdRefusal = $ZN['ЗаказНарядЗаказчикОтказОтРекламы'] ?? null;
                $vehicleMileage = $ZN['АвтомобильПробег'] ?? null;
                
                $sql = "INSERT INTO ZN_EventData (
                        Организация, ОрганизацияУИД, Подразделение, ПодразделениеУИД, ЗаказНарядНомер, 
                        ЗаказНарядУИД, ЗаказНарядДатаСоздания, ЗаказНарядВремяСоздания, ЗаказНарядДатаЗакрытия, 
                        ЗаказНарядВремяЗакрытия, ЗаказНарядВидРемонта, ЗаказНарядТипРемонта, ЗаказНарядВидТО, 
                        ЗаказНарядМастер, ЗаказНарядМастерУИД, ЗаказНарядМенеджер, ЗаказНарядМенеджерУИД, 
                        ЗаказНарядПлановаяДатаВыдачи, ЗаказНарядПлановоеВремяВыдачи, ЗаказНарядФактическаяДатаВыдачи, 
                        ЗаказНарядФактическоеВремяВыдачи, ЗаказНарядЗаказчик, ЗаказНарядЗаказчикУИД, 
                        ЗаказНарядЗаказчикТип, ЗаказНарядЗаказчикТелефон, ЗаказНарядЗаказчикЭлПочта, 
                        ЗаказНарядЗаказчикСогласиеНаПолучениеСМС, ЗаказНарядЗаказчикОтказОтРекламы, ЗаказНарядСтатус,
                        Автомобиль, АвтомобильУИД, АвтомобильVIN, АвтомобильМарка, АвтомобильМодель, 
                        АвтомобильГодВыпуска, АвтомобильПробег) 
                        VALUES (
                        :organization, :organizationUID, :department, :departmentUID, :orderNumber, 
                        :orderUID, :orderCreationDate, :orderCreationTime, :orderClosingDate, 
                        :orderClosingTime, :orderRepairType, :orderRepairCategory, :orderMaintenanceType, 
                        :orderMaster, :orderMasterUID, :orderManager, :orderManagerUID, 
                        :orderPlannedDeliveryDate, :orderPlannedDeliveryTime, :orderActualDeliveryDate, 
                        :orderActualDeliveryTime, :orderCustomer, :orderCustomerUID, 
                        :orderCustomerType, :orderCustomerPhone, :orderCustomerEmail, 
                        :orderCustomerSMSConsent, :orderCustomerAdRefusal, :orderStatus, :vehicle, 
                        :vehicleUID, :vehicleVIN, :vehicleBrand, :vehicleModel, :vehicleYear, :vehicleMileage)";

                try {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([
                        ':organization' => $organization,
                        ':organizationUID' => $organizationUID,
                        ':department' => $department,
                        ':departmentUID' => $departmentUID,
                        ':orderNumber' => $orderNumber,
                        ':orderUID' => $orderUID,
                        ':orderCreationDate' => formatDate($orderCreationDate),
                        ':orderCreationTime' => formatTime($orderCreationTime),
                        ':orderClosingDate' => formatDate($orderClosingDate),
                        ':orderClosingTime' => formatTime($orderClosingTime),
                        ':orderRepairType' => $orderRepairType,
                        ':orderRepairCategory' => $orderRepairCategory,
                        ':orderMaintenanceType' => $orderMaintenanceType,
                        ':orderMaster' => $orderMaster,
                        ':orderMasterUID' => $orderMasterUID,
                        ':orderManager' => $orderManager,
                        ':orderManagerUID' => $orderManagerUID,
                        ':orderPlannedDeliveryDate' => $orderPlannedDeliveryDate,
                        ':orderPlannedDeliveryTime' => $orderPlannedDeliveryTime,
                        ':orderActualDeliveryDate' => $orderActualDeliveryDate,
                        ':orderActualDeliveryTime' => $orderActualDeliveryTime,
                        ':orderCustomer' => $orderCustomer,
                        ':orderCustomerUID' => $orderCustomerUID,
                        ':orderCustomerType' => $orderCustomerType,
                        ':orderCustomerPhone' => $orderCustomerPhone,
                        ':orderCustomerEmail' => $orderCustomerEmail,
                        ':orderCustomerSMSConsent' => $orderCustomerSMSConsent,
                        ':orderCustomerAdRefusal' => $orderCustomerAdRefusal,
                        ':orderStatus' => $orderStatus,
                        ':vehicle' => $vehicle,
                        ':vehicleUID' => $vehicleUID,
                        ':vehicleVIN' => $vehicleVIN,
                        ':vehicleBrand' => $vehicleBrand,
                        ':vehicleModel' => $vehicleModel,
                        ':vehicleYear' => $vehicleYear,
                        ':vehicleMileage' => $vehicleMileage,
                    ]);

                    echo "Данные успешно вставлены.\n";
                } catch (PDOException $e) {
                    echo "Ошибка вставки данных: " . $e->getMessage() . "\n";
                }

                foreach($ZN['Работы'] as $work) {
                    if (!isset($work['Цех'])) {
                        echo "Некоторые данные отсутствуют для ZN: $work\n";
                        continue;
                    }

                    $ttt = $orderUID;

                    // ZN_AllEventData
                    $workshop = $work['Цех'] ?? null;
                    $workshopUID = $work['ЦехУИД'] ?? null;
                    $item = $work['Номенклатура'] ?? null;
                    $itemUID = $work['НоменклатураУИД'] ?? null;
                    $itemType = $work['НоменклатураВидНоменклатуры'] ?? null;
                    $workDescription = $work['Авторабота'] ?? null;
                    $workUID = $work['АвтоработаУИД'] ?? null;
                    $quantity = $work['Количество'] ?? null;
                    $isSubcontract = $work['ЭтоСубподряд'] ?? null;
                    $subcontractor = $work['Субподрядчик'] ?? null;
                    $subcontractorUID = $work['СубподрядчикУИД'] ?? null;
                    $orderUID = $ZN['ЗаказНарядУИД'] ?? null;

                    $sql = "INSERT INTO ZN_AllEventData (
                            Цех, ЦехУИД, Номенклатура, НоменклатураУИД, НоменклатураВидНоменклатуры, 
                            Авторабота, АвтоработаУИД, Количество, ЭтоСубподряд, Субподрядчик, 
                            СубподрядчикУИД, ЗаказНарядУИД)
                            VALUES (
                            :workshop, :workshopUID, :item, :itemUID, :itemType, 
                            :workDescription, :workUID, :quantity, :isSubcontract, :subcontractor, 
                            :subcontractorUID, :orderUID)";

                    try {
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([
                            ':workshop' => $workshop,
                            ':workshopUID' => $workshopUID,
                            ':item' => $item,
                            ':itemUID' => $itemUID,
                            ':itemType' => $itemType,
                            ':workDescription' => $workDescription,
                            ':workUID' => $workUID,
                            ':quantity' => $quantity,
                            ':isSubcontract' => $isSubcontract,
                            ':subcontractor' => $subcontractor,
                            ':subcontractorUID' => $subcontractorUID,
                            ':orderUID' => $orderUID
                        ]);

                        echo "Данные успешно вставлены для работы: $workDescription\n";
                    } catch (PDOException $e) {
                        echo "Ошибка вставки данных: " . $e->getMessage() . "\n";
                    }       
                }
            }
        }

        try {
            $conn->exec("UPDATE ZN_EventData
                         SET ЗаказНарядФактическаяДатаВыдачи = NULL,
                             ЗаказНарядФактическоеВремяВыдачи = NULL,
                             ЗаказНарядСтатус = 'В работе'
                         WHERE ЗаказНарядФактическаяДатаВыдачи = '1900-01-01'");
            echo "Данные обновлены";
        } catch (PDOException $e) {
            echo "Ошибка обновления данных: " . $e->getMessage();
        }
    }
}

$conn = null;

echo "Все данные успешно обработаны.\n";
?>
