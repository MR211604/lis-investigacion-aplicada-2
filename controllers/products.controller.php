<?php

use React\Http\Message\Response;

class ProductController {

  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  public function getProducts() {
    $this->conn->query('SELECT * FROM products')->then(function ($result) {
      return new Response(200, ['Content-Type' => 'application/json'], json_encode($result->resultRows));
    }, function (Exception $e) {
      return new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => $e->getMessage()]));
    });
  }

  public function createProduct(array $data) {
    
    $this->conn->query('INSERT INTO products (name, price, stock) VALUES (?, ?, ?)', [$data['name'], $data['price'], $data['stock']])->then(function ($result) {
      return new Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Producto agregado correctamente']));
    }, function (Exception $e) {
      return new Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => $e->getMessage()]));
    });

  }

  public function editProductById($id, $data) {
    $this->conn->query('UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?', [$data['name'], $data['price'], $data['stock'], $id])
    ->then(function () {
      return new React\Http\Message\Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Producto actualizado correctamente']));
    }, function (Exception $error) {
      return new React\Http\Message\Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => 'Error al actualizar el producto: ' . $error->getMessage()]));
    });
  }

  public function deleteProductById($id) {
    $this->conn->query('DELETE FROM products WHERE id = ?', [$id])
    ->then(function () {
      return new React\Http\Message\Response(200, ['Content-Type' => 'application/json'], json_encode(['message' => 'Producto eliminado correctamente']));
    }, function (Exception $error) {
      return new React\Http\Message\Response(500, ['Content-Type' => 'application/json'], json_encode(['error' => 'Error al eliminar el producto: ' . $error->getMessage()]));
    });
  }

}
