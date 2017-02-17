<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use Cadre\DomainSession\Storage\StorageInterface;

class SessionManager
{
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function start(string $id): SessionInterface
    {
        try {
            $session = $this->storage->read($id);
        } catch (SessionException $e) {
            $session = $this->storage->createNew();
        }

        if ($session instanceof SessionInterface && $session->isExpired()) {
            $this->storage->delete($id);
            $session = $this->storage->createNew();
        }

        return $session;
    }

    public function finish(SessionInterface $session)
    {
        $session->lock();
        $this->storage->write($session);
    }
}
