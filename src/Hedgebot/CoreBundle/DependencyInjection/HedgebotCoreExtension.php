<?php
namespace Hedgebot\CoreBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;

class HedgebotCoreExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('hedgebot_core.api.uri', $config['api']['uri']);
        $container->setParameter('hedgebot_core.api.token', $config['api']['token']);
        $container->setParameter('hedgebot_core.config_path', $config['config_path']);
        $container->setParameter('hedgebot_core.layout_path', $config['layout_path']);
        
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        
        $loader->load('services.yml');
    }
}
