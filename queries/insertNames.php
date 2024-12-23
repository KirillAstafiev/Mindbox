<?php

function insertNames() {
    $dsn = "sqlsrv:Server=SRVMARKETOLOG;Database=Mindbox";
    $username = "sa";
    $password = "123aA123";

    try {
        $pdo = new PDO($dsn, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
        $filePath = "../russian_names.txt";

        if (!file_exists($filePath)) {
            throw new Exception("Файл $filePath не найден.");
        }

        $names = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $sql = "INSERT INTO Names (Value) VALUES (:name)";
        $stmt = $pdo->prepare($sql);

        foreach ($names as $name) {
            $name = mb_convert_case(trim($name), MB_CASE_TITLE, 'UTF-8');
            try {
                $stmt->execute([':name' => $name]);
                echo "Имя добавлено: $name" . PHP_EOL;
            } catch (PDOException $e) {
                echo "Ошибка при добавлении имени '$name': " . $e->getMessage() . PHP_EOL;
            }
        }

        echo "Имена добавлены в базу данных.";
    } catch (PDOException $e) {
        echo "Ошибка подключения: " . $e->getMessage();
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
