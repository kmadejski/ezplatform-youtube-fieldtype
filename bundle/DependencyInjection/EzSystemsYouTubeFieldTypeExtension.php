<?php

namespace EzSystems\YouTubeFieldTypeBundle\DependencyInjection;

use EzSystems\PlatformUIBundle\DependencyInjection\PlatformUIExtension;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @see http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EzSystemsYouTubeFieldTypeExtension extends Extension implements PrependExtensionInterface, PlatformUIExtension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/'));
        $loader->load('config/services.yml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $configFiles = [
            'ezpublish' => 'config/ezplatform.yml',
        ];

        foreach ($configFiles as $extensionName => $configFileName) {
            $configFile = __DIR__ . '/../Resources/' . $configFileName;
            $config = Yaml::parse(file_get_contents($configFile));
            $container->prependExtensionConfig($extensionName, $config);
            $container->addResource(new FileResource($configFile));
        }

        $container->prependExtensionConfig('assetic', array('bundles' => array('EzSystemsYouTubeFieldTypeBundle')));

        $this->prependYui($container);
        $this->prependCss($container);
    }

    private function prependYui(ContainerBuilder $container)
    {
        $container->setParameter(
            'ezyt_fieldtype.public_dir',
            'bundles/ezsystemsyoutubefieldtype'
        );

        $yuiConfigFile = __DIR__ . '/../Resources/config/yui.yml';
        $config = Yaml::parse(file_get_contents($yuiConfigFile));
        $container->prependExtensionConfig('ez_platformui', $config);
        $container->addResource(new FileResource($yuiConfigFile));
    }

    private function prependCss(ContainerBuilder $container)
    {
        $container->setParameter(
            'ezyt_fieldtype.css_dir',
            'bundles/ezsystemsyoutubefieldtype/css'
        );

        $cssConfigFile = __DIR__ . '/../Resources/config/css.yml';
        $config = Yaml::parse(file_get_contents($cssConfigFile));
        $container->prependExtensionConfig('ez_platformui', $config);
        $container->addResource(new FileResource($cssConfigFile));
    }

    /**
     * Returns the translation domains used by the extension.
     *
     * @return array An array of extensions
     */
    public function getTranslationDomains()
    {
        return ['ezyt', 'validators'];
    }
}
