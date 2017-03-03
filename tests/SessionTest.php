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
        $expires = new DateTimeImmutable('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated, $expires);

        $this->assertEquals('id', $session->getId());
        $this->assertFalse($session->isExpired());
        $this->assertEquals($created->getTimestamp(), $session->getCreated()->getTimestamp());
        $this->assertEquals($updated->getTimestamp(), $session->getUpdated()->getTimestamp());
        $this->assertEquals($expires->getTimestamp(), $session->getExpires()->getTimestamp());
    }

    public function testAccessors()
    {
        $id = $this->createMock(SessionId::class);

        $data = ['foo' => 'bar'];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expires = new DateTimeImmutable('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated, $expires);

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

    public function testRenew()
    {
        $id = $this->createMock(SessionId::class);

        $data = [];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $oldExpires = new DateTimeImmutable('+1 minutes', new DateTimeZone('UTC'));
        $newExpires = new DateTimeImmutable('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated, $oldExpires);
        $session->renew('PT3M');

        $this->assertEquals(
            $newExpires->getTimestamp(),
            $session->getExpires()->getTimestamp(),
            '',
            5
        );
    }

    public function testSerializable()
    {
        $id = $this->createMock(SessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = ['data' => 'testing'];

        $accessed = $created = $updated = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $expires = new DateTimeImmutable('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $accessed, $updated, $expires);

        $serializedSession = serialize($session);

        $other = unserialize($serializedSession);

        $this->assertEquals((string) $session->getId(), (string) $other->getId());
    }
}
