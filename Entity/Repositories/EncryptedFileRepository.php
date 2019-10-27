<?php

namespace Azine\JsCryptoStoreBundle\Entity\Repositories;

use Doctrine\ORM\EntityRepository;

/**
 * EncryptedFileRepository.
 */
class EncryptedFileRepository extends EntityRepository
{
    /**
     * Remove expired files from DB.
     */
    public function removeExpiredFiles()
    {
        $now = new \DateTime();
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.file as file')
            ->where('f.expiry < :now')
            ->setParameter('now', $now);

        $expiredFiles = $queryBuilder->getQuery()->execute();
        foreach ($expiredFiles as $next) {
            unlink($next['file']);
        }

        $queryBuilder = $this->createQueryBuilder('f')
            ->delete()
            ->where('f.expiry < :now')
            ->setParameter('now', $now);

        $queryBuilder->getQuery()->execute();
    }

    /**
     * @param $owner_id
     *
     * @return array of array groupToken => EncryptedFile
     */
    public function findForDashBoard($owner_id)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.description as description, f.expiry as expiry, f.mimeType as mimeType, f.fileName as fileName, f.groupToken as groupToken, f.token as token')
            ->where('f.owner_id = :ownerId')
            ->setParameter('ownerId', $owner_id)
            ->orderBy('f.groupToken', 'desc');

        $files = $queryBuilder->getQuery()->execute();
        $fileGroups = array();
        foreach ($files as $file) {
            $groupToken = $file['groupToken'];
            $file['group'] = substr($files[0]['groupToken'], 0, strrpos($files[0]['groupToken'], '-'));
            if (!array_key_exists($groupToken, $fileGroups)) {
                $fileGroups[$groupToken] = array();
            }
            $fileGroups[$groupToken][] = $file;
        }

        return $fileGroups;
    }

    public function findForDownload($groupToken, $token = null)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.description as description, f.expiry as expiry, f.mimeType as mimeType, f.fileName as fileName, f.groupToken as groupToken, f.token as token')
            ->where('f.groupToken = :groupToken')
            ->setParameter('groupToken', $groupToken)
            ->orderBy('f.groupToken', 'desc');

        if (null != $token) {
            $queryBuilder->andWhere('f.token = :token')
                ->setParameter('token', $token);
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function getGroupTokensForUser($owner_id)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.groupToken as groupToken')
            ->distinct()
            ->where('f.owner_id = :ownerId')
            ->setParameter('ownerId', $owner_id)
            ->orderBy('f.groupToken', 'desc')
            ;

        $result = array();
        foreach ($queryBuilder->getQuery()->execute() as $next) {
            $result[] = substr($next['groupToken'], 0, strrpos($next['groupToken'], '-'));
        }

        return $result;
    }
}
