<?php

namespace KikCMS;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    public function boot(): void
    {
        parent::boot();

        if (empty($_ENV['DEFAULT_EMAIL_FROM'])) {
            throw new RuntimeException('Required DEFAULT_EMAIL_FROM $_ENV variable is missing');
        }

        if (empty($_ENV['PROJECT_ROOT'])) {
            throw new RuntimeException('Required PROJECT_ROOT $_ENV variable is missing');
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        // Import CMS services
        $container->import(__DIR__ . '/../config/services.yaml');
        $container->import(__DIR__ . '/../config/{packages}/*.yaml');

        // Import project services
        $projectServices = $this->getAppDir() . '/config/services.yaml';

        if (file_exists($projectServices)) {
            $container->import($projectServices);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        // CMS routes
        $routes->import(__DIR__ . '/../config/routes.yaml');
        $routes->import(__DIR__ . '/../config/routes/*.yaml');

        // Import project routes
        $projectRoutes = $this->getAppDir() . '/config/routes.yaml';

        if (file_exists($projectRoutes)) {
            $routes->import($projectRoutes);
        }
    }

    public function getAppDir(): string
    {
        return $_ENV['PROJECT_ROOT'];
    }
}
