<?php

declare(strict_types = 1);

namespace Surfnet\YubikeyApiClientBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SurfnetYubikeyApiClientExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), $config);

        $container->setParameter(
            'surfnet_yubikey_api_client.credentials.client_id',
            (string) $config['credentials']['client_id']
        );
        $container->setParameter(
            'surfnet_yubikey_api_client.credentials.client_secret',
            $config['credentials']['client_secret']
        );

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
