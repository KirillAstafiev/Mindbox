<?php

$apiUrl = "https://api.mindbox.ru/v3/operations/bulk";
$endpointId = "yourSiteId";
$operation = "DirectCrm.Customers.Import";
$secretKey = "YOUR_SECRET_KEY";

$filePath = "..\clients_data.csv"; 

if (!file_exists($filePath)) {
    die("Файл не найден: $filePath");
}

$url = "$apiUrl?endpointId=$endpointId&operation=$operation&csvCodePage=65001&csvColumnDelimiter=%3B&csvTextQualifier=%22";

$ch = curl_init($url);

$headers = [
    "Authorization: SecretKey $secretKey",
    "Accept: application/json",
    "Content-Type: text/csv;charset=utf-8",
];

$fileContent = file_get_contents($filePath);

curl_setopt($ch, CURLOPT_CAINFO, 'C:/Certificates/cacert.pem');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Ошибка cURL: " . curl_error($ch);
}

echo $response;

curl_close($ch);
