<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use Cadre\DomainSession\Storage\StorageInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class SessionManager
{
    protected $storage;
    protected $logger;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
        $this->setLogger(new NullLogger());
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function start($id): SessionInterface
    {
        $this->logger->debug('SessionManager::start', ['id' => $id]);
        if (empty($id)) {
            $session = $this->storage->createNew();
        } else {
            try {
                $session = $this->storage->read($id);
            } catch (SessionException $e) {
                $session = $this->storage->createNew();
            }
        }

        return $session;
    }

    public function finish(SessionInterface $session)
    {
        $id = $session->getId()->value();
        $this->logger->debug('SessionManager::finish', compact('id'));
        $session->lock();
        $this->storage->write($session);
    }
}
