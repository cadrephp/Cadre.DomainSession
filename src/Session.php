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
    protected $created;
    protected $updated;
    protected $expires;
    protected $locked = false;

    public function __construct(
        SessionId $id,
        array $data,
        DateTimeInterface $created,
        DateTimeInterface $updated,
        DateTimeInterface $expires
    ) {
        $this->id = $id;
        $this->data = $data;
        $this->created = new DateTimeImmutable($created->format('YmdHis'), $created->getTimezone());
        $this->updated = new DateTimeImmutable($updated->format('YmdHis'), $updated->getTimezone());
        $this->expires = new DateTimeImmutable($expires->format('YmdHis'), $expires->getTimezone());
    }

    public static function createWithId(SessionId $id, $interval = 'PT3M')
    {
        $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expires = $updated->add(new DateInterval($interval));
        $session = new static($id, [], $created, $updated, $expires);
        return $session;
    }

    public function all()
    {
        return $this->data;
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : $default;
    }

    public function set(string $key, $val)
    {
        $this->markAsUpdated();
        $this->data[$key] = $val;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key)
    {
        $this->markAsUpdated();
        unset($this->data[$key]);
    }

    public function getId(): SessionId
    {
        return $this->id;
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }

    public function getExpires(): DateTimeImmutable
    {
        return $this->expires;
    }

    public function renew($interval = 'PT3M')
    {
        $this->markAsUpdated();
        $this->expires = $this->updated->add(new DateInterval($interval));
    }

    public function isExpired(DateTimeInterface $when = null): bool
    {
        if (is_null($when)) {
            $when = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        }

        return ($this->expires <= $when);
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
