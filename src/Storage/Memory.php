<?php
declare(strict_types=1);
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class Memory implements StorageInterface
{
    protected $expiresInterval;
    protected $sessions = [];
    protected $mtime = [];

    public function __construct(string $expiresInterval = 'PT3M')
    {
        $this->expiresInterval = new DateInterval($expiresInterval);
    }

    public function createNew(): Session
    {
        return Session::createWithId(
            SessionId::createWithNewValue()
        );
    }

    public function read(string $id): Session
    {
        if (isset($this->sessions[$id])) {
            $expires = $this->mtime[$id]->add($this->expiresInterval);
            $when = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            if ($expires <= $when) {
                $this->delete($id);
            }
        }

        if (isset($this->sessions[$id])) {
            $source = @unserialize($this->sessions[$id]);
            if (false === $source) {
                throw new SessionException("Session {$id} not unserializable.");
            }

            return new Session(
                new SessionId($id),
                $source['data'],
                $source['created'],
                new DateTimeImmutable('now', new DateTimeZone('UTC')),
                $source['updated']
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
            'data' => $session->asArray(),
            'created' => $session->getCreated(),
            'accessed' => $session->getAccessed(),
            'updated' => $session->getUpdated()
        ]);

        $this->mtime[$session->getId()->value()] = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }

    public function delete(string $id)
    {
        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
            unset($this->mtime[$id]);
        }
    }
}
