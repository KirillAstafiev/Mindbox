<?php

function checkFullname($fullname){
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

        $values = explode(' ', $fullname);

        foreach ($values as $value) {
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':stringToCompare', $value, PDO::PARAM_STR);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['IsMatch']) {
                return mb_convert_case(trim($value), MB_CASE_TITLE, 'UTF-8');;
            }
        }

    } catch (PDOException $e) {
        echo "Ошибка обработки имени: " . $e->getMessage();
    }

    return null;
}