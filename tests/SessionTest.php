<?php
namespace Cadre\DomainSession;

use DateTime;
use DateTimeZone;

class SessionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $id = $this->createMock(SessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = [];

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $updated, $expires);

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

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $updated, $expires);

        $this->assertTrue($session->has('foo'));
        $this->assertEquals('bar', $session->get('foo'));

        $this->assertFalse($session->has('biz'));
        $this->assertEquals('default', $session->get('biz', 'default'));

        $session->set('biz', 'testing');

        $this->assertTrue($session->has('biz'));
        $this->assertEquals('testing', $session->get('biz', 'default'));

        $session->remove('biz');

        $this->assertFalse($session->has('biz'));
        $this->assertEquals('default', $session->get('biz', 'default'));
    }

    public function testUpdateLockedSession()
    {
        $session = Session::createWithId(
            SessionId::createWithNewValue()
        );

        $session->lock();

        $this->expectException(SessionException::class);

        $session->set('foo', 'bar');
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

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $oldExpires = new DateTime('+1 minutes', new DateTimeZone('UTC'));
        $newExpires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $updated, $oldExpires);
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

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new Session($id, $data, $created, $updated, $expires);

        $serializedSession = serialize($session);

        $other = unserialize($serializedSession);

        $this->assertEquals((string) $session->getId(), (string) $other->getId());
    }
}
