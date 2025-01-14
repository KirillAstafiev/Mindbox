<?php

function getUniqueClients() {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            WITH LatestEventData AS (
                SELECT 
                    Телефон COLLATE Latin1_General_100_CI_AS_SC_UTF8 AS Телефон,
                    Клиент COLLATE Latin1_General_100_CI_AS_SC_UTF8 AS ФИО,
                    ROW_NUMBER() OVER (PARTITION BY Телефон ORDER BY ДатаСобытия DESC, ВремяСобытия DESC) AS RowNum
                FROM EventData
                WHERE LEN(Телефон) = 10 AND Телефон LIKE '%[0-9]%' AND Телефон NOT LIKE '%[^0-9]%'
            ),
            DistinctEventData AS (
                SELECT 
                    Телефон,
                    ФИО
                FROM LatestEventData
                WHERE RowNum = 1
            ),
            ZNData AS (
                SELECT 
                    ЗаказНарядЗаказчикТелефон COLLATE Latin1_General_100_CI_AS_SC_UTF8 AS Телефон,
                    ЗаказНарядЗаказчик COLLATE Latin1_General_100_CI_AS_SC_UTF8 AS ЗаказНарядЗаказчик
                FROM ZN_EventData
                WHERE LEN(ЗаказНарядЗаказчикТелефон) = 10 AND ЗаказНарядЗаказчикТелефон LIKE '%[0-9]%' AND ЗаказНарядЗаказчикТелефон NOT LIKE '%[^0-9]%'
            )
            SELECT 
                '7' + COALESCE(E.Телефон, ZN.Телефон) AS Телефон,
                CASE 
                    WHEN ZN.Телефон IS NOT NULL 
                        THEN ZN.ЗаказНарядЗаказчик
                    ELSE E.ФИО
                END AS ФИО
            FROM DistinctEventData AS E
            FULL OUTER JOIN ZNData AS ZN
            ON E.Телефон = ZN.Телефон
            GROUP BY 
                COALESCE(E.Телефон, ZN.Телефон),
                CASE 
                    WHEN ZN.Телефон IS NOT NULL 
                        THEN ZN.ЗаказНарядЗаказчик
                    ELSE E.ФИО
                END;";

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