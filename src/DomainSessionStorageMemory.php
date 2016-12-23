<?php
namespace Cadre\Domain_Session;

class DomainSessionStorageMemory implements DomainSessionStorageInterface
{
    protected $idFactory;
    protected $sessions = [];

    public function __construct(IdFactoryInterface $idFactory)
    {
        $this->idFactory = $idFactory;
    }

    public function newId()
    {
        return ($this->idFactory)();
    }

    public function read($id)
    {
        if (isset($this->sessions[$id])) {
            return $this->sessions[$id];
        } else {
            return [];
        }
    }

    public function write($id, array $data)
    {
        $this->sessions[$id] = $data;
    }

    public function rename($oldId, $newId)
    {
        if (!isset($this->sessions[$oldId])) {
            throw new DomainSessionException("Session {$oldId} doesn't exist");
        }

        if (isset($this->sessions[$newId])) {
            throw new DomainSessionException("Session {$newId} already exists");
        }

        $this->sessions[$newId] = $this->sessions[$oldId];
        $this->delete($oldId);
    }

    public function delete($id)
    {
        if (isset($this->sessions[$id])) {
            unset($this->sessions[$id]);
        }
    }
}
