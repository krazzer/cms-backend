<?php

namespace KikCMS\Domain\App\Service;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

readonly class CallableService
{
    public function __construct(private ContainerInterface $container) {}

    public function getCallableByString(?string $configCallable): ?callable
    {
        if ( ! $configCallable || ! str_contains($configCallable, '::')) {
            return null;
        }

        [$class, $method] = explode('::', $configCallable);

        if ( ! class_exists($class)) {
            throw new InvalidArgumentException("Service class $class not found.");
        }

        if ( ! $this->container->has($class)) {
            throw new InvalidArgumentException("Service class $class exists, but could not be injected. Make sure " .
                "the class is publicly available.");
        }

        $instance = $this->container->get($class);

        if ( ! method_exists($instance, $method) || ! is_callable([$instance, $method])) {
            throw new InvalidArgumentException("Method $method not found on service $class.");
        }

        return [$instance, $method];
    }
}
