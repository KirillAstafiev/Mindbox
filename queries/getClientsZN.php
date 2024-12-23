<?php

function getClientsZN(){
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "
            SELECT top 1
            *,
            CONCAT('7', [ЗаказНарядЗаказчикТелефон]) AS Телефон
            FROM [Mindbox].[dbo].[ZN_EventData];";

        $stmt = $pdo->query($sql);

        if ($stmt === false) {
            throw new Exception("Ошибка работы запроса: " . implode(", ", $pdo->errorInfo()));
        }
    
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $data;
    } catch (PDOException $e) {
        echo "Ошибка подключения: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Ошибка выполнения: " . $e->getMessage();
    }
}