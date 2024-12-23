<?php

function getAllClientWorksheetsZN($phoneNumber) {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
        SELECT *
        FROM [Mindbox].[dbo].[ZN_EventData]
        WHERE [ЗаказНарядЗаказчикТелефон] = :phoneNumber;
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    } catch (PDOException $e) {
        echo "Ошибка подключения: " . $e->getMessage();
    }
}
