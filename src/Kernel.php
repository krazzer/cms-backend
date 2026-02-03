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

    const string DIR_DOCKER    = 'docker';
    const string DIR_VENDOR    = 'vendor';
    const string DIR_SRC       = 'src';
    const string DIR_CONFIG    = 'config';
    const string DIR_RESOURCES = 'resources';
    const string DIR_CERTS     = 'certs';

    const string DIR_RESOURCES_CERTS = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . self::DIR_CERTS;
    const string DIR_VENDOR_KIKSAUS  = self::DIR_VENDOR . DIRECTORY_SEPARATOR . 'kiksaus';

    const string FILE_DOCKER_COMPOSE_SITE = self::DIR_DOCKER . '/docker-compose-site.yml';

    const string FILE_CERT     = self::DIR_CERTS . '/cert.crt';
    const string FILE_CERT_KEY = self::DIR_CERTS . '/cert.key';

    const string FILE_SNAKE_CERT     = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . self::DIR_CERTS . '/snakeoil.crt';
    const string FILE_SNAKE_CERT_KEY = self::DIR_RESOURCES . DIRECTORY_SEPARATOR . self::DIR_CERTS . '/snakeoil.key';

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
        // Add app namespace to autowire
        $container->services()
            ->load('App\\', $this->getAppDir(self::DIR_SRC) . '/*')
            ->autowire()
            ->autoconfigure();

        $cmsConfigDir = $this->getCmsDir(self::DIR_CONFIG);

        // Import CMS services
        $container->import($cmsConfigDir . '/services.yaml');
        $container->import($cmsConfigDir . '/services/*.yaml');
        $container->import($cmsConfigDir . '/services/**/*.yaml');
        $container->import($cmsConfigDir . '/{packages}/*.yaml');

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

    public function getAppDir(?string $path = null): string
    {
        $projectRoot = $_ENV['PROJECT_ROOT'];

        if ($path) {
            return $projectRoot . DIRECTORY_SEPARATOR . $path;
        }

        return $projectRoot;
    }

    public function getCmsDir(?string $path = null): string
    {
        $packageDirName = basename($this->getProjectDir());

        $cmsRoot = $this->getAppDir(self::DIR_VENDOR_KIKSAUS . DIRECTORY_SEPARATOR . $packageDirName);

        if ($path) {
            return $cmsRoot . DIRECTORY_SEPARATOR . $path;
        }

        return $cmsRoot;
    }
}
