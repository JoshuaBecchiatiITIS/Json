<?php
require  '../models/product.php';

// Definisci un array associativo per mappare le route
$routes = [
    'GET' => [],
    'POST' => [],
    'PUT' => [],
    'DELETE' => []
];

// Funzione per aggiungere una route
function addRoute($method, $path, $callback) {
    global $routes;
    $routes[$method][$path] = $callback;
}

// Funzione per ottenere il metodo della richiesta HTTP
function getRequestMethod() {
    return $_SERVER['REQUEST_METHOD'];
}

// Funzione per ottenere il percorso richiesto
function getRequestPath() {
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    return rtrim($path, '/');
}

// Funzione per gestire la richiesta
function handleRequest() {
    global $routes;

    $method = getRequestMethod();
    $path = getRequestPath();

    // Verifica se esiste una route per il metodo e il percorso richiesti
    if (isset($routes[$method])) {
        foreach ($routes[$method] as $routePath => $callback) {
            // Verifica se il percorso richiesto corrisponde al percorso della route
            if (preg_match('#^' . $routePath . '$#', $path, $matches)) {
                // Chiamata al callback passando l'ID come parametro
                call_user_func_array($callback, $matches);
                return;
            }
        }
    }

    // Ritorna un errore 404 se la route non è stata trovata
    http_response_code(404);
    echo "404 Not Found";
}

// Aggiungi le tue route qui
addRoute('GET', '/customers/(\d+)', function($id) {
    // $id contiene il valore dell'ID
    echo "Gestisci richiesta GET per il cliente con ID: $id";
});

addRoute('GET', '/products/(\d+)', function ($id){
    $pID = explode('/', $id);
    $pdt = Product::Find($pID[2]);

    if($pdt){
          $data = [
              'type' => 'products',
              'id' => $pdt->getId(),
              'attributes' => [
                  'nome' => $pdt->getNome(),
                  'marca' => $pdt->getMarca(),
                  'prezzo' => $pdt->getPrezzo()
              ]
            ];
        $response = ['data' => $data];
        header("Location: /products" . $pID[2]);
        header('HTTP/1.1 200 OK.');
        header('Content-Type: application/vnd.api+json');

        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {

        http_response_code(404);
        echo json_encode(['error' => 'Prodotto non trovato.']);
    }
});

addRoute('GET', '/products', function (){
    $pdts = Product::FetchAll();
    $data = [];

    foreach ($pdts as $pdt)
    {
        $data[] = [
            'type' => 'products',
            'id' => $pdt->getId(),
            'attributes' => [
                'nome' => $pdt->getNome(),
                'marca' => $pdt->getMarca(),
                'prezzo' => $pdt->getPrezzo()
            ]
        ];
    }

    header("Location: /products");
    header('HTTP/1.1 200 OK.');
    header('Content-Type: application/vnd.api+json');
    $response = ['data' => $data];

    echo json_encode($response, JSON_PRETTY_PRINT);
});

addRoute('POST', '/products', function () {

    header("Location: /products");
    header('HTTP/1.1 201 Created.');
    header('Content-Type: application/vnd.api+json');

    try {
        if(isset($_POST['data'])){
            $postData = $_POST;
        }
        else{
            $postData = json_decode(file_get_contents('php://input'), true);
        }

        $newPdt = Product::Create($postData['data']['attributes']);
        $data = [
            'type' => 'products',
            'id' => $newPdt->getId(),
            'attributes' => [
                'nome' => $newPdt->getNome(),
                'marca' => $newPdt->getMarca(),
                'prezzo' => $newPdt->getPrezzo()
            ]
        ];

        $response = ['data' => $data];
        echo json_encode($response, JSON_PRETTY_PRINT);

    } catch (PDOException $e) {
        header("Location: /products");
        header('HTTP/1.1 500 INTERNAL SERVER ERROR.');
        header('Content-Type: application/vnd.api+json');
        http_response_code(500);
        echo json_encode(['error' => 'Errore nella creazione del prodotto.']);
    }
});

addRoute('PATCH', '/products/(\d+)', function ($id) {
    $putData = json_decode(file_get_contents('php://input'), true);
    $pID = explode('/', $id);
    $pdt = Product::Find($pID[2]);

    try {
        $updatedPdt = $pdt->Update($putData['data']['attributes']);

        if (isset($updatedPdt)) {
            $data = [
                'type' => 'products',
                'id' => intval($pdt->getId()),
                'attributes' => [
                    'nome' => $pdt->getNome(),
                    'marca' => $pdt->getMarca(),
                    'prezzo' => $pdt->getPrezzo()
                ]
            ];
            $response = ['data' => $data];
            header("Location: /products" . $pID[2]);
            header('HTTP/1.1 200 OK.');
            header('Content-Type: application/vnd.api+json');
            echo json_encode($response, JSON_PRETTY_PRINT);

        } else {
            header("Location: /products/(\d+)");
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: application/vnd.api+json');
            http_response_code(404);
            echo json_encode(['error' => 'Prodotto non trovato.']);
        }

    } catch (PDOException $e) {
        header("Location: /products/(\d+)");
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/vnd.api+json');
        http_response_code(500);
        echo json_encode(['error' => 'Errore nell\'aggiornamento del prodotto.']);
    }
});

addRoute('DELETE', '/products/(\d+)', function ($id) {
    $newID = str_split($id, 10);
    $product = Product::Find($newID[1]);

    if ($product) {
        if ($product->Delete()) {
            header("Location: /products/(\d+)");
            header('Content-Type: application/vnd.api+json');
            http_response_code(204);
        } else {
            header("Location: /products/(\d+)");
            header('Content-Type: application/vnd.api+json');
            http_response_code(500);
            echo json_encode(['error' => 'Errore durante l\'eliminazione del prodotto']);
        }
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Prodotto non trovato']);
    }
});


// Esegui il gestore della richiesta
handleRequest();