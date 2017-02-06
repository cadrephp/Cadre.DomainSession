<?php
namespace Cadre\Domain_Session;

class DomainSessionStorageMemoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReadMissingId()
    {
        $id = DomainSessionId::withNewValue();
        $session = DomainSession::withId($id);

        $storage = new DomainSessionStorageMemory();

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }

    public function testReadUnserializableId()
    {
        $id = DomainSessionId::withNewValue();
        $session = DomainSession::withId($id);

        $storage = new DomainSessionStorageMemory();

        $reflectionClass = new \ReflectionClass(DomainSessionStorageMemory::class);
        $reflectionProperty = $reflectionClass->getProperty('sessions');
        $reflectionProperty->setAccessible(true);
        $bogus = [$id->value() => 'bogus-dsadh89h32huih3jk4h23'];
        $reflectionProperty->setValue($storage, $bogus);

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }

    public function testCreateAndWriteNewId()
    {
        $storage = new DomainSessionStorageMemory();

        $session = $storage->createNew();
        $id = $session->id()->value();

        $storage->write($session);

        $this->assertInstanceOf(DomainSessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());
    }

    public function testWriteRegeneratedId()
    {
        $storage = new DomainSessionStorageMemory();

        $session = $storage->createNew();
        $id = $session->id()->value();

        $storage->write($session);

        $this->assertInstanceOf(DomainSessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());

        $session->id()->regenerate();

        $storage->write($session);

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }

    public function testDeleteMissingId()
    {
        $id = DomainSessionId::withNewValue();
        $session = DomainSession::withId($id);

        $storage = new DomainSessionStorageMemory();

        $storage->write($session);

        $this->assertInstanceOf(DomainSessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());

        $storage->delete($id);

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }
}
