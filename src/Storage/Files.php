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

class Files implements StorageInterface, LoggerAwareInterface
{
    protected $path;
    protected $expiresInterval;
    protected $logger;

    public function __construct(string $path, string $expiresInterval = 'PT3M')
    {
        $this->path = $path;
        $this->expiresInterval = new DateInterval($expiresInterval);
        $this->setLogger(new NullLogger());
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function createNew(): Session
    {
        $this->logger->debug('Storage\Files::createNew');
        return Session::createWithId(
            SessionId::createWithNewValue()
        );
    }

    public function read(string $id): Session
    {
        $this->logger->debug('Storage\Files::read', compact('id'));

        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            $expires = (new DateTimeImmutable())
                ->setTimestamp(filemtime($filename))
                ->add($this->expiresInterval);
            $when = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            if ($expires <= $when) {
                $this->logger->debug('Storage\Files::read::expired', compact('id'));
                $this->delete($id);
            }
        }

        if (file_exists($filename)) {
            $source = $this->unserialize(file_get_contents($filename));
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
        $this->logger->debug('Storage\Files::write', compact('id'));

        if ($session->getId()->hasUpdatedValue()) {
            $this->logger->debug('Storage\Files::write::updatedKey', compact('id'));
            $this->delete($session->getId()->startingValue());
        }

        $filename = $this->getFilename($session->getId()->value());

        file_put_contents(
            $filename,
            serialize([
                'data' => $session->asArray(),
                'created' => $session->getCreated(),
                'accessed' => $session->getAccessed(),
                'updated' => $session->getUpdated(),
            ])
        );
    }

    public function delete(string $id)
    {
        if (empty($id)) {
            return;
        }

        $this->logger->debug('Storage\Files::delete', compact('id'));

        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    protected function getFilename(string $id)
    {
        // Sanitizing id for filename
        return $this->path . DIRECTORY_SEPARATOR . $id;
    }

    private function unserialize($serialized)
    {
        $level = error_reporting(0);
        $unserialized = unserialize($serialized);
        error_reporting($level);
        return $unserialized;
    }
}
