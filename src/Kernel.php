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

    private bool $project = false;

    const string DIR_VENDOR_KIKSAUS  = 'vendor/kiksaus';
    const string DIR_SRC             = 'src';
    const string DIR_CONFIG          = 'config';
    const string DIR_CERTS           = 'certs';
    const string DIR_CMS_CERTS       = 'var/certs';
    const string DIR_CONFIG_PACKAGES = 'config/packages';
    const string DIR_PUBLIC          = 'public_html';
    const string DIR_ADMIN           = 'public_html/cms';

    const string FILE_DOCKER_COMPOSE_SERVICES = 'docker/docker-compose-services.yml';
    const string FILE_DOCKER_COMPOSE_SITE     = 'docker/docker-compose-site.yml';
    const string FILE_DOCKER_COMPOSE          = 'docker/docker-compose.yml';

    const string FILE_CERT         = 'certs/cert.crt';
    const string FILE_CERT_KEY     = 'certs/cert.key';
    const string FILE_CMS_CERT     = 'var/certs/cert.crt';
    const string FILE_CMS_CERT_KEY = 'var/certs/cert.key';

    const string FILE_SNAKE_CERT     = 'resources/certs/snakeoil.crt';
    const string FILE_SNAKE_CERT_KEY = 'resources/certs/snakeoil.key';

    public function boot(): void
    {
        parent::boot();

        if (empty($_ENV['DEFAULT_EMAIL_FROM'])) {
            throw new RuntimeException('Required DEFAULT_EMAIL_FROM $_ENV variable is missing');
        }

        // If the ENV variable is set, we're running in a project context, if not, we're running the CMS standalone
        $this->project = isset($_ENV['PROJECT_ROOT']);
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $cmsConfigDir = $this->getCmsDir(self::DIR_CONFIG);

        // Import CMS services
        $container->import($cmsConfigDir . '/services.yaml');
        $container->import($cmsConfigDir . '/services/*.yaml');
        $container->import($cmsConfigDir . '/services/**/*.yaml');
        $container->import($cmsConfigDir . '/{packages}/*.yaml');

        // Load environment-specific config
        $envPackagesDir = $this->getCmsDir(self::DIR_CONFIG_PACKAGES . DIRECTORY_SEPARATOR . $this->getEnvironment());

        if (is_dir($envPackagesDir)) {
            $container->import($envPackagesDir . '/*.yaml');
            $container->import($envPackagesDir . '/**/*.yaml');
        }

        if ( ! $this->isProject()) {
            return;
        }

        // Add app namespace to autowire
        $container->services()
            ->load('App\\', $this->getAppDir(self::DIR_SRC) . '/*')
            ->autowire()
            ->autoconfigure();

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

        if ( ! $this->isProject()) {
            return;
        }

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
        // Tests don't need project files
        if ($this->isProject()) {
            $package = basename($this->getProjectDir());
            $cmsRoot = $this->getAppDir(self::DIR_VENDOR_KIKSAUS . DIRECTORY_SEPARATOR . $package);
        } else {
            $cmsRoot = $this->getProjectDir();
        }

        if ($path) {
            return $cmsRoot . DIRECTORY_SEPARATOR . $path;
        }

        return $cmsRoot;
    }

    public function isProject(): bool
    {
        return $this->project;
    }
}
