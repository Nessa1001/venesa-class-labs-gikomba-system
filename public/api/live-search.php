<?php

declare(strict_types=1);

require_once __DIR__ . '/../../app/bootstrap.php';

use App\models\ProductModel;

header('Content-Type: application/json; charset=UTF-8');

$query = trim($_GET['q'] ?? '');
if ($query === '' || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    $model = new ProductModel();
    echo json_encode($model->search($query, 8));
} catch (Throwable $throwable) {
    http_response_code(500);
    echo json_encode([]);
}
