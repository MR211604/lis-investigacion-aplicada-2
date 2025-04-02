<?php

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\EventLoop\Loop;
use React\MySQL\Factory;

require 'vendor/autoload.php';
require './controllers/products.controller.php';

$loop = Loop::get();
$factory = new Factory($loop);
$connection = $factory->createLazyConnection('root:root@localhost/lis_investigacion_aplicada_2');
$browser = new React\Http\Browser();

$productController = new ProductController($connection);

$r = new RouteCollector(new Std(), new GroupCountBased());

$r->addRoute('GET', '/', function () {
  $htmlContent = file_get_contents(__DIR__ . '/client/index.html');
  return new Response(
    200,
    [
      'Content-Type' => 'text/html'
    ],
    $htmlContent
  );
});

$r->addRoute('GET', '/contact', function () {
  $htmlContent = file_get_contents(__DIR__ . '/client/contact.html');
  return new Response(
    200,
    [
      'Content-Type' => 'text/html'
    ],
    $htmlContent
  );
});

$r->addRoute('GET', '/data', function () {
  $htmlContent = file_get_contents(__DIR__ . '/client/data.html');
  return new Response(
    200,
    [
      'Content-Type' => 'text/html'
    ],
    $htmlContent
  );
});

//API para controlar la informacion de /data
$r->addRoute('POST', '/data/add', function (ServerRequestInterface $request) use ($productController) {
  $data = $request->getParsedBody();
  $productController->createProduct($data);   
});

$r->addRoute('POST', '/data/update/{:id\d+}', function (ServerRequestInterface $request, string $id) use ($productController) {
  $data = $request->getParsedBody();
  $productController->editProductById($id, $data);
});

$r->addRoute('POST', '/data/delete/{:id\d+}', function (ServerRequestInterface $request, string $id) use ($productController) {
  $productController->deleteProductById($id);
});

$r->addRoute('GET', '/styles.css', function (ServerRequestInterface $request) {
  $query = $request->getUri()->getQuery();
  parse_str($query, $queryParams);

  if (isset($queryParams['images']) && $queryParams['images'] === 'true') {
    // Manejo de imágenes aleatorias
    $imagesDir = __DIR__ . '/images';

    if (!is_dir($imagesDir)) {
      return new Response(404, ['Content-Type' => 'text/plain'], 'Images directory not found');
    }

    $images = glob($imagesDir . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

    if (empty($images)) {
      return new Response(404, ['Content-Type' => 'text/plain'], 'No images found');
    }

    $randomImage = $images[array_rand($images)];
    $imageContent = file_get_contents($randomImage);

    $extension = pathinfo($randomImage, PATHINFO_EXTENSION);
    $contentType = 'image/jpeg';

    switch (strtolower($extension)) {
      case 'png':
        $contentType = 'image/png';
        break;
      case 'gif':
        $contentType = 'image/gif';
        break;
      case 'jpg':
      case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    }

    return new Response(
      200,
      [
        'Content-Type' => $contentType
      ],
      $imageContent
    );
  } else {

    $filePath = __DIR__ . '/client/styles.css';

    if (!file_exists($filePath)) {
      return new Response(404, ['Content-Type' => 'text/plain'], 'CSS File Not Found');
    }

    $fileContent = file_get_contents($filePath);
    return new Response(
      200,
      [
        'Content-Type' => 'text/css'
      ],
      $fileContent
    );
  }
});

$http = new HttpServer(new Router($r));

$socket = new React\Socket\SocketServer('127.0.0.1:8080');

$http->listen($socket);

echo "Server running at http://127.0.0.1:8080" . PHP_EOL;
