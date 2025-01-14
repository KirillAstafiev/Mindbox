<?php

$dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
$username = "sa";
$password = "123aA123";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "
        SELECT 
            CASE 
                WHEN EXISTS (
                    SELECT 1 
                    FROM Names 
                    WHERE LOWER(Value) = LOWER(:stringToCompare)
                ) THEN CAST(1 AS BIT)
                ELSE CAST(0 AS BIT)
            END AS IsMatch;
    ";
} catch (Exception $e) {
    echo "Ошибка обновления данных: $e";
} catch (PDOException $e) {
    echo "Ошибка БД: $e";
}