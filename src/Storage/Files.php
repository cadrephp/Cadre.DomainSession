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

class Files implements StorageInterface
{
    protected $path;
    protected $expiresInterval;

    public function __construct(string $path, string $expiresInterval = 'PT3M')
    {
        $this->path = $path;
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
        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            $expires = (new DateTimeImmutable())
                ->setTimestamp(filemtime($filename))
                ->add($this->expiresInterval);
            $when = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            if ($expires <= $when) {
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
        if ($session->getId()->hasUpdatedValue()) {
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
        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            unlink($filename);
        }
    }

    protected function getFilename(string $id)
    {
        // Sanitizing id for filename
        return $this->path . DIRECTORY_SEPARATOR . bin2hex($id);
    }

    private function unserialize($serialized)
    {
        $level = error_reporting(0);
        $unserialized = unserialize($serialized);
        error_reporting($level);
        return $unserialized;
    }
}
