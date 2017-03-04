<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class Session implements SessionInterface
{
    protected $id;
    protected $data;
    protected $accessed;
    protected $created;
    protected $updated;
    protected $locked = false;

    public function __construct(
        SessionId $id,
        array $data,
        DateTimeInterface $created,
        DateTimeInterface $accessed,
        DateTimeInterface $updated
    ) {
        $this->id = $id;
        $this->data = $data;
        $this->created = new DateTimeImmutable($created->format('YmdHis'), $created->getTimezone());
        $this->accessed = new DateTimeImmutable($accessed->format('YmdHis'), $accessed->getTimezone());
        $this->updated = new DateTimeImmutable($updated->format('YmdHis'), $updated->getTimezone());
    }

    public static function createWithId(SessionId $id)
    {
        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $session = new static($id, [], $created, $accessed, $updated);
        return $session;
    }

    public function asArray()
    {
        return $this->data;
    }

    public function __get(string $key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    public function __set(string $key, $val)
    {
        $this->markAsUpdated();
        $this->data[$key] = $val;
    }

    public function __isset(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function __unset(string $key)
    {
        $this->markAsUpdated();
        unset($this->data[$key]);
    }

    public function getId(): SessionId
    {
        return $this->id;
    }

    public function getAccessed(): DateTimeImmutable
    {
        return $this->accessed;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    public function lock()
    {
        $this->locked = true;
        $this->id = SessionLockedId::createFromSessionId($this->id);
    }

    protected function markAsUpdated()
    {
        if ($this->locked) {
            throw new SessionException('Cannot update a locked session');
        }
        $this->updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
    }
}
