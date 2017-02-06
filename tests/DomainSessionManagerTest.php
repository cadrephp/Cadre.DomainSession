<?php
namespace Cadre\Domain_Session;

use DateTime;
use DateTimeZone;

class DomainSessionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testNewSession()
    {
        $id = '';

        $storage = new DomainSessionStorageMemory();
        $manager = new DomainSessionManager($storage);

        $session = $manager->start($id);
        $session->set('foo', 'bar');
        $manager->finish($session);

        $this->assertEquals('bar', $storage->read($session->id())->get('foo', 'default'));
    }

    public function testExpiredSession()
    {
        $storage = new DomainSessionStorageMemory();
        $manager = new DomainSessionManager($storage);

        $session = $storage->createNew('PT3M');

        $reflectionClass = new \ReflectionClass(DomainSession::class);
        $reflectionProperty = $reflectionClass->getProperty('expires');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($session, new DateTime('-10 minutes', new DateTimeZone('UTC')));

        $storage->write($session);

        $id = $session->id()->value();
        unset($session);

        $session = $manager->start($id);

        $this->assertNotEquals(bin2hex($id), bin2hex($session->id()->value()));
    }
}
