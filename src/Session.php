<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use DateInterval;
use DateTime;
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
        DateTime $created,
        DateTime $updated,
        DateTime $expires
    ) {
        $this->id = $id;
        $this->data = $data;
        $this->created = $created;
        $this->updated = $updated;
        $this->expires = $expires;
    }

    public static function withId(SessionId $id, $interval = 'PT3M')
    {
        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = clone ($updated);
        $expires->add(new DateInterval($interval));
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

    public function id(): SessionId
    {
        return $this->id;
    }

    public function created(): DateTime
    {
        return clone $this->created;
    }

    public function updated(): DateTime
    {
        return clone $this->updated;
    }

    public function expires(): DateTime
    {
        return clone $this->expires;
    }

    public function renew($interval = 'PT3M')
    {
        $this->markAsUpdated();
        $this->expires = clone $this->updated;
        $this->expires->add(new DateInterval($interval));
    }

    public function isExpired(DateTime $when = null): bool
    {
        if (is_null($when)) {
            $when = new DateTime('now', new DateTimeZone('UTC'));
        }

        return ($this->expires <= $when);
    }

    public function lock()
    {
        $this->locked = true;
    }

    protected function markAsUpdated()
    {
        if ($this->locked) {
            throw new SessionException('Cannot update a locked session');
        }
        $this->updated = new DateTime('now', new DateTimeZone('UTC'));
    }
}
