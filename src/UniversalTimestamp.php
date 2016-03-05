<?php


namespace Litipk\Jiffy;


if (extension_loaded('mongo') && extension_loaded('mongodb')) {
    trait TsExtension { use MongoAdapter; use MongodbAdapter; };
} elseif (extension_loaded('mongo')) {
    trait TsExtension { use MongoAdapter; };
} elseif (extension_loaded('mongodb')) {
    trait TsExtension { use MongodbAdapter; };
} else {
    trait TsExtension {};
}


/**
 * Class UniversalTimestamp
 * @package Litipk\Jiffy
 */
class UniversalTimestamp
{
    const ISO8601_WITH_MILLISECONDS = '_ISO8601_WITH_MILLIS_';
    const ISO8601_WITH_MILLISECONDS_WITHOUT_TZ = '_ISO8601_WITH_MILLIS_WITHOUT_TZ';
    const ISO8601_WITH_MICROSECONDS = 'Y-m-d\TH:i:s.uO';
    const ISO8601_WITH_MICROSECONDS_WITHOUT_TZ = 'Y-m-d\TH:i:s.u';

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
        $dtU = (int)$dateTime->format('u');

        return new UniversalTimestamp(
            $dateTime->getTimestamp()*1000 + (int)floor($dtU/1000),
            $dtU % 1000
        );
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
     * @param string|\DateTimeZone $tz
     * @return \DateTimeImmutable
     */
    public function asDateTimeInterface($tz = 'UTC')
    {
        $dateTime = new \DateTimeImmutable('@'.((string)$this->asSeconds()));
        $dateTime = $dateTime->setTimezone(is_string($tz) ? new \DateTimeZone($tz) : $tz);

        return new \DateTimeImmutable(
            $dateTime->format('Y-m-d\TH:i:s').'.'.
            sprintf("%03d", $this->millis%1000).sprintf("%03d", $this->micros).
            $dateTime->format('O')
        );
    }

    /**
     * @param string $format
     * @param string|\DateTimeZone $tz
     * @return string
     */
    public function asFormattedString($format = self::ISO8601_WITH_MICROSECONDS, $tz = 'UTC')
    {
        if (self::ISO8601_WITH_MILLISECONDS === $format) {
            $rParts = preg_split('/\+/', $this->asDateTimeInterface($tz)->format(\DateTime::ISO8601));
            return $rParts[0].'.'.((string)$this->millis%1000).'+'.$rParts[1];
        } elseif (self::ISO8601_WITH_MILLISECONDS_WITHOUT_TZ === $format) {
            $rParts = preg_split('/\+/', $this->asDateTimeInterface($tz)->format(\DateTime::ISO8601));
            return $rParts[0].'.'.((string)$this->millis%1000);
        } else {
            return $this->asDateTimeInterface($tz)->format($format);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asFormattedString();
    }
}
