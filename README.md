# oryx/adr

Action Domain Responder (ADR) library for PHP, compatible with oryx/mvc and PWA middleware.

## Overview

This library implements the Action Domain Responder (ADR) pattern, a refinement of the MVC pattern that separates concerns into:
- **Action**: Receives input and delegates to Domain
- **Domain**: Contains business logic
- **Responder**: Prepares and returns HTTP responses

The library includes:
- ADR pattern interfaces and abstract base classes
- ManifestJsonMiddleware for PWA compatibility
- PSR-15 compliant middleware integration
- PSR-4 autoloading
- JSON:API and Problem Details (RFC 7807) responders

## Installation

```bash
composer require oryx/adr
```

## Requirements

- PHP 8.1+
- PSR interfaces (http-message, http-server-middleware)
- Laminas Diactoros (for JSON responses)
- oryx/mvc (framework integration)

## Usage

### Basic ADR Implementation

```php
use Oryx\Adr\Action\ActionInterface;
use Oryx\Adr\Domain\DomainInterface;
use Oryx\Adr\Responder\ResponderFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\Response;

// Define your domain
class UserDomain implements DomainInterface {
    public function getUserProfile(int $userId): array {
        // Business logic here
        return [
            'id' => $userId,
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
    }
}

// Define your responder
class JsonApiResponder implements ResponderInterface {
    private $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function respond(): ResponseInterface {
        // Simplified JSON:API responder for example
        return new \Laminas\Diactoros\JsonResponse($this->data);
    }
}

// Define your action
class GetUserProfileAction implements ActionInterface {
    private UserDomain $domain;
    private ResponderFactory $responderFactory;
    
    public function __construct(UserDomain $domain, ResponderFactory $responderFactory) {
        $this->domain = $domain;
        $this->responderFactory = $responderFactory;
    }
    
    public function execute(DomainInterface $domain): string {
        // Extract user ID from request (in real implementation)
        $userId = 123; 
        
        // Delegate to domain
        $userData = $domain->getUserProfile($userId);
        
        // Return responder class name
        return JsonApiResponder::class;
    }
    
    public function __invoke(ServerRequestInterface $request, PsrResponseInterface $response, callable $next = null): PsrResponseInterface
    {
        // Execute the action to get the responder class name
        $responderClass = $this->execute($this->domain);
        
        // Create the responder instance using the factory
        $responderInstance = $this->responderFactory->create($responderClass);
        
        // Generate and return the response
        return $responderInstance->respond();
    }
}

// Usage in oryx/mvc
$app = new \Oryx\Mvc\Application();
$container = $app->getContainer();

// Register your domain and responder factory with the container
// Example:
// $container->set(UserDomain::class, new UserDomain());
// $container->set(\Oryx\Adr\Responder\ResponderFactory::class, 
//     new \Oryx\Adr\Responder\DefaultResponderFactory($container));

// Then create your action and process the request.
```

### Using Built-in Responders

```php
use Oryx\Adr\Responder\JsonApiResponder;
use Oryx\Adr\Responder\ProblemDetailsResponder;

// JSON:API responder for successful responses
$jsonApiResponder = new JsonApiResponder([
    'data' => [
        'type' => 'users',
        'id' => '1',
        'attributes' => [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]
    ]
]);

// Problem Details responder for error responses
$problemDetailsResponder = new ProblemDetailsResponder(
    'https://example.com/probs/out-of-credit',
    'You do not have enough credit.',
    403,
    'Your account has only 5 USD left.',
    'https://example.com/account/12345/msgs/abc'
);
```

### Using with oryx/mvc Middleware

The ManifestJsonMiddleware is PSR-15 compliant and can be used directly with oryx/mvc:

```php
use Oryx\Adr\Middleware\ManifestJsonMiddleware;
use Oryx\Mvc\Application;

// Create application
$app = new Application();

// Add PWA manifest middleware
$app->addMiddleware(new ManifestJsonMiddleware());

// Add other middleware and routes...
$app->run();
```

### ManifestJsonMiddleware Features

The ManifestJsonMiddleware automatically handles requests for:
- `/manifest.json`
- `/manifest.webmanifest`

It returns a standard PWA manifest with configurable properties:
- App name and short name (configurable via constructor)
- Description (configurable via constructor)
- Start URL (configurable via constructor)
- Display mode (configurable via constructor)
- Theme and background colors (configurable via constructor)
- Icon array for various sizes (configurable via constructor, falls back to default set if empty)

The middleware delegates all other requests to the next middleware in the queue.

#### Usage with Custom Values

```php
use Oryx\Adr\Middleware\ManifestJsonMiddleware;

// Create middleware with custom PWA manifest values
$middleware = new ManifestJsonMiddleware(
    'My Custom PWA',           // name
    'MyApp',                   // short_name
    'A custom PWA application',// description
    '/home',                   // start_url
    'fullscreen',              // display
    '#000000',                 // background_color
    '#ffffff',                 // theme_color
    [                          // icons (empty array uses default icons)
        [
            'src' => '/icons/custom-192.png',
            'sizes' => '192x192',
            'type' => 'image/png'
        ]
    ]
);
```

## PWA Compatibility

This library provides PWA compatibility through the ManifestJsonMiddleware which serves the web app manifest required for:
- Installable PWAs
- Offline capabilities (when combined with service workers)
- Mobile app-like experience

For full PWA functionality, combine this middleware with:
- Service worker middleware (for offline caching)
- Secure HTTPS connection
- Proper icon assets

## Testing

Run the test suite:

```bash
vendor/bin/phpunit
```

## License

This library is licensed under the BSD 3-Clause License - see the [LICENSE](LICENSE) file for details.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -am 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Create a new Pull Request