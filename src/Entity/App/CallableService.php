<?php

namespace App\Entity\App;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CallableService
{
    /** @var ContainerInterface */
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $configCallable
     * @return callable|null
     */
    public function getCallableByString(string $configCallable): ?callable
    {
        if ( ! str_contains($configCallable, '::')) {
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