<?php
declare(strict_types=1);
namespace Cadre\DomainSession;

use DateTime;

interface SessionInterface
{
    public function all();
    public function get(string $key, $default = null);
    public function set(string $key, $val);
    public function has(string $key): bool;
    public function remove(string $key);

    public function id(): SessionId;
    public function created(): DateTime;
    public function updated(): DateTime;
    public function expires(): DateTime;
    public function renew($interval = 'PT3M');
    public function isExpired(DateTime $when = null): bool;
    public function lock();
}
