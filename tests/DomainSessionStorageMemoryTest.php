<?php
namespace Cadre\Domain_Session;

class DomainSessionStorageNullTest extends \PHPUnit_Framework_TestCase
{
    private $idFactory;

    public function setUp()
    {
        $this->idFactory = $this->createMock(IdFactoryInterface::class);
        $this->idFactory
            ->method('__invoke')
            ->willReturn('newId');
    }

    public function testNewId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $this->assertEquals('newId', $storage->newId());
    }

    public function testReadNewId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $this->assertEquals([], $storage->read('id'));
    }

    public function testReadExistingId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->write('id', ['data']);

        $this->assertEquals(['data'], $storage->read('id'));
    }

    public function testWriteId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->write('id', ['data']);

        $this->assertEquals(['data'], $storage->read('id'));
    }

    public function testRenameId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->write('oldId', ['data']);
        $storage->rename('oldId', 'newId');

        $this->assertEquals([], $storage->read('oldId'));
        $this->assertEquals(['data'], $storage->read('newId'));
    }

    public function testRenameMissingId()
    {
        $this->expectException(DomainSessionException::class);
        $this->expectExceptionMessage('Session oldId doesn\'t exist');

        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->rename('oldId', 'newId');
    }

    public function testRenameToExistingId()
    {
        $this->expectException(DomainSessionException::class);
        $this->expectExceptionMessage('Session newId already exists');

        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->write('oldId', ['data']);
        $storage->write('newId', ['data']);

        $storage->rename('oldId', 'newId');
    }

    public function testDeleteId()
    {
        $storage = new DomainSessionStorageMemory($this->idFactory);

        $storage->write('id', ['data']);

        $this->assertEquals(['data'], $storage->read('id'));

        $storage->delete('id');

        $this->assertEquals([], $storage->read('id'));
    }
}
