<?php
namespace Cadre\Domain_Session;

class DomainSessionFactory implements DomainSessionFactoryInterface
{
    protected $storage;

    public function __construct(DomainSessionStorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function __invoke($id)
    {
        return new DomainSession($id, $this->storage);
    }
}
