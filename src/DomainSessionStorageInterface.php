<?php
declare(strict_types=1);
namespace Cadre\Domain_Session;

interface DomainSessionStorageInterface
{
    public function createNew($interval = 'PT3M'): DomainSession;
    public function read(string $id): DomainSession;
    public function write(DomainSessionInterface $session);
    public function delete(string $id);
}
