<?php

namespace Dark\Dummy\Date;

use DateTime;
use JsonSerializable;

/**
 * 
 */
class DarkDummyDate extends DateTime implements JsonSerializable
{
  const MONDAY = 1;
  const TUESDAY = 2;
  const WEDNESDAY = 3;
  const THURSDAY = 4;
  const FRIDAY = 5;
  const SATURDAY = 6;
  const SUNDAY = 7;

  const YEARS_PER_CENTURY = 100;
  const YEARS_PER_DECADE = 10;
  const MONTHS_PER_YEAR = 12;
  const MONTHS_PER_QUARTER = 3;
  const MONTHS_PER_TRIMESTER = 4;
  const WEEKS_PER_YEAR = 52;
  const DAYS_PER_WEEK = 7;
  const SECONDS_PER_MINUTE = 60;
  const MINUTES_PER_HOUR = 60;


  public function __construct(string $date = 'today', \DateTimeZone $timezone = null)
  {
    parent::__construct(self::stripTime($date), $timezone);
  }

  public static function create($year, $month, $day, $hour = 0, $minute = 0, $second = 0): self
  {
    return new self("$year-$month-$day $hour:$minute:$second");
  }

  public function __toString()
  {
    return $this->format('YmdHis');
  }

  public function setTimeFromTimeString($time)
  {
    $temp = explode(":", $time);
    return $this->setTime($temp[0], $temp[1], $temp[2]);
  }

  public function isoFormart()
  {
    return $this->format('jS F, Y \a\\t g:ia');
  }

  static public function now(): self
  {
    return new self();
  }

  static public function createFromFormat($format, $strDateTime, $tz = 'utc')
  {
    $dt = DateTime::createFromFormat($format, $strDateTime, new \DateTimeZone($tz));
    return new DarkDummyDate($dt->format($format));
  }

  public function between($date1, $date2, $equal = true): bool
  {
    if ($date1->greaterThan($date2)) {
      $temp = $date1;
      $date1 = $date2;
      $date2 = $temp;
    }
    if ($equal) {
      return $this->greaterThanOrEqualTo($date1) && $this->lessThanOrEqualTo($date2);
    }
    return $this->greaterThan($date1) && $this->lessThan($date2);
  }

  public function greaterThan($date): bool
  {
    return $this > $date;
  }

  public function greaterThanOrEqualTo($date): bool
  {
    return $this >= $date;
  }

  public function lessThan($date): bool
  {
    return $this < $date;
  }
  public function lessThanOrEqualTo($date): bool
  {
    return $this <= $date;
  }

  public function equalTo($date): bool
  {
    return $this == $date;
  }

  public function eq($date): bool
  {
    return $this->equalTo($date);
  }

  public function jsonSerialize()
  {
    return [
      'DarkDummyDate' => [
        'date' => $this->format('Y-m-d H:i:s'),
        'TimeZone' => $this->getTimezone()->getName()
      ]
    ];
  }

  static public function parse($time = null, $tz = null)
  {
    return new static($time, $tz);
  }

  public function __get($property)
  {
    switch ($property) {
      case 'timestamp':
        return $this->getTimestamp();

      case 'year':
        return (int) $this->format('Y');
      case 'month':
        return (int) $this->format('m');
      case 'day':
        return (int) $this->format('d');
      case 'hour':
        return (int) $this->format('H');
      case 'minute':
        return (int) $this->format('i');
      case 'second':
        return (int) $this->format('s');
    }
  }

  public function __set($property, $value)
  {
    static $readonly = [
      'quarter', 'week', 'year_day', 'weekday',
      'tomorrow', 'yesterday', 'utc', 'local'
    ];

    switch ($property) {
      case 'year':
      case 'month':
      case 'day':
      case 'hour':
      case 'minute':
      case 'second':
        $this->change([$property => $value]);
        return;
    }
  }

  public function change(array $options, $cascade = false)
  {
    static $default_options = [

      'year' => null,
      'month' => null,
      'day' => null,
      'hour' => null,
      'minute' => null,
      'second' => null

    ];

    $options = array_intersect_key($options + $default_options, $default_options);

    $year = null;
    $month = null;
    $day = null;
    $hour = null;
    $minute = null;
    $second = null;

    extract($options);

    if ($cascade) {
      if ($hour !== null && $minute === null) {
        $minute = 0;
      }

      if ($minute !== null && $second === null) {
        $second = 0;
      }
    }

    if ($year !== null || $month !== null || $day !== null) {
      $this->setDate(
        $year === null ? $this->year : $year,
        $month === null ? $this->month : $month,
        $day === null ? $this->day : $day
      );
    }

    if ($hour !== null || $minute !== null || $second !== null) {
      $this->setTime(
        $hour === null ? $this->hour : $hour,
        $minute === null ? $this->minute : $minute,
        $second === null ? $this->second : $second
      );
    }

    return $this;
  }

  public function diffInYears($date = null, $absolute = true)
  {
    return (int) $this->diff($date, $absolute)->format('%r%y');
  }

  public function diffInMonths($date = null, $absolute = true)
  {
    return $this->diffInYears($date, $absolute) * static::MONTHS_PER_YEAR + (int) $this->diff($date, $absolute)->format('%r%m');
  }

  public function diffInWeeks($date = null, $absolute = true)
  {
    return (int) ($this->diffInDays($date, $absolute) / static::DAYS_PER_WEEK);
  }

  public function diffInDays($date = null, $absolute = true)
  {
    return (int) $this->diff($date, $absolute)->format('%r%a');
  }

  public function diffInHours($date = null, $absolute = true)
  {
    return (int) ($this->diffInSeconds($date, $absolute) / static::SECONDS_PER_MINUTE / static::MINUTES_PER_HOUR);
  }

  public function diffInMinutes($date = null, $absolute = true)
  {
    return (int) ($this->diffInSeconds($date, $absolute) / static::SECONDS_PER_MINUTE);
  }

  public function diffInSeconds($date = null, $absolute = true)
  {
    $diff = $this->diff($date);

    $value = ((($diff->days * static::HOURS_PER_DAY) +
      $diff->h) * static::MINUTES_PER_HOUR +
      $diff->i) * static::SECONDS_PER_MINUTE +
      $diff->s;
    return $absolute || !$diff->invert ? $value : -$value;
  }

  public function addYear($num = 1)
  {
    $this->year = $this->year + $num;
  }

  private static function stripTime(string $dateTime)
  {
    if (in_array($dateTime, ['today', 'now', ''], true)) {
      return 'today';
    }
    if ('@' === substr($dateTime, 0, 1)) {
      return date('Y-m-d 00:00:00', (int) substr($dateTime, 1));
    }
    return preg_replace('/\d{1,2}:\d{1,2}(?::\d{1,2}(?:\.\d+)?)?/', '00:00:00', $dateTime);
  }
}
