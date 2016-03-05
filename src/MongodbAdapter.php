<?php


namespace Litipk\Jiffy;


/**
 * Trait MongodbAdapter: Only to be used inside UniversalTimestamp.
 * @package Litipk\Jiffy
 */
trait MongodbAdapter
{
    /**
     * @param \MongoDB\BSON\UTCDatetime $mongoDate
     * @return UniversalTimestamp
     */
    public static function fromMongodbUTCDateTime(\MongoDB\BSON\UTCDatetime $mongoDate)
    {
        return UniversalTimestamp::fromMillisecondsTimestamp((int)$mongoDate->__toString());
    }

    /**
     * @return \MongoDate
     */
    public function asMongodbUTCDateTime()
    {
        return new \MongoDB\BSON\UTCDatetime($this->millis);
    }
}
