<?php

function getUniqueVins() {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "SELECT DISTINCT АвтомобильVIN
                FROM (
                    SELECT АвтомобильVIN FROM EventData WHERE АвтомобильVIN IS NOT NULL AND TRIM(АвтомобильVIN) != ''
                    UNION
                    SELECT АвтомобильVIN FROM ZN_EventData WHERE АвтомобильVIN IS NOT NULL AND TRIM(АвтомобильVIN) != ''
                ) AS CombinedData";

        $stmt = $pdo->prepare($sql);
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