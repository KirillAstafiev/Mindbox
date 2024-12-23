<?php

function getOrders() {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $sql = "
        SELECT
            [ДатаНачала],
            [РабочийЛистУИД],
            [ВидСобытия],
            [Состояние],
            [Менеджер],
            [НомерТелефона]
        FROM [Mindbox].[dbo].[AllEventData]
        WHERE [НомерТелефона] IS NOT NULL 
            AND [НомерТелефона] <> ''
        ORDER BY [ДатаНачала] DESC; 
        ";
        
        $stmt = $pdo->query($sql);
    
        $data = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data[] = $row;
        }
    
        return $data;
    } catch (PDOException $e) {
        echo "Ошибка подключения: " . $e->getMessage();
    }
}