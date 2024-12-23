<?php

function getLongestFullname($worksheets){
    $key = isset($worksheets[0]['Клиент']) ? 'Клиент' : 'ЗаказНарядЗаказчик';
    $result = $worksheets[0][$key];

    foreach ($worksheets as $worksheet) {
        $result = strlen($worksheet[$key]) > strlen($result) ? $worksheet[$key] : $result;
    }

    return $result;
}