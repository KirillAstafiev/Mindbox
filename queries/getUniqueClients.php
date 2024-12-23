<?php


function getUniqueClients() {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql = "
            WITH RankedPhones AS (
                SELECT 
                    Телефон COLLATE Cyrillic_General_CI_AS AS Телефон,
                    Клиент COLLATE Cyrillic_General_CI_AS AS ФИО,
                    ЭлПочты COLLATE Cyrillic_General_CI_AS AS ЭлПочта,
                    ROW_NUMBER() OVER (PARTITION BY Телефон ORDER BY Клиент COLLATE Cyrillic_General_CI_AS ASC) AS RowNum
                FROM (
                    SELECT 
                        Телефон COLLATE Cyrillic_General_CI_AS AS Телефон,
                        Клиент COLLATE Cyrillic_General_CI_AS AS Клиент,
                        ЭлПочты COLLATE Cyrillic_General_CI_AS AS ЭлПочты
                    FROM EventData
                    WHERE Телефон IS NOT NULL 
                    AND Телефон <> ''
                    AND NOT (
                        Телефон LIKE '7%' OR 
                        Телефон LIKE '+7%' OR 
                        Телефон LIKE '8%' OR 
                        Телефон LIKE '+8%'
                    )
                    AND LEN(REPLACE(REPLACE(REPLACE(REPLACE(Телефон, '+', ''), '(', ''), ')', ''), '-', '')) = 10
                    AND Клиент NOT LIKE '%[^а-яА-Я ]%'
                    
                    UNION ALL
                    
                    SELECT 
                        ЗаказНарядЗаказчикТелефон COLLATE Cyrillic_General_CI_AS AS Телефон,
                        ЗаказНарядЗаказчик COLLATE Cyrillic_General_CI_AS AS Клиент,
                        ЗаказНарядЗаказчикЭлПочта COLLATE Cyrillic_General_CI_AS AS ЭлПочты
                    FROM ZN_EventData
                    WHERE ЗаказНарядЗаказчикТелефон IS NOT NULL 
                    AND ЗаказНарядЗаказчикТелефон <> ''
                    AND NOT (
                        ЗаказНарядЗаказчикТелефон LIKE '7%' OR 
                        ЗаказНарядЗаказчикТелефон LIKE '+7%' OR 
                        ЗаказНарядЗаказчикТелефон LIKE '8%' OR 
                        ЗаказНарядЗаказчикТелефон LIKE '+8%'
                    )
                    AND LEN(REPLACE(REPLACE(REPLACE(REPLACE(ЗаказНарядЗаказчикТелефон, '+', ''), '(', ''), ')', ''), '-', '')) = 10
                    AND ЗаказНарядЗаказчик NOT LIKE '%[^а-яА-Я ]%'
                ) AS CombinedData
            )
            SELECT
                CONCAT('7', Телефон) AS Телефон,
                ФИО,
                ЭлПочта
            FROM RankedPhones
            WHERE RowNum = 1";

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