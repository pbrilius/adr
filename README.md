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
use Oryx\Adr\Responder\ResponderInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;

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
class JsonResponder implements ResponderInterface {
    private $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function respond(): ResponseInterface {
        return new JsonResponse($this->data);
    }
}

// Define your action
class GetUserProfileAction implements ActionInterface {
    public function execute(DomainInterface $domain): string {
        // Extract user ID from request (in real implementation)
        $userId = 123; 
        
        // Delegate to domain
        $userData = $domain->getUserProfile($userId);
        
        // Return responder class name
        return JsonResponder::class;
    }
}
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

It returns a standard PWA manifest with:
- App name and short name
- Description
- Start URL
- Display mode
- Theme and background colors
- Icon array for various sizes

The middleware delegates all other requests to the next middleware in the queue.

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