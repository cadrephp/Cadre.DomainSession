<?php
namespace Cadre\Domain_Session;

use DateTime;
use DateTimeZone;

class DomainSessionTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $id = $this->createMock(DomainSessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = [];

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new DomainSession($id, $data, $created, $updated, $expires);

        $this->assertEquals('id', $session->id());
        $this->assertFalse($session->isExpired());
        $this->assertEquals($created->getTimestamp(), $session->created()->getTimestamp());
        $this->assertEquals($updated->getTimestamp(), $session->updated()->getTimestamp());
        $this->assertEquals($expires->getTimestamp(), $session->expires()->getTimestamp());
    }

    public function testAccessors()
    {
        $id = $this->createMock(DomainSessionId::class);

        $data = ['foo' => 'bar'];

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new DomainSession($id, $data, $created, $updated, $expires);

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

    public function testRenew()
    {
        $id = $this->createMock(DomainSessionId::class);

        $data = [];

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $oldExpires = new DateTime('+1 minutes', new DateTimeZone('UTC'));
        $newExpires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new DomainSession($id, $data, $created, $updated, $oldExpires);
        $session->renew('PT3M');

        $this->assertEquals(
            $newExpires->getTimestamp(),
            $session->expires()->getTimestamp(),
            '',
            5
        );
    }

    public function testSerializable()
    {
        $id = $this->createMock(DomainSessionId::class);
        $id->expects($this->once())->method('__toString')->willReturn('id');

        $data = ['data' => 'testing'];

        $created = $updated = new DateTime('now', new DateTimeZone('UTC'));
        $expires = new DateTime('+3 minutes', new DateTimeZone('UTC'));

        $session = new DomainSession($id, $data, $created, $updated, $expires);

        $serializedSession = serialize($session);

        $other = unserialize($serializedSession);

        $this->assertEquals((string) $session->id(), (string) $other->id());
    }
}
