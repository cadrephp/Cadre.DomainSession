<?php
namespace Cadre\DomainSession;

use PHPUnit\Framework\TestCase;

class SessionIdTest extends TestCase
{
    public function testCreation()
    {
        $value = random_bytes(16);
        $id = new SessionId($value);

        $this->assertEquals($value, $id->value());
        $this->assertEquals($value, $id->startingValue());
        $this->assertEquals($value, (string) $id);
        $this->assertFalse($id->hasUpdatedValue());
    }

    public function testRegenerate()
    {
        $value = random_bytes(16);
        $id = new SessionId($value);
        $id->regenerate(32);

        $this->assertNotEquals($value, $id->value());
        $this->assertEquals($value, $id->startingValue());
        $this->assertNotEquals($value, (string) $id);
        $this->assertEquals(64, strlen($id));
        $this->assertTrue($id->hasUpdatedValue());
    }

    public function testWithNewValue()
    {
        $id = SessionId::createWithNewValue(8);

        $this->assertNotEquals('', $id->value());
        $this->assertEquals('', $id->startingValue());
        $this->assertEquals(16, strlen($id));
        $this->assertTrue($id->hasUpdatedValue());
    }
}
