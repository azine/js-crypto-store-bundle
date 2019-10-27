<?php

namespace Azine\JsCryptoStoreBundle\Service;

interface OwnerProviderInterface
{
    /**
     * This id is used to associate a file with a user, so the user can see his/her encrypted files.
     *
     * @return string a string identifying the owner(s) of the encrypted file
     */
    public function getOwnerId();
}
