<?php

namespace Azine\JsCryptoStoreBundle\Command;

use Azine\JsCryptoStoreBundle\Entity\EncryptedFile;
use Azine\JsCryptoStoreBundle\Entity\Repositories\EncryptedFileRepository;
use Monolog\Logger;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remove all encrypted file that are expired from the database.
 *
 * @author dominik
 */
class RemoveExpiredFilesCommand extends Command
{
    /**
     * @var string|null The default command name
     */
    protected static $defaultName = 'js-crypto-store:remove-expired';

    /** @var ManagerRegistry */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, Logger $logger)
    {
        parent::__construct();
        $this->managerRegistry = $managerRegistry;
    }

    protected function configure()
    {
        $this->setName(self::$defaultName)
            ->setDescription('Remove all encrypted file that are expired from the database')
            ->setHelp(<<<EOF
The <info>js-crypto-store:remove-expired</info> command removes all expired files from the database.
EOF
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EncryptedFileRepository $repository */
        $repository = $this->managerRegistry->getRepository(EncryptedFile::class);
        $repository->removeExpiredFiles();
    }
}
