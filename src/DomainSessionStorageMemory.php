<?php
declare(strict_types=1);
namespace Cadre\Domain_Session;

use DateTime;
use DateTimeZone;

class DomainSessionStorageMemory implements DomainSessionStorageInterface
{
    protected $sessions = [];

    public function createNew($interval = 'PT3M'): DomainSession
    {
        return DomainSession::withId(
            DomainSessionId::withNewValue(),
            $interval
        );
    }

    public function read(string $id): DomainSession
    {
        if (isset($this->sessions[$id])) {
            $source = @unserialize($this->sessions[$id]);
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

        $this->sessions[$session->id()->value()] = serialize([
            'data' => $session->all(),
            'created' => $session->created(),
            'updated' => $session->updated(),
            'expires' => $session->expires(),
        ]);
    }

    public function delete(string $id)
    {
        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
        }
    }
}
