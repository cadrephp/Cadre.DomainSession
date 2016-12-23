<?php
namespace Cadre\Domain_Session;

class IdFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testDoesNothing()
    {
        $idFactory = new IdFactory();

        $this->assertInternalType('string', $idFactory());
    }
}
