<?php
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;

class MemoryTest extends \PHPUnit_Framework_TestCase
{
    public function testReadMissingId()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Memory();

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testReadUnserializableId()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Memory();

        $reflectionClass = new \ReflectionClass(Memory::class);
        $reflectionProperty = $reflectionClass->getProperty('sessions');
        $reflectionProperty->setAccessible(true);
        $bogus = [$id->value() => 'bogus-dsadh89h32huih3jk4h23'];
        $reflectionProperty->setValue($storage, $bogus);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testCreateAndWriteNewId()
    {
        $storage = new Memory();

        $session = $storage->createNew();
        $id = $session->id()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());
    }

    public function testWriteRegeneratedId()
    {
        $storage = new Memory();

        $session = $storage->createNew();
        $id = $session->id()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());

        $session->id()->regenerate();

        $storage->write($session);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testDeleteMissingId()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Memory();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());

        $storage->delete($id);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }
}
