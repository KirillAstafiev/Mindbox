<?php

function getAllClientCarsZN($phoneNumber) {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT DISTINCT АвтомобильVIN
                FROM ZN_EventData
                WHERE ЗаказНарядЗаказчикТелефон = :phoneNumber";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
        $stmt->execute();

        $vins = [
            "VIN" => []
        ];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $vins['VIN'][] = $row['АвтомобильVIN'];
        }

        return $vins;
    } catch (PDOException $e) {
        echo "Ошибка подключения или выполнения запроса: " . $e->getMessage();
    }
}

