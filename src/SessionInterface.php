<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use DateTimeImmutable;
use DateTimeInterface;

interface SessionInterface
{
    public function asArray();
    public function __get(string $key);
    public function __set(string $key, $val);
    public function __isset(string $key): bool;
    public function __unset(string $key);

    public function getId(): SessionId;
    public function getCreated(): DateTimeImmutable;
    public function getAccessed(): DateTimeImmutable;
    public function getUpdated(): DateTimeImmutable;
    public function lock();
}
