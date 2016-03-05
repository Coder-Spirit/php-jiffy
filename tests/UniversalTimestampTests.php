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

    public function testFromWhatever()
    {
        $ts1 = UniversalTimestamp::fromWhatever(1445817008639);
        $ts2 = UniversalTimestamp::fromWhatever(new \DateTime());
        $ts3 = UniversalTimestamp::fromWhatever(null);

        $this->assertTrue($ts1 instanceof UniversalTimestamp);
        $this->assertTrue($ts2 instanceof UniversalTimestamp);
        $this->assertTrue($ts3 instanceof UniversalTimestamp);

        if (extension_loaded('mongo')) {
            $ts4 = UniversalTimestamp::fromWhatever(new \MongoDate());
            $this->assertTrue($ts4 instanceof UniversalTimestamp);
        }
        if (extension_loaded('mongodb')) {
            $ts4 = UniversalTimestamp::fromWhatever(
                new \MongoDB\BSON\UTCDatetime(UniversalTimestamp::now()->asMilliseconds())
            );
            $this->assertTrue($ts4 instanceof UniversalTimestamp);
        }
    }

    /**
     * @expectedException \Litipk\Jiffy\JiffyException
     * @expectedExceptionMessage The provided value cannot be interpreted as a timestamp
     */
    public function testFromWhatever_WithInvalidTimestamp()
    {
        $ts1 = UniversalTimestamp::fromWhatever("Hello");
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

    public function testToString()
    {
        $ts1 = UniversalTimestamp::fromMillisecondsTimestamp(1445817646455, 378);

        $d1 = new \DateTime($ts1->asFormattedString());
        $d2 = new \DateTime((string)$ts1);

        $ts2 = UniversalTimestamp::fromDateTimeInterface($d1);
        $ts3 = UniversalTimestamp::fromDateTimeInterface($d2);

        $this->assertEquals($ts1->asSeconds(), $ts2->asSeconds());
        $this->assertEquals($ts1->asMilliseconds(), $ts2->asMilliseconds());
        $this->assertEquals($ts1->asSeconds(), $ts3->asSeconds());
        $this->assertEquals($ts1->asMilliseconds(), $ts3->asMilliseconds());
    }

    public function testAsFormattedString_WithSpecialSettings()
    {
        $ts1 = UniversalTimestamp::fromMillisecondsTimestamp(1445817646571, 473);

        $this->assertEquals(
            '2015-10-26T00:00:46.571+0000',
            $ts1->asFormattedString(UniversalTimestamp::ISO8601_WITH_MILLISECONDS, 'UTC')
        );
        $this->assertEquals(
            '2015-10-26T00:00:46.571',
            $ts1->asFormattedString(UniversalTimestamp::ISO8601_WITH_MILLISECONDS_WITHOUT_TZ, 'UTC')
        );
        $this->assertEquals(
            '2015-10-26T00:00:46.571473+0000',
            $ts1->asFormattedString(UniversalTimestamp::ISO8601_WITH_MICROSECONDS, 'UTC')
        );
        $this->assertEquals(
            '2015-10-26T00:00:46.571473',
            $ts1->asFormattedString(UniversalTimestamp::ISO8601_WITH_MICROSECONDS_WITHOUT_TZ, 'UTC')
        );
    }
}
