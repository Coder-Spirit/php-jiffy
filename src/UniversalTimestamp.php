<?php


namespace Litipk\Jiffy;


if (!extension_loaded('mongo')) {
    trait TsExtension {};
} else {
    trait TsExtension { use MongoAdapter; };
}


/**
 * Class UniversalTimestamp
 * @package Litipk\Jiffy
 */
class UniversalTimestamp
{
    use TsExtension;

    /** @var int */
    private $millis;

    /** @var int */
    private $micros;

    /**
     * Constructor.
     *
     * @param integer $millisSinceEpoch
     * @param integer $micros
     */
    private function __construct($millisSinceEpoch, $micros = 0)
    {
        if ($millisSinceEpoch < 0 || $micros < 0) {
            throw new JiffyException('The number of milliseconds and microseconds must be positive');
        }

        $this->millis = $millisSinceEpoch + (int)($micros/1000);
        $this->micros = $micros % 1000;
    }

    /**
     * @return UniversalTimestamp
     */
    public static function now()
    {
        $ts_parts = explode(' ', microtime());

        return new UniversalTimestamp(
            (int)floor($ts_parts[0]*1000) + (int)$ts_parts[1]*1000,  // Millis
            ((int)round($ts_parts[0]*1000000))%1000                  // Micros
        );
    }

    /**
     * @param \DateTimeInterface $dateTime
     * @return UniversalTimestamp
     */
    public static function fromDateTimeInterface(\DateTimeInterface $dateTime)
    {
        return new UniversalTimestamp($dateTime->getTimestamp()*1000, 0);
    }

    /**
     * @param int $secondsSinceEpoch
     * @return UniversalTimestamp
     */
    public static function fromSecondsTimestamp($secondsSinceEpoch)
    {
        return new UniversalTimestamp($secondsSinceEpoch*1000);
    }

    /**
     * @param int $millisSinceEpoch
     * @param int $micros
     * @return UniversalTimestamp
     */
    public static function fromMillisecondsTimestamp($millisSinceEpoch, $micros = 0)
    {
        return new UniversalTimestamp($millisSinceEpoch, $micros);
    }

    /**
     * @param mixed $dateObject   If it's an integer, then it's understood as milliseconds since epoch
     * @return UniversalTimestamp
     */
    public static function fromWhatever($dateObject) {
        if (null === $dateObject) {
            return static::now();
        } elseif (is_int($dateObject)) {
            return static::fromMillisecondsTimestamp($dateObject);
        } elseif ($dateObject instanceof UniversalTimestamp) {
            return $dateObject;
        } elseif ($dateObject instanceof \DateTimeInterface) {
            return static::fromDateTimeInterface($dateObject);
        } elseif ($dateObject instanceof \MongoDate) {
            return static::fromMongoDate($dateObject);
        } else {
            throw new JiffyException('The provided value cannot be interpreted as a timestamp');
        }
    }

    /**
     * @param UniversalTimestamp $otherTimestamp
     * @return boolean
     */
    public function isGreaterThan(UniversalTimestamp $otherTimestamp)
    {
        return (
            $this->millis > $otherTimestamp->millis ||
            $this->millis === $otherTimestamp->millis && $this->micros > $otherTimestamp->micros
        );
    }

    /**
     * @param int $seconds
     * @return UniversalTimestamp
     */
    public function addSeconds($seconds)
    {
        return new UniversalTimestamp($this->millis + 1000*$seconds, $this->micros);
    }

    /**
     * @param int $millis
     * @return UniversalTimestamp
     */
    public function addMilliseconds($millis)
    {
        return new UniversalTimestamp($this->millis + $millis, $this->micros);
    }

    /**
     * @return int
     */
    public function asSeconds()
    {
        return (int)floor($this->millis/1000);
    }

    /**
     * @return int
     */
    public function asMilliseconds()
    {
        return $this->millis;
    }

    /**
     * @return int
     */
    public function getRemainingMicroseconds()
    {
        return $this->micros;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function asDateTimeInterface()
    {
        $dateTime = new \DateTimeImmutable();
        return $dateTime->setTimestamp($this->asSeconds());
    }

    /**
     * @param string $format
     * @return string
     */
    public function asFormattedString($format = \DateTime::ISO8601)
    {
        return $this->asDateTimeInterface()->format($format);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asFormattedString();
    }
}
