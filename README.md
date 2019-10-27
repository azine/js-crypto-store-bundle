# js-crypto-store-bundle
Symfony Bundle to store files on the server that have been securely encrypted on the client side (browser).

Meta-Data is stored in the database, the file-content is stored on the file-system.

Uses vanilla JS and Stanford Javascript Crypto Library (See https://github.com/bitwiseshiftleft/sjcl/)

## Features:
---------

- Upload password protected encrypted files
- Share files via link 
- Group encrypted files under one link, to share multiple files at once 
- Set expiry date for files that will be removed by the cleanup command
- Cleanup command that can be run via cronjob to remove expired files

## Installation
To install AzineEmailBundle with Composer just add the following to your `composer.json` file:

```javascript
// composer.json
{
    require: {
        "azine/azine/js-crypto-store-bundle": "dev-master"
    }
}
```
Then, you can install the new dependencies by running Composer’s update command from 
the directory where your `composer.json` file is located:

```
php composer.phar update
```
Now, Composer will automatically download all required files, and install them for you. 
All that is left to do is to update your AppKernel.php file, and register the new bundle.
AzineEmailBundle has a dependency on KnpPaginatorBundle, so it`s also nessesary to add it to AppKernel.php 
file after installing.


```php
<?php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Azine\JsCryptoStoreBundle\AzineJsCryptoStoreBundle(),
    // ...
);
```

Register the routes of the AzineEmailBundle:

```yml
# in app/config/routing.yml

# Default configuration for "AzineJsCryptoStoreBundle"
azine_js_crypto_store:

    # Service used to get the ownerId (e.g. the current user id) to associate uploaded files with the current user. All files with the same owner_id as provided by this service will be shown on the dashboard
    ownerProvider:        Azine\JsCryptoStoreBundle\Service\DefaultOwnerProvider

    # Encryption Ciper Algorythm. Default: aes
    encryptionCipher:     aes

    # Encryption Mode. Default: 'gcm' (Galois/Counter mode)
    encryptionMode:       gcm

    # Encryption Key Hash-Iterations. Default: 1000
    encryptionIterations: 1000

    # KS. Default: 256
    encryptionKs:         256

    # TS. Default: 128
    encryptionTs:         128

    # Default expiry time/date for documents. Format: input for DateTime(). Default: '14 days'
    defaultLifeTime:      '14 days'

    # Maximum file size in bytes for the encryption. Default: 50'000'000 = 50mb
    maxFileSize:          50000000

    
```

See https://github.com/bitwiseshiftleft/sjcl/ for valid configuration options for the encryption.

## Customize Owner Provider
To be able to share administration of uploaded files among users of a team or company, you can implement you custom logic to return an owner id.

E.g. by user role or by a group/team/company attribute you have stored on your users. 

You just need to implement your version of `\Azine\JsCryptoStoreBundle\Service\OwnerProviderInterface`, publish it as service and set the reference to your implementation in the config.yml.



## Build-Status etc.
[![Build Status](https://travis-ci.org/azine/js-crypto-store-bundle.png)](https://travis-ci.org/azine/js-crypto-store-bundle) 
[![Total Downloads](https://poser.pugx.org/azine/js-crypto-store-bundle/downloads.png)](https://packagist.org/packages/azine/js-crypto-store-bundle) 
[![Latest Stable Version](https://poser.pugx.org/azine/js-crypto-store-bundle/v/stable.png)](https://packagist.org/packages/azine/js-crypto-store-bundle) 
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/azine/js-crypto-store-bundle/badges/quality-score.png?s=6190311a47fa9ab8cfb45bfce5c5dcc49fc75256)](https://scrutinizer-ci.com/g/azine/js-crypto-store-bundle/) 
[![Code Coverage](https://scrutinizer-ci.com/g/azine/js-crypto-store-bundle/badges/coverage.png?s=57b026ec89fdc0767c1255c4a23b9e87a337a205)](https://scrutinizer-ci.com/g/azine/js-crypto-store-bundle/) 
