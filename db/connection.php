<?php

use React\MySQL\Factory;

$factory = new Factory();

$connection = $factory->createLazyConnection('root:root@localhost/lis_investigacion_aplicada_2');
$connection->on(
  'error',
  function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
  }
);

return $connection;
