<?php

function formatDateTimeCSV(string $date = null, string $time = null): string
{
    if ($date === null || $time === null) {
        return '';
    }

    // Убираем миллисекунды, если их больше, чем 3 цифры
    $time = preg_replace('/\.(\d{3})\d+$/', '.$1', $time);
    $datetime = DateTime::createFromFormat('Y-m-d H:i:s.u', "$date $time");

    if ($datetime === false) {
        throw new Exception("Неверный формат даты или времени: $date $time");
    }

    return $datetime->format('d.m.Y H:i:s.v');
}

