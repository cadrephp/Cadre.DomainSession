<?php
namespace Cadre\DomainSession;

use DateTimeImmutable;
use DateTimeZone;
use Cadre\DomainSession\Storage\Memory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class SessionManagerTest extends TestCase
{
    public function testNewSession()
    {
        $id = '';

        $storage = new Memory('PT3M');
        $manager = new SessionManager($storage);

        $session = $manager->start($id);
        $session->foo = 'bar';
        $manager->finish($session);

        $this->assertEquals('bar', $storage->read($session->getId())->foo ?? 'default');
    }

    public function testExpiredSession()
    {
        $storage = new Memory('PT3M');
        $manager = new SessionManager($storage);

        $session = $storage->createNew();
        $id = $session->getId()->value();
        $storage->write($session);
        unset($session);

        $reflectionClass = new \ReflectionClass(Memory::class);
        $reflectionProperty = $reflectionClass->getProperty('mtime');
        $reflectionProperty->setAccessible(true);
        $mtime = [$id => new DateTimeImmutable('-10 minutes', new DateTimeZone('UTC'))];
        $reflectionProperty->setValue($storage, $mtime);

        $session = $manager->start($id);

        $this->assertNotEquals($id, $session->getId()->value());
    }
}
