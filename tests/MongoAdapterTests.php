<?php


use Litipk\Jiffy\UniversalTimestamp;


class MongoAdapterTests extends \PHPUnit_Framework_TestCase
{
    public function testFromMongoDate()
    {
        if (!extension_loaded('mongo')) return;

        $uTs_A = UniversalTimestamp::now();
        $uTs_B = UniversalTimestamp::fromMongoDate(new \MongoDate());
        $uTs_C = UniversalTimestamp::now();

        $this->assertGreaterThanOrEqual(4, 5);

        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_B->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_B->asMilliseconds(), $uTs_C->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_C->asMilliseconds());
    }

    public function testAsMongoDate()
    {
        if (!extension_loaded('mongo')) return;

        $ts1 = UniversalTimestamp::fromMongoDate(new \MongoDate());
        $md1 = $ts1->asMongoDate();

        $this->assertTrue($md1 instanceof \MongoDate);
        $this->assertEquals($ts1->asSeconds(), $md1->sec);
        $this->assertEquals($ts1->asMilliseconds()%1000, $md1->usec/1000);
    }
}
