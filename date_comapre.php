<?php

$now = '2015-03-12';
$date = '2015-03-12';



if (!(strtotime($now) > strtotime($date))) {
    
    echo "now or past";
} else {
    echo  "future";
}