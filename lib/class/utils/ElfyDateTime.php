<?php
  namespace utils;

  use exceptions\InvalidParameterException;

  class ElfyDateTime {
    /**
     * Parses the specified type into a DateTime object.
     * Returns null if the input is null, zero or empty.
     *
     * @param $arbitraryType
     * @return \DateTime|null
     * @throws InvalidParameterException
     */
    public static function parse($arbitraryType) {
      if ($arbitraryType instanceof \DateTime) {
        return $arbitraryType;
      }

      if ($arbitraryType === 0 ||
          $arbitraryType === null ||
          empty($arbitraryType)) {
        return null;
      }

      if (is_string($arbitraryType)) {
        return new \DateTime($arbitraryType);
      }

      throw new InvalidParameterException('Unrecognised date time format '.$arbitraryType);
    }

    /**
     * Parses the specified type into a DateTime, or returns today's date if it fails.
     *
     * @param $arbitraryType
     * @return \DateTime
     */
    public static function parseOrToday($arbitraryType)  {
      try {
        $date = self::parse($arbitraryType);
      } catch (InvalidParameterException $ex) {
        $date = null;
      }

      if (! ($date instanceof \DateTime)) {
        return self::now();
      }

      return $date;
    }

    /**
     * Returns an instance of a DateTime object with today's date.
     * @return \DateTime
     */
    public static function now() {
      return new \DateTime();
    }

    public static function toLongDateString(\DateTime $date) {
      return $date->format('Y-m-d H:i');
    }

    public static function toShortDateString(\DateTime $date) {
      return $date->format('Y-m-d');
    }
  }
