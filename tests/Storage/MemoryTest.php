<?php
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class MemoryTest extends TestCase
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
        $sessions = [$id->value() => 'bogus-dsadh89h32huih3jk4h23'];
        $reflectionProperty->setValue($storage, $sessions);

        $reflectionProperty = $reflectionClass->getProperty('mtime');
        $reflectionProperty->setAccessible(true);
        $expires = new DateTimeImmutable('+3 minutes', new DateTimeZone('UTC'));
        $mtime = [$id->value() => $expires];
        $reflectionProperty->setValue($storage, $mtime);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testCreateAndWriteNewId()
    {
        $storage = new Memory();

        $session = $storage->createNew();
        $id = $session->getId()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->getId(), $storage->read($id)->getId());
    }

    public function testWriteRegeneratedId()
    {
        $storage = new Memory();

        $session = $storage->createNew();
        $id = $session->getId()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->getId(), $storage->read($id)->getId());

        $session->getId()->regenerate();

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
        $this->assertEquals($session->getId(), $storage->read($id)->getId());

        $storage->delete($id);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testReadExpiredSession()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Memory();
        $storage->write($session);

        $reflectionClass = new \ReflectionClass(Memory::class);
        $reflectionProperty = $reflectionClass->getProperty('mtime');
        $reflectionProperty->setAccessible(true);
        $expires = new DateTimeImmutable('-10 minutes', new DateTimeZone('UTC'));
        $mtime = [$id->value() => $expires];
        $reflectionProperty->setValue($storage, $mtime);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }
}
