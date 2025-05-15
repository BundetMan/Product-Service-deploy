<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/config.php';


$app = AppFactory::create();

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

// CORS middleware
$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    $origin = $request->getHeaderLine('Origin');
    $allowedOrigins = [
        'http://localhost:5173',
        'https://product-management-deploy-git-main-bundets-projects.vercel.app',
        'https://product-management-deploy.vercel.app',
    ];

    if (in_array($origin, $allowedOrigins)) {
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $origin)
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Credentials', 'true');
    }

    return $response;
});


// Define a test route
$app->get('/', function ($request, $response, $args) {
    $response->getBody()->write("Hello, Product-service!");
    return $response;
});


//get all products
$app->get('/products', function ($request, $response, $args) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM products WHERE status = true ORDER BY id DESC");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$products) {
        $response->getBody()->write(json_encode(['message' => 'No products found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode($products));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//get product by id
$app->get('/product/{id}', function ($request, $response, $args) {
    $id = $args['id'];
    $db = getDBConnection();
    $stm = $db->prepare("SELECT * FROM products WHERE id = :id AND status = true");
    $stm->execute(['id' => $id]);
    $product = $stm->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        $response->getBody()->write(json_encode(['message' => 'Product not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode($product));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//create product
$app->post('/product/create', function ($request, $response, $args){
    $data = json_decode($request->getBody()->getContents(), true);
    $db = getDBConnection();
    $stmt = $db->prepare("INSERT INTO products (name, description, price, category, stock) VALUES (:name, :description, :price, :category, :stock)");
    $stmt->execute([
        'name' => $data['name'],
        'description' => $data['description'],
        'price' => $data['price'],
        'category' => $data['category'],
        'stock' => $data['stock']
    ]);
    if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => 'Failed to create product']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode(['message' => 'Product created successfully']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
});

//update product
$app->put('/product/update/{id}', function ($request, $response, $args){
    $id = $args['id'];
    // $data = $request->getParsedBody();
    $data = json_decode($request->getBody()->getContents(), true);
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE products SET name = :name, description = :description, price = :price, category = :category, stock = :stock WHERE id = :id");
    $stmt->execute([
        'name' => $data['name'],
        'description' => $data['description'],
        'price' => $data['price'],
        'category' => $data['category'],
        'stock' => $data['stock'],
        'id' => $id
    ]);
    if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => 'Product not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode(['message' => 'Product updated successfully']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

//delete product
$app->delete('/product/delete/{id}', function ($request, $response, $args){
    $id = $args['id'];
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE products SET status = false WHERE id = :id");
    $stmt->execute(['id' => $id]);
    if ($stmt->rowCount() === 0) {
        $response->getBody()->write(json_encode(['message' => 'Product not found']));
        return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
    }
    $response->getBody()->write(json_encode(['message' => 'Product deleted successfully']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});
$app->run();
    