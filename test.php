<?php

require_once 'vendor/autoload.php';

use Dark\Dummy\Gantt\Gantt;

$dates = array(
    array('start' => '2018-10-08', 'end' => '2021-10-24', 'label' => 'Periodo de Trabajo de graduaciòn', 'costt' => '500.00'),
    array('start' => '2018-10-09', 'end' => '2018-12-25', 'label' => 'Ante-Proyecto', 'costt' => '3500.00'),
    array('start' => '2018-12-25', 'end' => '2019-06-12', 'label' => 'Desarrollo', 'costt' => '1500.00'),
    array('start' => '2019-06-12', 'end' => '2019-10-25', 'label' => 'Implementación', 'costt' => '3500.00')
);



$gantt = new Gantt($dates);

echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <title></title>
    <link rel='stylesheet' href='./src/assets/scss/darkgantt.css'>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js'></script>
</head>
<body>";
echo $gantt;
echo "</body>
</html>";
