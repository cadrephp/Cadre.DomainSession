<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use DateTimeImmutable;
use DateTimeInterface;

interface SessionInterface
{
    public function all();
    public function get(string $key, $default = null);
    public function set(string $key, $val);
    public function has(string $key): bool;
    public function remove(string $key);

    public function id(): SessionId;
    public function getCreated(): DateTimeImmutable;
    public function getUpdated(): DateTimeImmutable;
    public function getExpires(): DateTimeImmutable;
    public function renew($interval = 'PT3M');
    public function isExpired(DateTimeInterface $when = null): bool;
    public function lock();
}
