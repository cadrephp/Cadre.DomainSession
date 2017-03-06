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
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Memory implements StorageInterface, LoggerAwareInterface
{
    protected $expiresInterval;
    protected $sessions = [];
    protected $mtime = [];
    protected $logger;

    public function __construct(string $expiresInterval = 'PT3M')
    {
        $this->expiresInterval = new DateInterval($expiresInterval);
        $this->setLogger(new NullLogger());
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createNew(): Session
    {
        $this->logger->debug('Storage\Memory::createNew');
        return Session::createWithId(
            SessionId::createWithNewValue()
        );
    }

    public function read(string $id): Session
    {
        $this->logger->debug('Storage\Memory::read', compact('id'));

        if (isset($this->sessions[$id])) {
            $expires = $this->mtime[$id]->add($this->expiresInterval);
            $when = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            if ($expires <= $when) {
                $this->logger->debug('Storage\Memory::read::expired', compact('id'));
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
        $id = $session->getId()->value();
        $this->logger->debug('Storage\Memory::write', compact('id'));

        if ($session->getId()->hasUpdatedValue()) {
            $this->logger->debug('Storage\Memory::write::updatedKey', compact('id'));
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
        if (empty($id)) {
            return;
        }

        $this->logger->debug('Storage\Memory::delete', compact('id'));

        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
            unset($this->mtime[$id]);
        }
    }
}
