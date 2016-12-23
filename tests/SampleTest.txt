<?php
namespace Cadre;

class SampleTest extends \PHPUnit_Framework_TestCase
{
    public function testWithoutName()
    {
        $sample = new Sample();

        $this->assertEquals('Hello, world.', (string) $sample);
    }

    public function testWithName()
    {
        $sample = new Sample('Andrew');

        $this->assertEquals('Hello, Andrew.', (string) $sample);
    }
}
