<?php

namespace KikCMS;

use KikCMS\Domain\App\Path\PathConfig;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    private bool $project = false;

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
        $cmsConfigDir = $this->getCmsDir(PathConfig::DIR_CONFIG);

        // Import CMS services
        $container->import($cmsConfigDir . '/services.yaml');
        $container->import($cmsConfigDir . '/services/*.yaml');
        $container->import($cmsConfigDir . '/services/**/*.yaml');
        $container->import($cmsConfigDir . '/{packages}/*.yaml');

        // Load environment-specific config
        $envPackagesDir = $this->getCmsDir(PathConfig::DIR_CONFIG_PACKAGES . DIRECTORY_SEPARATOR . $this->getEnvironment());

        if (is_dir($envPackagesDir)) {
            $container->import($envPackagesDir . '/*.yaml');
            $container->import($envPackagesDir . '/**/*.yaml');
        }

        if ( ! $this->isProject()) {
            return;
        }

        // Add app namespace to autowire
        $container->services()
            ->load('App\\', $this->getAppDir(PathConfig::DIR_SRC) . '/*')
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

    public function getDir(?string $path = null): string
    {
        return $this->isProject() ? $this->getAppDir($path) : $this->getCmsDir($path);
    }

    public function getPublicDir(?string $path = null): string
    {
        if( ! $path){
            $path = PathConfig::DIR_PUBLIC;
        } else {
            $path = PathConfig::DIR_PUBLIC . DIRECTORY_SEPARATOR . $path;
        }

        return $this->isProject() ? $this->getAppDir($path) : $this->getCmsDir($path);
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
            $cmsRoot = $this->getAppDir(PathConfig::DIR_VENDOR_KIKSAUS . DIRECTORY_SEPARATOR . $package);
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
