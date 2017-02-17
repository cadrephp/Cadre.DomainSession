<?php
namespace Cadre\DomainSession;

use DateTime;
use DateTimeZone;
use Cadre\DomainSession\Storage\Memory;

class SessionManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testNewSession()
    {
        $id = '';

        $storage = new Memory();
        $manager = new SessionManager($storage);

        $session = $manager->start($id);
        $session->set('foo', 'bar');
        $manager->finish($session);

        $this->assertEquals('bar', $storage->read($session->id())->get('foo', 'default'));
    }

    public function testExpiredSession()
    {
        $storage = new Memory();
        $manager = new SessionManager($storage);

        $session = $storage->createNew('PT3M');

        $reflectionClass = new \ReflectionClass(Session::class);
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
