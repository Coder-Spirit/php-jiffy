<?php


namespace Litipk\Jiffy;


/**
 * Trait MongoAdapter: Only to be used inside UniversalTimestamp.
 * @package Litipk\Jiffy
 */
trait MongoAdapter
{
    /**
     * @param \MongoDate $mongoDate
     * @return UniversalTimestamp
     */
    public static function fromMongoDate(\MongoDate $mongoDate)
    {
        return UniversalTimestamp::fromMillisecondsTimestamp(
            $mongoDate->sec*1000 + (int)($mongoDate->usec/1000)
        );
    }

    /**
     * @return \MongoDate
     */
    public function asMongoDate()
    {
        return new \MongoDate(
            (int)floor($this->millis/1000),
            1000*($this->millis%1000)
        );
    }
}
