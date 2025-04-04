<?php

use React\Http\Message\Response;

final class JSONResponse {

  public static function response($statusCode, $data = null) {
    
    $body = $data ? json_encode($data) : null;

    $headers = [
      'Content-Type' => 'application/json',
    ];
    
    return new Response(
      $statusCode,
      $headers,
      $body
    );
  }

}