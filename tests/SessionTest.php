<?php
namespace Cadre\DomainSession;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class SessionTest extends TestCase
{
    public function testCreation()
    {
        $id = $this->createMock(SessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = [];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated);

        $this->assertEquals('id', $session->getId());
        $this->assertEquals($created->getTimestamp(), $session->getCreated()->getTimestamp());
        $this->assertEquals($updated->getTimestamp(), $session->getUpdated()->getTimestamp());
    }

    public function testAccessors()
    {
        $id = $this->createMock(SessionId::class);

        $data = ['foo' => 'bar'];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated);

        $this->assertTrue(isset($session->foo));
        $this->assertEquals('bar', $session->foo);

        $this->assertFalse(isset($session->biz));
        $this->assertEquals('default', $session->biz ?? 'default');

        $session->biz = 'testing';

        $this->assertTrue(isset($session->biz));
        $this->assertEquals('testing', $session->biz ?? 'default');

        unset($session->biz);

        $this->assertFalse(isset($session->biz));
        $this->assertEquals('default', $session->biz ?? 'default');
    }

    public function testUpdateLockedSession()
    {
        $session = Session::createWithId(
            SessionId::createWithNewValue()
        );

        $session->lock();

        $this->expectException(SessionException::class);

        $session->foo = 'bar';
    }

    public function testRegenerateIdFromLockedSession()
    {
        $session = Session::createWithId(
            SessionId::createWithNewValue()
        );

        $session->lock();

        $this->expectException(SessionException::class);

        $session->getId()->regenerate();
    }

    public function testSerializable()
    {
        $id = $this->createMock(SessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = ['data' => 'testing'];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated);

        $serializedSession = serialize($session);

        $other = unserialize($serializedSession);

        $this->assertEquals((string) $session->getId(), (string) $other->getId());
    }
}
