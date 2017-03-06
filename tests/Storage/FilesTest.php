<?php
namespace Cadre\DomainSession\Storage;

use Cadre\DomainSession\Session;
use Cadre\DomainSession\SessionException;
use Cadre\DomainSession\SessionId;
use Cadre\DomainSession\SessionInterface;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class FilesTest extends TestCase
{
    private $root;
    private $idFactory;

    public function setUp()
    {
        $this->root = vfsStream::setup('SessionDir');
    }

    public function testReadMissingId()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Files($this->root->url());

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testReadUnserializableId()
    {
        $id = SessionId::createWithNewValue();

        vfsStream::newFile($id)
            ->at($this->root)
            ->setContent('bogus-dsadh89h32huih3jk4h23');

        $storage = new Files($this->root->url());

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testCreateAndWriteNewId()
    {
        $storage = new Files($this->root->url());

        $session = $storage->createNew();
        $id = $session->getId()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->getId()->value(), $storage->read($id)->getId()->value());
    }

    public function testWriteRegeneratedId()
    {
        $storage = new Files($this->root->url());

        $session = $storage->createNew();
        $id = $session->getId()->value();

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->getId()->value(), $storage->read($id)->getId()->value());

        unset($session);
        $session = $storage->read($id);

        $session->getId()->regenerate();

        $storage->write($session);

        $this->expectException(SessionException::class);

        $storage->read($id);
    }

    public function testDeleteMissingId()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        $storage = new Files($this->root->url());

        $storage->write($session);

        $this->assertInstanceOf(SessionInterface::class, $storage->read($id));
        $this->assertEquals($session->getId()->value(), $storage->read($id)->getId()->value());

        $storage->delete($id);

        $this->expectException(SessionException::class);

        $s2 = $storage->read($id);
    }

    public function testDeleteEmptyId()
    {
        $storage = new Files($this->root->url());

        $storage->delete('');
    }

    public function testReadExpiredSession()
    {
        $id = SessionId::createWithNewValue();
        $session = Session::createWithId($id);

        vfsStream::newFile($id)
            ->at($this->root)
            ->setContent(serialize([
                'data' => $session->asArray(),
                'created' => $session->getCreated(),
                'accessed' => $session->getAccessed(),
                'updated' => $session->getUpdated(),
            ]))
            ->lastModified(strtotime('-10 minutes'));

        $storage = new Files($this->root->url());

        $this->expectException(SessionException::class);

        $storage->read($id);
    }
}
