<?php

namespace Azine\JsCryptoStoreBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultOwnerProvider implements OwnerProviderInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * DefaultOwnerProvider constructor.
     *
     * @param TokenStorageInterface|null $token
     */
    public function __construct(TokenStorageInterface $tokenStorage = null)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Return the current users user-id or a random string if no user is logged in.
     *
     * @return string
     */
    public function getOwnerId()
    {
        if (null == $this->tokenStorage || null == $this->tokenStorage->getToken()) {
            return md5(microtime());
        }

        $token = $this->tokenStorage->getToken();

        if (!\is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return md5(microtime());
        }

        return $user->getId();
    }
}
