<?php
declare(strict_types=1);
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionInterface;

interface StorageInterface
{
    public function createNew(): Session;
    public function read(string $id): Session;
    public function write(SessionInterface $session);
    public function delete(string $id);
}
