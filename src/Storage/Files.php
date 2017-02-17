<?php
declare(strict_types=1);
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;

class Files implements StorageInterface
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function createNew($interval = 'PT3M'): Session
    {
        return Session::createWithId(
            SessionId::createWithNewValue(),
            $interval
        );
    }

    public function read(string $id): Session
    {
        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            $source = $this->unserialize(file_get_contents($filename));
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
        if ($session->id()->hasUpdatedValue()) {
            $this->delete($session->id()->startingValue());
        }

        $filename = $this->getFilename($session->id()->value());

        file_put_contents(
            $filename,
            serialize([
                'data' => $session->all(),
                'created' => $session->getCreated(),
                'updated' => $session->getUpdated(),
                'expires' => $session->getExpires(),
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
