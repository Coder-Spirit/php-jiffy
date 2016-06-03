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
final class UniversalTimestamp
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
        $dtU = (int)$dateTime->format('u');

        return new UniversalTimestamp(
            $dateTime->getTimestamp()*1000 + (int)floor($dtU/1000),
            $dtU % 1000
        );
    }

    /**
     * @param string $strTimestamp
     * @param string $tz
     * @return UniversalTimestamp
     */
    public static function fromStringTimestamp($strTimestamp, $tz = 'UTC')
    {
        return self::fromDateTimeInterface(new \DateTimeImmutable(
            $strTimestamp,
            ($tz instanceof \DateTimeZone) ? $tz : new \DateTimeZone($tz)
        ));
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
            return self::now();
        } elseif (is_int($dateObject)) {
            return self::fromMillisecondsTimestamp($dateObject);
        } elseif (is_string($dateObject)) {
            return self::fromStringTimestamp($dateObject);
        } elseif ($dateObject instanceof UniversalTimestamp) {
            return $dateObject;
        } elseif ($dateObject instanceof \DateTimeInterface) {
            return self::fromDateTimeInterface($dateObject);
        } elseif ($dateObject instanceof \MongoDate) {
            return self::fromMongoDate($dateObject);
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
        $copy = clone $this;
        $copy->millis += 1000*$seconds;

        return $copy;
    }

    /**
     * @param int $millis
     * @return UniversalTimestamp
     */
    public function addMilliseconds($millis)
    {
        $copy = clone $this;
        $copy->millis += $millis;

        return $copy;
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
        $dateTime = new \DateTimeImmutable();
        $dateTime = $dateTime
            ->setTimestamp($this->asSeconds())
            ->setTimezone(is_string($tz) ? new \DateTimeZone($tz) : $tz);

        return new \DateTimeImmutable(
            $dateTime->format('Y-m-d\TH:i:s').'.'.
            sprintf("%'.03d", $this->millis%1000).sprintf("%'.03d", $this->micros).
            $dateTime->format('O')
        );
    }

    /**
     * @param string $format
     * @param string|\DateTimeZone $tz
     * @param bool $showMillis
     * @param bool $stripTz
     * @return string
     */
    public function asFormattedString(
        $format = \DateTime::ISO8601, $tz = 'UTC', $showMillis = false, $stripTz = false
    )
    {
        $r = $this->asDateTimeInterface($tz)->format($format);

        if (\DateTime::ISO8601 === $format && $showMillis) {
            $rParts = preg_split('/\+/', $r);
            $r = $rParts[0].'.'.((string)$this->millis%1000).($stripTz?'':('+'.$rParts[1]));
        } elseif (\DateTime::ISO8601 === $format && $stripTz) {
            $rParts = preg_split('/\+/', $r);
            $r = $rParts[0];
        }

        return $r;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->asFormattedString();
    }
}
