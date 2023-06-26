<?php

namespace Azine\JsCryptoStoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Define the possible configuration settings for the config.yml/.xml.
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('azine_js_crypto_store');

        $rootNode->children()
            ->scalarNode('ownerProvider')->defaultValue('Azine\JsCryptoStoreBundle\Service\DefaultOwnerProvider')->info('Service used to get the ownerId (e.g. the current user id) to associate uploaded files with the current user.')->end()

            ->scalarNode('encryptionCipher')->defaultValue('aes')->info('Encryption Ciper Algorythm. Default: aes')->end()
            ->scalarNode('encryptionMode')->defaultValue('gcm')->info("Encryption Mode. Default: 'gcm' (Galois/Counter mode)")->end()
            ->integerNode('encryptionIterations')->defaultValue(1000)->info('Encryption Key Hash-Iterations. Default: 1000')->end()
            ->integerNode('encryptionKs')->defaultValue(256)->info('KS. Default: 256')->end()
            ->integerNode('encryptionTs')->defaultValue(128)->info('TS. Default: 128')->end()
            ->scalarNode('defaultLifeTime')->defaultValue('14 days')->info("Default expiry time/date for documents. Format: input for DateTime(). Default: '14 days'")->end()
            ->integerNode('maxFileSize')->defaultValue(50000000)->info("Maximum file size in bytes for the encryption. Default: 50'000'000 = 50mb")->end()
        ->end();

        return $treeBuilder;
    }
}
