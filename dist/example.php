<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Example showing how to use the oryx/adr library with oryx/mvc

use Oryx\Adr\Action\ActionInterface;
use Oryx\Adr\Domain\DomainInterface;
use Oryx\Adr\Responder\ResponderInterface;
use Oryx\Adr\Middleware\ManifestJsonMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use Laminas\Diactoros\Response\JsonResponse;

// Example Domain - contains business logic
class UserDomain implements DomainInterface {
    public function getUserData(): array {
        // Business logic would go here
        return [
            'id' => 123,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'profile' => [
                'age' => 30,
                'location' => 'New York'
            ]
        ];
    }
}

// Example Responder - prepares HTTP response
class JsonResponder implements ResponderInterface {
    private $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function respond(): ResponseInterface {
        return new JsonResponse($this->data);
    }
}

// Example Action - coordinates between Domain and Responder
class GetUserDataAction implements ActionInterface {
    public function execute(DomainInterface $domain): ResponderInterface {
        $userData = $domain->getUserData();
        return new JsonResponder($userData);
    }
}

// Mock handler for non-manifest requests (simulating oryx/mvc application)
class AppHandler implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface {
        // In a real app, this would route to controllers/actions
        $path = $request->getUri()->getPath();
        
        if ($path === '/api/user') {
            // Simulate calling our ADR action
            $domain = new UserDomain();
            $action = new GetUserDataAction();
            $responder = $action->execute($domain);
            return $responder->respond();
        }
        
        return new Response(
            'Not Found', 
            404, 
            ['Content-Type' => 'text/plain']
        );
    }
}

// Create the middleware
$manifestMiddleware = new ManifestJsonMiddleware();
$appHandler = new AppHandler();

// Test 1: Manifest request
echo "Test 1: Request for /manifest.json\n";
$serverParams = [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI' => '/manifest.json',
    'SCRIPT_NAME' => '',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
];
$request = new ServerRequest(
    $serverParams,
    [],
    'https://example.com/manifest.json',
    'GET',
    new Stream('php://temp', 'r+'),
    [],
    [],
    [],
    null,
    '1.1'
);

$response = $manifestMiddleware->process($request, $appHandler);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->getHeaderLine('Content-Type') . "\n";
$body = $response->getBody()->getContents();
echo "Body: " . $body . "\n\n";

// Test 2: API request (should go to app handler)
echo "Test 2: Request for /api/user\n";
$serverParams['REQUEST_URI'] = '/api/user';
$request = new ServerRequest(
    $serverParams,
    [],
    'https://example.com/api/user',
    'GET',
    new Stream('php://temp', 'r+'),
    [],
    [],
    [],
    null,
    '1.1'
);

$response = $manifestMiddleware->process($request, $appHandler);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content-Type: " . $response->getHeaderLine('Content-Type') . "\n";
$body = $response->getBody()->getContents();
echo "Body: " . $body . "\n\n";

// Test 3: Regular page request (should go to app handler)
echo "Test 3: Request for / (should go to app handler)\n";
$serverParams['REQUEST_URI'] = '/';
$request = new ServerRequest(
    $serverParams,
    [],
    'https://example.com/',
    'GET',
    new Stream('php://temp', 'r+'),
    [],
    [],
    [],
    null,
    '1.1'
);

$response = $manifestMiddleware->process($request, $appHandler);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Body: " . $response->getBody()->getContents() . "\n";

echo "\n=== Example completed successfully ===\n";