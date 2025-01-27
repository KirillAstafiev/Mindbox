<?php

include "config/settings.php";

function getCarsByVIN($vins)
{
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];

    $username = 'odata';
    $password = 'Ghtwejhrjk4';

    $urls = [
        "Архангельск" => "http://192.168.4.11/alfa5/hs/marketing/cars",
        "Калининград" => "http://192.168.8.53/alfa/hs/marketing/cars",
        "Череповец" => "http://192.168.10.5/mitsu/hs/marketing/cars",
        "Сыктывкар" => "http://192.168.84.54/alpha5/hs/marketing/cars",
        "Вологда" => "http://192.168.4.11/alfa5_vologda/hs/marketing/cars",
        "Смоленск" => "http://192.168.101.2/aa5_smolensk/hs/marketing/cars",
    ];

    $result = [];

    foreach ($urls as $city => $url) {
        try {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_CAINFO, 'C:/Certificates/cacert.pem');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vins));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

            $responseRaw = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception("Ошибка запроса: " . curl_error($ch));
            }

            if (!$responseRaw) {
                throw new Exception("Сервер {$city} вернул пустой ответ.");
            }

            $response = json_decode($responseRaw, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Некорректный JSON от сервера {$city}: " . json_last_error_msg());
            }

            if (!empty($response)) {
                foreach ($response as $car) {
                    $result[] = $car;
                }
            }

            curl_close($ch);
        } catch (Exception $e) {
            echo "Ошибка для города {$city}: " . $e->getMessage() . "\n";
        }
    }

    return $result;
}
