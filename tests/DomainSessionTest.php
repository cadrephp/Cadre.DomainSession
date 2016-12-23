<?php
namespace Cadre\Domain_Session;

class DomainSessionTest extends \PHPUnit_Framework_TestCase
{
    public function testStartNewSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);

        $session = new DomainSession('', $storage);
        $session->start();

        $this->assertEquals('id', $session->getId());
        $this->assertEquals('', $session->getStartingId());
    }

    public function testRestartNewSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);

        $session = new DomainSession('', $storage);
        $session->start();
        $session->start();
    }

    public function testRestartFinishedSession()
    {
        $this->expectException(DomainSessionException::class);

        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);

        $session = new DomainSession('', $storage);
        $session->start();
        $session->finish();
        $session->start();
    }

    public function testFinishWritesSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);
        $storage->expects($this->once())->method('write')->with(
            $this->equalTo('id'),
            $this->equalTo(['data' => 'testing'])
        );

        $session = new DomainSession('', $storage);
        $session->data = 'testing';
        $session->finish();
    }

    public function testFinishNonStartedSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->never())->method('newId');
        $storage->expects($this->never())->method('read');
        $storage->expects($this->never())->method('write');

        $session = new DomainSession('', $storage);
        $session->finish();

        $this->assertEquals('', $session->getId());
    }

    public function testRefinishSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);
        $storage->expects($this->once())->method('write');

        $session = new DomainSession('', $storage);
        $session->start();
        $session->finish();
        $session->finish();
    }

    public function testRegenerateSession()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage
            ->expects($this->exactly(2))
            ->method('newId')
            ->will($this->onConsecutiveCalls('oldId', 'newId'));
        $storage->expects($this->once())->method('read')->willReturn([]);
        $storage->expects($this->once())->method('rename')->with('oldId', 'newId');

        $session = new DomainSession('', $storage);
        $session->regenerateId();

        $this->assertTrue($session->hasUpdatedId());
    }

    public function testRegenerateFinishedSession()
    {
        $this->expectException(DomainSessionException::class);

        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);
        $storage->expects($this->once())->method('write');
        $storage->expects($this->never())->method('rename');

        $session = new DomainSession('', $storage);
        $session->start();
        $session->finish();
        $session->regenerateId();
    }

    public function testAccessors()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('newId')->willReturn('id');
        $storage->expects($this->once())->method('read')->willReturn([]);

        $session = new DomainSession('', $storage);

        $this->assertFalse(isset($session->data));

        $session->data = 'testing';

        $this->assertTrue(isset($session->data));
        $this->assertEquals('testing', $session->data);

        unset($session->data);

        $this->assertFalse(isset($session->data));
    }

    public function testAutoStartOnGet()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('read')->willReturn(['data' => 'testing']);

        $session = new DomainSession('id', $storage);

        $this->assertEquals('testing', $session->data);
    }

    public function testAutoStartOnUnset()
    {
        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('read')->willReturn(['data' => 'testing']);

        $session = new DomainSession('id', $storage);

        unset($session->data);
    }

    public function testSetFinishedSession()
    {
        $this->expectException(DomainSessionException::class);

        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('read')->willReturn([]);
        $storage->expects($this->once())->method('write');

        $session = new DomainSession('id', $storage);
        $session->start();
        $session->finish();
        $session->data = 'testing';
    }

    public function testUnsetFinishedSession()
    {
        $this->expectException(DomainSessionException::class);

        $storage = $this->createMock(DomainSessionStorageInterface::class);
        $storage->expects($this->once())->method('read')->willReturn(['data' => 'testing']);
        $storage->expects($this->once())->method('write');

        $session = new DomainSession('id', $storage);
        $session->start();
        $session->finish();
        unset($session->data);
    }
}
