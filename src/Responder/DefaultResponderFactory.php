<?php

declare(strict_types=1);

namespace Oryx\Adr\Responder;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use ReflectionClass;
use ReflectionParameter;

/**
 * Default responder factory that uses reflection to instantiate responders,
 * injecting dependencies from the container.
 */
class DefaultResponderFactory implements ResponderFactory
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function create(string $responderClass): ResponderInterface
    {
        $reflector = new ReflectionClass($responderClass);

        if (!$reflector->isInstantiable()) {
            throw new \RuntimeException("Responder class {$responderClass} is not instantiable");
        }

        $constructor = $reflector->getConstructor();

        if (null === $constructor) {
            return $reflector->newInstance();
        }

        $parameters = $constructor->getParameters();
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $this->resolveParameter($parameter);
            $dependencies[] = $dependency;
        }

        return $reflector->newInstanceArgs($dependencies);
    }

    private function resolveParameter(ReflectionParameter $parameter): mixed
    {
        // If the parameter has a default value, we can use it if the container doesn't have the service
        if ($parameter->isDefaultValueAvailable()) {
            $defaultValue = $parameter->getDefaultValue();
        } else {
            $defaultValue = null;
        }

        if ($parameter->allowsNull()) {
            $allowsNull = true;
        } else {
            $allowsNull = false;
        }

        $class = $parameter->getClass();

        if (null !== $class) {
            $className = $class->getName();

            // Check if the container has the service
            if ($this->container->has($className)) {
                return $this->container->get($className);
            }

            // If the parameter allows null and we don't have the service, return null
            if ($allowsNull) {
                return null;
            }

            // If there's a default value, use it
            if ($defaultValue !== null) {
                return $defaultValue;
            }

            // If the class is not instantiated and we have no default, try to create it (if it's concrete)
            // But note: we might cause infinite recursion. We'll skip and throw an exception.
            // Instead, we rely on the container to have the service.
            throw new \RuntimeException(
                "Unable to resolve dependency {$className} for parameter {$parameter->getName()}. "
                . "Ensure it is defined in the container or has a default value."
            );
        }

        // For non-class parameters (e.g., string, int), use default value if available
        if ($defaultValue !== null) {
            return $defaultValue;
        }

        // If we get here, the parameter is required and we cannot resolve it
        throw new \RuntimeException(
            "Unable to resolve non-class parameter {$parameter->getName()}. "
            . "Ensure it has a default value or is provided by the container."
        );
    }
}