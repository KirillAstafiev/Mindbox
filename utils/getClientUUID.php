<?php

function getClientUUID($data){
    return $data['customer']['customFields']['clientUUID'];
}