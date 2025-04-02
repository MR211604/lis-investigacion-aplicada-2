<?php
use React\EventLoop\Loop;
use React\MySQL\Factory;

$loop = Loop::get();
$factory = new Factory($loop);

$connection = $factory->createLazyConnection('root:root@localhost/lis_investigacion_aplicada_2');

return $connection;