<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
try {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

} catch (\Dotenv\Exception\InvalidPathException $e) {
    error_log("Warning: .env file not found. Using system environment variables.");
}

$appEnv = $_ENV['APP_ENV'] ?? 'production';

// Set error reporting based on environment
if ($appEnv === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

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
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
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

// recieive email and send to discord
$app->post('/contact/send', function ($request, $response, $args) {
    $body = $request->getBody()->getContents();
    $params = json_decode($body, true);

    $name = htmlspecialchars($params['name'] ?? 'N/A');
    $email = htmlspecialchars($params['email'] ?? 'N/A');
    $message = htmlspecialchars($params['message'] ?? 'N/A');

    $discordWebhook = $_ENV['WEBHOOK_URL']; 

    $data = [
        'username' => 'Contact Bot',
        'embeds' => [[
            'title' => '📩 New Contact Message',
            'color' => 7506394,
            'fields' => [
                ['name' => '👤 Name', 'value' => $name, 'inline' => false],
                ['name' => '📧 Email', 'value' => $email, 'inline' => false],
                ['name' => '💬 Message', 'value' => $message, 'inline' => false],
            ],
            'timestamp' => date('c'),
        ]]
    ];

    $ch = curl_init($discordWebhook);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Failed to notify Discord']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});

//recieve feedback and send to discord
$app->post('/feedback/send', function ($request, $response, $args) {
    $body = $request->getBody()->getContents();
    $params = json_decode($body, true);

    $name = htmlspecialchars($params['name'] ?? 'N/A');
    $email = htmlspecialchars($params['email'] ?? 'N/A');
    $message = htmlspecialchars($params['message'] ?? 'N/A');

    $webhook = $_ENV['WEBHOOK_URL'];

    $data = [
        'username' => 'Feedback Bot',
        'embeds' => [[
            'title' => '📝 New Feedback Received',
            'color' => 3447003,
            'fields' => [
                ['name' => '👤 Name', 'value' => $name, 'inline' => false],
                ['name' => '📧 Email', 'value' => $email, 'inline' => false],
                ['name' => '💬 Message', 'value' => $message, 'inline' => false],
            ],
            'timestamp' => date('c'),
        ]]
    ];

    $ch = curl_init($webhook);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status >= 200 && $status < 300) {
        $response->getBody()->write(json_encode(['status' => 'success']));
        return $response->withHeader('Content-Type', 'application/json');
    } else {
        $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Failed to send']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
});


$app->run();
    