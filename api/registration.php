<?php

// Регистрация пользователя

include "config/settings.php";
include "config/headersJSON.php";
require "queries/checkFullname.php";
require "config/initCurl.php";
require "api/getClientByPhoneNumber.php";

function registration($phoneNumber, $email, $fullName) {
    global $headers;
    global $endpointId;

    $userExists = getClientByPhoneNumber($phoneNumber);

    $url = "https://api.mindbox.ru/v3/operations/sync?endpointId=$endpointId&operation=Registration";

    $data = [
        "customer" => [
            "mobilePhone" => $phoneNumber,
            "fullName" => $fullName,
            "email" => $email,
            "customFields" => [
                "nameForAds" => checkFullname($fullName)
            ] 
        ],
    ];

    try {
        $ch = initCurl($headers, $url, $data);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception("Ошибка запроса: " . curl_error($ch));
        }

        curl_close($ch);

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Ошибка декодирования JSON ответа");
            return false;
        }

        if ($responseData['status'] == 'ValidationError') {
            $errorMessage = $responseData['validationMessages'][0]['message'] ?? 'Неизвестная ошибка';
            echo "Ошибка регистрации $fullName ($phoneNumber): $errorMessage\n";
            return false;
        } elseif ($responseData['status'] != 'Success') {
            echo "Неожиданный ответ при регистрации: " . json_encode($responseData) . "\n";
            return false;
        }

        echo "$fullName ($phoneNumber) " . ($userExists ? "обновлен\n" : "зарегистрирован\n");
        return true;
    } catch (Exception $e) {
        echo "Ошибка при регистрации $fullName ($phoneNumber): " . $e->getMessage() . "\n";
        return false;
    }
}