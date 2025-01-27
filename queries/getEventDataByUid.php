<?php

function getEventDataByUid($worksheetId)
{
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
        SELECT *
        FROM [Mindbox].[dbo].[EventData]
        WHERE [РабочийЛист] = :worksheetId";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':worksheetId', $worksheetId, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Ошибка подключения: " . $e->getMessage();
    }
}
