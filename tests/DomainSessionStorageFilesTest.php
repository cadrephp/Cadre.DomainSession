<?php
namespace Cadre\Domain_Session;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class DomainSessionStorageFilesTest extends \PHPUnit_Framework_TestCase
{
    private $root;
    private $idFactory;

    public function setUp()
    {
        $this->root = vfsStream::setup('SessionDir');
        $this->idFactory = $this->createMock(IdFactoryInterface::class);
        $this->idFactory
            ->method('__invoke')
            ->willReturn('newId');
    }

    public function testNewId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        $this->assertEquals('newId', $storage->newId());
    }

    public function testReadNewId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        $this->assertFalse($this->root->hasChild('id'));
        $this->assertEquals([], $storage->read('id'));
        $this->assertFalse($this->root->hasChild('id'));
    }

    public function testReadExistingId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        vfsStream::newFile('id')
            ->at($this->root)
            ->setContent(serialize(['data']));

        $this->assertEquals(['data'], $storage->read('id'));
    }

    public function testReadExpiredId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        vfsStream::newFile('id')
            ->at($this->root)
            ->setContent(serialize(['data']))
            ->lastAttributeModified(time() - 200);

        $this->assertEquals([], $storage->read('id'));
    }

    public function testWriteId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        $this->assertFalse($this->root->hasChild('id'));

        $storage->write('id', ['data']);

        $this->assertTrue($this->root->hasChild('id'));
        $this->assertEquals(
            ['data'],
            unserialize($this->root->getChild('id')->getContent())
        );
    }

    public function testRenameId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        $ctime = time() - 50;

        vfsStream::newFile('oldId')
            ->at($this->root)
            ->setContent(serialize(['data']))
            ->lastAttributeModified($ctime);

        $this->assertTrue($this->root->hasChild('oldId'));

        $storage->rename('oldId', 'newId');

        $this->assertFalse($this->root->hasChild('oldId'));
        $this->assertTrue($this->root->hasChild('newId'));
        $this->assertEquals($ctime, $this->root->getChild('newId')->filectime());
    }

    public function testRenameMissingId()
    {
        $this->expectException(DomainSessionException::class);
        $this->expectExceptionMessage('Session oldId doesn\'t exist');

        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        $storage->rename('oldId', 'newId');
    }

    public function testRenameToExistingId()
    {
        $this->expectException(DomainSessionException::class);
        $this->expectExceptionMessage('Session newId already exists');

        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        vfsStream::newFile('oldId')
            ->at($this->root)
            ->setContent(serialize(['data']));

        vfsStream::newFile('newId')
            ->at($this->root)
            ->setContent(serialize(['data']));

        $storage->rename('oldId', 'newId');
    }

    public function testRenameToExpiredId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        vfsStream::newFile('oldId')
            ->at($this->root)
            ->setContent(serialize(['data']));

        vfsStream::newFile('newId')
            ->at($this->root)
            ->setContent(serialize(['expired data']))
            ->lastAttributeModified(time() - 200);

        $storage->rename('oldId', 'newId');

        $this->assertEquals(
            ['data'],
            unserialize($this->root->getChild('newId')->getContent())
        );
    }

    public function testDeleteId()
    {
        $storage = new DomainSessionStorageFiles($this->idFactory, vfsStream::url('SessionDir'), 180);

        vfsStream::newFile('id')
            ->at($this->root)
            ->setContent(serialize(['data']));

        $this->assertTrue($this->root->hasChild('id'));

        $storage->delete('id');

        $this->assertFalse($this->root->hasChild('id'));
    }
}
