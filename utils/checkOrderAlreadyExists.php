<?php

function checkOrderAlreadyExists($externalOrderId, $allClientOrders){
    if(isset($allClientOrders['orders'])) {
        foreach ($allClientOrders['orders'] as $order) {
            if (isset($order['ids']['externalOrderId']) && $order['ids']['externalOrderId'] === $externalOrderId) {
                return $order['ids']['mindboxId'] ?? null;
            }
        }
    }

    return null;
}