<?php
// Функция для форматирования даты и времени в нужный формат
function formatDateTime($datetime) {
    return $datetime->format('YmdHis');
}

function formatDate($date) {
    return DateTime::createFromFormat('d.m.Y', $date)->format('Y-m-d');
}

function formatTime($time) {
    return DateTime::createFromFormat('H:i:s', $time)->format('H:i:s.0000000');
}

// Функция для декодирования неверно закодированных строк
function decodeIncorrectEncoding($string) {
    $converted = iconv('Windows-1251', 'UTF-8//IGNORE', $string);
    if ($converted === false || $converted === $string) {
        $converted = iconv('CP1252', 'UTF-8//IGNORE', $string);
    }
    return $converted;
}

// Функция для преобразования строки в верхний регистр
function toUpperCase($string) {
    return mb_strtoupper($string, 'UTF-8');
}

function formatDateTime2(DateTime $dateTime) {
    return $dateTime->format('Y-m-d H:i:s'); // Измените формат в зависимости от нужд
}
?>