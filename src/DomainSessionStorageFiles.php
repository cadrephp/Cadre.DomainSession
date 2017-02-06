<?php
declare(strict_types=1);
namespace Cadre\Domain_Session;

class DomainSessionStorageFiles implements DomainSessionStorageInterface
{
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function createNew($interval = 'PT3M'): DomainSession
    {
        return DomainSession::withId(
            DomainSessionId::withNewValue(),
            $interval
        );
    }

    public function read(string $id): DomainSession
    {
        $filename = $this->getFilename($id);

        if (file_exists($filename)) {
            $source = @unserialize(file_get_contents($filename));
            if (false === $source) {
                throw new DomainSessionException("Session {$id} not unserializable.");
            }
            return new DomainSession(
                new DomainSessionId($id),
                $source['data'],
                $source['created'],
                $source['updated'],
                $source['expires']
            );
        }

        throw new DomainSessionException("Session {$id} not found.");
    }

    public function write(DomainSessionInterface $session)
    {
        if ($session->id()->hasUpdatedValue()) {
            $this->delete($session->id()->startingValue());
        }

        $filename = $this->getFilename($session->id()->value());

        file_put_contents(
            $filename,
            serialize([
                'data' => $session->all(),
                'created' => $session->created(),
                'updated' => $session->updated(),
                'expires' => $session->expires(),
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
}
