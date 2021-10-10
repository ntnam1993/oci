<?php declare(strict_types=1);

namespace Valantic\Oci;

use Shopware\Core\Framework\Plugin;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Valantic\Oci\Business\Handlers\OciHandlerInterface;

/** @psalm-suppress PropertyNotSetInConstructor */
final class ValanticOci extends Plugin
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(OciHandlerInterface::class)
            ->addTag('app.oci_handler');
    }

    public function getMigrationNamespace(): string
    {
        return 'Valantic\Oci\Persistence\Migration';
    }
}
