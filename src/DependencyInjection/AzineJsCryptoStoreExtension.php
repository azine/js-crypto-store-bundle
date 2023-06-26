<?php

namespace Azine\JsCryptoStoreBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Configure the bundle with the values from the config.yml/.xml.
 */
class AzineJsCryptoStoreExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
        $container->setParameter('azine_js_crypto_store.encryptionCipher', $config['encryptionCipher']);
        $container->setParameter('azine_js_crypto_store.encryptionMode', $config['encryptionMode']);
        $container->setParameter('azine_js_crypto_store.encryptionIterations', $config['encryptionIterations']);
        $container->setParameter('azine_js_crypto_store.encryptionKs', $config['encryptionKs']);
        $container->setParameter('azine_js_crypto_store.encryptionTs', $config['encryptionTs']);
        $container->setParameter('azine_js_crypto_store.maxFileSize', $config['maxFileSize']);
        $container->setParameter('azine_js_crypto_store.defaultLifeTime', $config['defaultLifeTime']);

        $container->setAlias('azine_js_crypto_store.ownerProvider', $config['ownerProvider']);
    }
}
