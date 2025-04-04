<?php

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With');

require_once 'controllers/UtilisateurController.php';
require_once 'controllers/UtilisateurSystemeController.php';
require_once 'controllers/TransfertController.php';
require_once 'controllers/VehiculeController.php';



$request_method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

$base_path = '/TransAirEvent/api/index.php'; 
$endpoint = str_replace($base_path, '', $request_uri);

$endpoint = strtok($endpoint, '?');


if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

switch (true) {
    case ($endpoint === '/utilisateurs'):
        case preg_match('/^\/utilisateurs\/\d+$/', $endpoint):
            $controller = new UtilisateurController($request_method, $endpoint);
            break;
            
        case ($endpoint === '/utilisateurs_systeme'):
        case preg_match('/^\/utilisateurs_systeme\/\d+$/', $endpoint):
        case ($endpoint === '/utilisateurs_systeme/login'):
            $controller = new UtilisateurSystemeController($request_method, $endpoint);
            break;
        
    case str_starts_with($endpoint, '/transferts'):

        $controller = new TransfertController($request_method, $endpoint);
        break;
        
    case str_starts_with($endpoint, '/vehicules'):

       $controller = new VehiculeController($request_method, $endpoint);
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint non trouve ee']);
        exit();
}

$response = $controller->processRequest();
http_response_code($response['status']);
echo json_encode($response['data'] ?? $response['message']);