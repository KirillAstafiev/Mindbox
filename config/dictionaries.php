<?php

$orderLineStatuses = [
    "SMS" => "statusSMS",
    "Анкетирование" => "statusAnketa",
    "Аренда автомобиля" => "statusRent",
    "Встреча трейд ин" => "statusMeetingTradeIn",
    "Выдача автомобиля" => "statusAutoOut",
    "Заказ на автомобиль" => "statusAutoOrder",
    "Замена автомобиля" => "statusAutoSwitch",
    "Интернет-запрос" => "statusBrowser",
    "Кредит одобрен" => "statusCreditAccepted",
    "Личная встреча" => "statusMeeting",
    "Мессенджер" => "statusMessenger",
    "Отказ от автомобиля" => "statusAutoCanceled",
    "Оценка Trade-in окончательная" => "statusTradeInCheckingCompleted",
    "Оценка трейд ин предварительная" => "statusTradeInCheckingPrev",
    "Первичный контакт - кредитование" => "statusFirstContactCredit",
    "Первичный контакт - страхование" => "statusFirstContactStrah",
    "Почтовое письмо" => "statusMail",
    "Прочее" => "statusOther",
    "Телефонный звонок" => "statusPhoneCall",
    "Тест драйв (факт)" => "statusTestDriveFact",
    "Электронное письмо" => "statusEmail",
];

$clientStatusesInOrder = [
    "1" => "В работе",
    "2" => "Заказ на автомобиль создан",
    "3" => "Отказ от покупки",
    "4" => "Реализация",
    "5" => "Закрыт"
];