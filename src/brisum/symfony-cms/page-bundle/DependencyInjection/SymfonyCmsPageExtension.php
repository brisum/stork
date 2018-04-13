<?php

namespace Brisum\Symfony\Cms\Bundle\SymfonyCmsPageBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Config\FileLocator;

class SymfonyCmsPageExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('symfony_cms_page.templates', $config['templates']);
        $container->setParameter('symfony_cms_page.statuses', $config['statuses']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('sonata_admin.yml');
    }
}
