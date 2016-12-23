<?php
namespace Cadre\Domain_Session;

class DomainSessionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testNewInstance()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);

        $factory = new DomainSessionFactory($storage);

        $session = $factory('id');

        $this->assertEquals('id', $session->getId());
    }
}
