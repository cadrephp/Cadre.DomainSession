<?php
namespace Cadre\Domain_Session;

interface DomainSessionStorageInterface
{
    public function newId();
    public function read($id);
    public function write($id, array $data);
    public function rename($oldId, $newId);
    public function delete($id);
}
