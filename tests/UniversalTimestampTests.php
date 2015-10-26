<?php


use Litipk\Jiffy\UniversalTimestamp;


class UniversalTimestampTests extends \PHPUnit_Framework_TestCase
{
    public function testNow()
    {
        $classicTs_A = time();
        $uTs_A = UniversalTimestamp::now();
        $classicTs_B = time();

        $this->assertGreaterThanOrEqual($classicTs_A, $uTs_A->asSeconds());

        $this->assertGreaterThanOrEqual($uTs_A->asSeconds(), $classicTs_B);

    }

    public function testFromDateTimeInterface()
    {
        $classicTs_A = time();
        $uTs_A = UniversalTimestamp::fromDateTimeInterface(new \DateTime());
        $classicTs_B = time();

        $this->assertGreaterThanOrEqual($classicTs_A, $uTs_A->asSeconds());
        $this->assertGreaterThanOrEqual($uTs_A->asSeconds(), $classicTs_B);
    }

    public function testFromMongoDate()
    {
        $uTs_A = UniversalTimestamp::now();
        $uTs_B = UniversalTimestamp::fromMongoDate(new \MongoDate());
        $uTs_C = UniversalTimestamp::now();

        $this->assertGreaterThanOrEqual(4, 5);

        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_B->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_B->asMilliseconds(), $uTs_C->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_C->asMilliseconds());
    }

    public function testFromSeconds()
    {
        $classicTs_A = time();
        $uTs_A = UniversalTimestamp::fromSecondsTimestamp(time());
        $classicTs_B = time();

        $this->assertGreaterThanOrEqual($classicTs_A, $uTs_A->asSeconds());
        $this->assertGreaterThanOrEqual($uTs_A->asSeconds(), $classicTs_B);
    }

    public function testFromMilliseconds()
    {
        $classicTs_A = 1445817008;
        $uTs_A = UniversalTimestamp::fromMillisecondsTimestamp(1445817008639);
        $classicTs_B = 1445817009;

        $this->assertGreaterThanOrEqual($classicTs_A, $uTs_A->asSeconds());
        $this->assertGreaterThanOrEqual($uTs_A->asSeconds(), $classicTs_B);
    }

    /**
     * @expectedException \Litipk\Jiffy\JiffyException
     * @expectedExceptionMessage The number of milliseconds and microseconds must be positive
     */
    public function testFromMilliseconds_WithNegativeValues()
    {
        $uTs = UniversalTimestamp::fromMillisecondsTimestamp(-1445817008639);
    }

    public function testIsGreaterThan()
    {
        $ts1 = UniversalTimestamp::now();
        $ts2 = UniversalTimestamp::now();

        $this->assertTrue($ts2->isGreaterThan($ts1));
        $this->assertFalse($ts1->isGreaterThan($ts2));
    }

    public function testAddSeconds()
    {
        $ts1 = UniversalTimestamp::now();
        $ts2 = $ts1->addSeconds(1);

        $this->assertTrue($ts2->isGreaterThan($ts1));
        $this->assertFalse($ts1->isGreaterThan($ts2));

        $this->assertEquals($ts1->asSeconds()+1, $ts2->asSeconds());
    }

    public function testAddMilliseconds()
    {
        $ts1 = UniversalTimestamp::now();
        $ts1Millis = $ts1->asMilliseconds();

        $ts2 = $ts1->addMilliseconds(37);

        $this->assertEquals($ts1Millis, $ts1->asMilliseconds()); // Checking immutability

        $this->assertTrue($ts2->isGreaterThan($ts1));
        $this->assertFalse($ts1->isGreaterThan($ts2));

        $this->assertEquals($ts1->asMilliseconds()+37, $ts2->asMilliseconds());
    }

    public function testAsMilliseconds()
    {
        $ts = UniversalTimestamp::now();

        $this->assertGreaterThanOrEqual($ts->asSeconds()*1000, $ts->asMilliseconds());
        $this->assertGreaterThan($ts->asMilliseconds(), ($ts->asSeconds()+1)*1000);
    }

    public function testGetRemainingMicroseconds()
    {
        $ts1 = UniversalTimestamp::now();
        $ts2 = UniversalTimestamp::now();

        $this->assertGreaterThanOrEqual(0, $ts1->getRemainingMicroseconds());
        $this->assertLessThan(1000, $ts1->getRemainingMicroseconds());

        if ($ts1->asMilliseconds() === $ts2->asMilliseconds()) {
            $this->assertTrue($ts2->getRemainingMicroseconds() > $ts1->getRemainingMicroseconds());
        }
    }

    public function testAsMongoDate()
    {
        $ts1 = UniversalTimestamp::fromMongoDate(new \MongoDate());
        $md1 = $ts1->asMongoDate();

        $this->assertTrue($md1 instanceof \MongoDate);
        $this->assertEquals($ts1->asSeconds(), $md1->sec);
        $this->assertEquals($ts1->asMilliseconds()%1000, $md1->usec/1000);
    }

    public function testToString()
    {
        $this->assertEquals('2015-10-26T01:00:46+0100', UniversalTimestamp::fromSecondsTimestamp(1445817646));
        $this->assertEquals('2015-10-26T01:00:47+0100', UniversalTimestamp::fromSecondsTimestamp(1445817647));
        $this->assertEquals('2015-10-26T01:01:47+0100', UniversalTimestamp::fromSecondsTimestamp(1445817707));
    }
}
