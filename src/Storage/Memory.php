<?php
declare(strict_types=1);
namespace Cadre\DomainSession\Storage;

use DateTime;
use DateTimeZone;
use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;

class Memory implements StorageInterface
{
    protected $sessions = [];

    public function createNew($interval = 'PT3M'): Session
    {
        return Session::createWithId(
            SessionId::createWithNewValue(),
            $interval
        );
    }

    public function read(string $id): Session
    {
        if (isset($this->sessions[$id])) {
            $source = @unserialize($this->sessions[$id]);
            if (false === $source) {
                throw new SessionException("Session {$id} not unserializable.");
            }
            return new Session(
                new SessionId($id),
                $source['data'],
                $source['created'],
                $source['updated'],
                $source['expires']
            );
        }

        throw new SessionException("Session {$id} not found.");
    }

    public function write(SessionInterface $session)
    {
        if ($session->getId()->hasUpdatedValue()) {
            $this->delete($session->getId()->startingValue());
        }

        $this->sessions[$session->getId()->value()] = serialize([
            'data' => $session->all(),
            'created' => $session->getCreated(),
            'updated' => $session->getUpdated(),
            'expires' => $session->getExpires(),
        ]);
    }

    public function delete(string $id)
    {
        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
        }
    }
}
