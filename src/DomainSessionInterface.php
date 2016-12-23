<?php
namespace Cadre\Domain_Session;

interface DomainSessionInterface
{
    public function getId();
    public function getStartingId();
    public function hasUpdatedId();
    public function regenerateId();

    public function start();
    public function finish();
}
