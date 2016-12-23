<?php
namespace Cadre\Domain_Session;

class DomainSession implements DomainSessionInterface
{
    protected $id = null;
    protected $startingId = null;
    protected $data = null;
    protected $storage;
    protected $finished = false;

    public function __construct(
        $id,
        DomainSessionStorageInterface $storage
    ) {
        $this->id = trim($id);
        $this->startingId = $this->id;
        $this->storage = $storage;
    }

    public function __get($key)
    {
        if ($this->data === null) {
            $this->start();
        }

        return $this->data->$key;
    }

    public function __set($key, $val)
    {
        if ($this->finished) {
            throw new DomainSessionException("Session {$this->id} is already finished");
        }

        if ($this->data === null) {
            $this->start();
        }

        $this->data->$key = $val;
    }

    public function __isset($key)
    {
        if ($this->data === null) {
            $this->start();
        }

        return isset($this->data->$key);
    }

    public function __unset($key)
    {
        if ($this->finished) {
            throw new DomainSessionException("Session {$this->id} is already finished");
        }

        if ($this->data === null) {
            $this->start();
        }

        unset($this->data->$key);
    }

    public function __destruct()
    {
        if (! $this->finished) {
            $this->finish();
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStartingId()
    {
        return $this->startingId;
    }

    public function hasUpdatedId()
    {
        return $this->startingId !== $this->id;
    }

    public function start()
    {
        if ($this->finished) {
            throw new DomainSessionException("Session {$this->id} cannot be restarted");
        }

        if ($this->data !== null) {
            return;
        }

        if ($this->id === '') {
            $this->id = $this->storage->newId();
        }

        $this->data = (object) $this->storage->read($this->id);
    }

    public function finish()
    {
        if ($this->data === null) {
            // never started!
            return;
        }

        if ($this->finished) {
            // already finished
            return;
        }

        $this->storage->write($this->id, (array) $this->data);
        $this->finished = true;
    }

    public function regenerateId()
    {
        if ($this->data === null) {
            $this->start();
        }

        if ($this->finished) {
            throw new DomainSessionException("Session {$this->id} is already finished");
        }

        $oldId = $this->id;
        $this->id = $this->storage->newId();
        $this->storage->rename($oldId, $this->id);
        return $this->id;
    }
}
