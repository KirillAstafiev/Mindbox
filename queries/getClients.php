<?php

function getClients(){
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "
            WITH RankedPhones AS (
                SELECT 
                    [ДатаСобытия],
                    [ВремяСобытия],
                    [ВидСобытия],
                    [РабочийЛист],
                    [РабочийЛистСтатус],
                    [Телефон],
                    [Организация],
                    [Клиент],
                    [ЭлПочты],
                    [МаркаАвто],
                    [МодельАвто],
                    [АвтомобильVIN]
                FROM [Mindbox].[dbo].[EventData]
                WHERE [Телефон] IS NOT NULL 
                AND [Телефон] <> ''
                AND NOT (
                    [Телефон] LIKE '7%' OR 
                    [Телефон] LIKE '+7%' OR 
                    [Телефон] LIKE '8%' OR 
                    [Телефон] LIKE '+8%'
                )
                AND LEN(REPLACE(REPLACE(REPLACE(REPLACE([Телефон], '+', ''), '(', ''), ')', ''), '-', '')) = 10
            )
            SELECT
                rp.[ДатаСобытия],
                rp.[ВремяСобытия],
                rp.[ВидСобытия],
                rp.[РабочийЛист],
                rp.[РабочийЛистСтатус],
                '7' + REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(rp.[Телефон], '+', ''), '(', ''), ')', ''), '-', ''), ' ', '') AS [Телефон],
                rp.[Организация],
                rp.[Клиент],
                rp.[ЭлПочты],
                rp.[МаркаАвто],
                rp.[МодельАвто],
                rp.[АвтомобильVIN],
                c.[Id] AS ModelId
            FROM RankedPhones rp
            LEFT JOIN [Mindbox].[dbo].[Cars] c
                ON CONCAT(rp.[МаркаАвто], ' ', rp.[МодельАвто]) COLLATE Cyrillic_General_CI_AS = CONCAT(c.[Brand], ' ', c.[Model]) COLLATE Cyrillic_General_CI_AS
            WHERE rp.[Клиент] NOT LIKE '%[^а-яА-Я0-9 ]%'
            ORDER BY rp.[Телефон], rp.[ДатаСобытия] DESC";

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