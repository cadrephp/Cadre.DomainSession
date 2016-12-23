<?php
namespace Cadre\Domain_Session;

interface DomainSessionFactoryInterface
{
    public function __invoke($id);
}
