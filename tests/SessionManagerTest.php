<?php
namespace Cadre\DomainSession;

use DateTimeImmutable;
use DateTimeZone;
use Cadre\DomainSession\Storage\Memory;
use PHPUnit\Framework\TestCase;

class SessionManagerTest extends TestCase
{
    public function testNewSession()
    {
        $id = '';

        $storage = new Memory();
        $manager = new SessionManager($storage);

        $session = $manager->start($id);
        $session->foo = 'bar';
        $manager->finish($session);

        $this->assertEquals('bar', $storage->read($session->getId())->foo ?? 'default');
    }

    public function testExpiredSession()
    {
        $storage = new Memory();
        $manager = new SessionManager($storage);

        $session = $storage->createNew('PT3M');

        $reflectionClass = new \ReflectionClass(Session::class);
        $reflectionProperty = $reflectionClass->getProperty('expires');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($session, new DateTimeImmutable('-10 minutes', new DateTimeZone('UTC')));

        $storage->write($session);

        $id = $session->getId()->value();
        unset($session);

        $session = $manager->start($id);

        $this->assertNotEquals(bin2hex($id), bin2hex($session->getId()->value()));
    }
}
