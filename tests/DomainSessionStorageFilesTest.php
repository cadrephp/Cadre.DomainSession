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
    }

    public function testReadMissingId()
    {
        $id = DomainSessionId::withNewValue();
        $session = DomainSession::withId($id);

        $storage = new DomainSessionStorageFiles($this->root->url());

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }

    public function testReadUnserializableId()
    {
        $id = DomainSessionId::withNewValue();
        $session = DomainSession::withId($id);

        vfsStream::newFile(bin2hex($id))
            ->at($this->root)
            ->setContent('bogus-dsadh89h32huih3jk4h23');

        $storage = new DomainSessionStorageFiles($this->root->url());

        $this->expectException(DomainSessionException::class);

        $storage->read($id);
    }

    public function testCreateAndWriteNewId()
    {
        $storage = new DomainSessionStorageFiles($this->root->url());

        $session = $storage->createNew();
        $id = $session->id()->value();

        $storage->write($session);

        $this->assertInstanceOf(DomainSessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());
    }

    public function testWriteRegeneratedId()
    {
        $storage = new DomainSessionStorageFiles($this->root->url());

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

        $storage = new DomainSessionStorageFiles($this->root->url());

        $storage->write($session);

        $this->assertInstanceOf(DomainSessionInterface::class, $storage->read($id));
        $this->assertEquals($session->id(), $storage->read($id)->id());

        $storage->delete($id);

        $this->expectException(DomainSessionException::class);

        $s2 = $storage->read($id);
    }
}
