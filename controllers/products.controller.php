<?php

use Psr\Http\Message\ServerRequestInterface;
require './utils/json-response.php';

class ProductController
{

  private $conn;

  public function __construct($conn)
  {
    $this->conn = $conn;
  }

  public function getProducts()
  {
    return $this->conn->query('SELECT * FROM products')->then(function ($result) {
      return JSONResponse::response(200, ['ok' => true, 'products' => $result->resultRows]);
    }, function (Exception $e) {
      return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al obtener los productos: ' . $e->getMessage()]);
    });
  }

  public function getProductById($id)
  {
    if (empty($id)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'ID de producto no proporcionado']);
    }

    return $this->conn->query('SELECT * FROM products WHERE id = ?', [$id])->then(function ($result) use ($id) {
      if (count($result->resultRows) === 0) {
        return JSONResponse::response(404, ['ok' => false, 'error' => 'Producto con el id ' . $id . ' no encontrado']);
      }
      return JSONResponse::response(200, ['ok' => true, 'product' => $result->resultRows[0]]);
    }, function (Exception $e) {
      return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al obtener el producto: ' . $e->getMessage()]);
    });
  }

  public function createProduct(ServerRequestInterface $request)
  {

    $body = $request->getBody()->getContents();
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      JSONResponse::response(400, ['ok' => false, 'error' => 'Error al decodificar el JSON']);
    }

    $name = $data['name'];
    $price = $data['price'];
    $stock = $data['stock'];

    if (empty($name) || empty($price) || empty($stock)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'Todos los campos son obligatorios']);
    }

    if (strlen($name) < 3) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'El nombre del producto debe tener al menos 3 caracteres']);
    }

    if (!is_numeric($price) || !is_numeric($stock)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'El precio y el stock deben ser números']);
    }

    return $this->conn->query('INSERT INTO products (name, price, stock) VALUES (?, ?, ?)', [$name, $price, $stock])->then(function ($result) use ($name, $price, $stock) {
      $product = [
        'id' => $result->insertId,
        'name' => $name,
        'price' => $price,
        'stock' => $stock
      ];
      return JSONResponse::response(200, ['ok' => true, 'message' => 'Producto agregado correctamente', 'product' => $product]);
    }, function (Exception $e) {
      return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al agregar el producto: ' . $e->getMessage()]);
    });
  }

  public function editProductById(ServerRequestInterface $request, $id)
  {

    $body = $request->getBody()->getContents();
    $data = json_decode($body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'Error al decodificar el JSON']);
    }

    $name = $data['name'];
    $price = $data['price'];
    $stock = $data['stock'];

    if (empty($id)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'ID de producto no proporcionado']);
    }

    if (empty($name) || empty($price) || empty($stock)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'Todos los campos son obligatorios']);
    }

    if (strlen($name) < 3) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'El nombre del producto debe tener al menos 3 caracteres']);
    }

    if (!is_numeric($price) || !is_numeric($stock)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'El precio y el stock deben ser números']);
    }


    return $this->conn->query('SELECT id FROM products WHERE id = ?', [$id])->then(function ($result) use ($id, $name, $price, $stock) {
      if (count($result->resultRows) === 0) {
        return JSONResponse::response(404, ['ok' => false, 'error' => 'Producto con el id ' . $id . ' no encontrado']);
      }
      return $this->conn->query('UPDATE products SET name = ?, price = ?, stock = ? WHERE id = ?', [$name, $price, $stock, $id])->then(function () use ($id, $name, $price, $stock) {
        return JSONResponse::response(200, ['ok' => true, 'message' => 'Producto actualizado correctamente', 'product' => ['id' => $id, 'name' => $name, 'price' => $price, 'stock' => $stock]]);
      }, function (Exception $error) {
        return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al actualizar el producto: ' . $error->getMessage()]);
      });
    }, function (Exception $error) {
      return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al buscar el producto: ' . $error->getMessage()]);
    });
  }

  public function deleteProductById($id)
  {

    if (empty($id)) {
      return JSONResponse::response(400, ['ok' => false, 'error' => 'ID de producto no proporcionado']);
    }

    return $this->conn->query('SELECT id FROM products WHERE id = ?', [$id])->then(function ($result) use ($id) {
      if (count($result->resultRows) === 0) {
        return JSONResponse::response(404, ['ok' => false, 'error' => 'Producto con el id ' . $id . ' no encontrado']);
      }
      return $this->conn->query('DELETE FROM products WHERE id = ?', [$id])->then(function () {
        return JSONResponse::response(200, ['ok' => true, 'message' => 'Producto eliminado correctamente']);
      }, function (Exception $error) {
        return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al eliminar el producto: ' . $error->getMessage()]);
      });
    }, function (Exception $error) {
      return JSONResponse::response(500, ['ok' => false, 'error' => 'Error al buscar el producto: ' . $error->getMessage()]);
    });
  }
}
