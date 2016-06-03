<?php


use Litipk\Jiffy\UniversalTimestamp;


class MongodbAdapterTests extends \PHPUnit_Framework_TestCase
{
    public function testFromMongoDate()
    {
        if (!extension_loaded('mongodb')) return;

        $uTs_A = UniversalTimestamp::now();
        $uTs_B = UniversalTimestamp::fromMongodbUTCDateTime(
            new \MongoDB\BSON\UTCDatetime($uTs_A->asMilliseconds()+1)
        );
        $uTs_C = UniversalTimestamp::fromMillisecondsTimestamp($uTs_A->asMilliseconds()+2);

        $this->assertGreaterThanOrEqual(4, 5);

        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_B->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_B->asMilliseconds(), $uTs_C->asMilliseconds());
        $this->assertGreaterThanOrEqual($uTs_A->asMilliseconds(), $uTs_C->asMilliseconds());
    }

    public function testAsMongoDate()
    {
        if (!extension_loaded('mongodb')) return;

        $ts1 = UniversalTimestamp::fromMongodbUTCDateTime(
            new \MongoDB\BSON\UTCDatetime(UniversalTimestamp::now()->asMilliseconds())
        );
        $md1 = $ts1->asMongodbUTCDateTime();

        $this->assertTrue($md1 instanceof \MongoDB\BSON\UTCDatetime);
        $this->assertEquals($ts1->asSeconds(), (int)floor(((int)$md1->__toString())/1000));
        $this->assertEquals($ts1->asMilliseconds()%1000, ((int)$md1->__toString())%1000);
    }
}
