<?php
include "api/addProduct.php";

function addAllCarModels(){
    $carModels = getCarModels();

    foreach($carModels as $model){
        echo addProduct($model['Model'], $model['Id']);
    }
}
