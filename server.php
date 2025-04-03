<?php

use FastRoute\DataGenerator\GroupCountBased;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;

require 'vendor/autoload.php';
require './controllers/products.controller.php';
require './db/connection.php';
require './router.php';

$browser = new React\Http\Browser();
$productController = new ProductController($connection);
$r = new RouteCollector(new Std(), new GroupCountBased());


//Routes
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
  $htmlContent = file_get_contents(__DIR__ . '/client/products/data.html');
  return new Response(
    200,
    [
      'Content-Type' => 'text/html',
      'Access-Control-Allow-Origin' => '*',
    ],
    $htmlContent
  );
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

//CRUD para controlar la informacion de /data
$r->addRoute('GET', '/products', function () use ($productController) {
  return $productController->getProducts();
});

$r->addRoute('POST', '/products/create', function (ServerRequestInterface $request) use ($productController) {
  return $productController->createProduct($request);
});

$r->addRoute('POST', '/products/update/{id:\d+}', function (ServerRequestInterface $request, $id) use ($productController) {
  return $productController->editProductById($request, $id);
});

$r->addRoute('POST', '/products/delete/{id:\d+}', function (ServerRequestInterface $request, $id) use ($productController) {
  return $productController->deleteProductById($id);
});


$http = new HttpServer(new Router($r));
$socket = new React\Socket\SocketServer('127.0.0.1:8080');
$http->listen($socket);

$http->on(
  'error',
  function (Exception $error) {
    echo 'Error: ' . $error->getMessage() . PHP_EOL;
  }
);

echo "Server running at http://127.0.0.1:8080" . PHP_EOL;
