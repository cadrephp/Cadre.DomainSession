<?php
declare(strict_types=1);
namespace Cadre\Domain_Session;

class DomainSessionManager
{
    protected $storage;

    public function __construct(DomainSessionStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function start(string $id): DomainSessionInterface
    {
        try {
            $session = $this->storage->read($id);
        } catch (DomainSessionException $e) {
            $session = $this->storage->createNew();
        }

        if ($session instanceof DomainSessionInterface && $session->isExpired()) {
            $this->storage->delete($id);
            $session = $this->storage->createNew();
        }

        return $session;
    }

    public function finish(DomainSessionInterface $session)
    {
        $this->storage->write($session);
    }
}
