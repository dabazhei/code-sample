<?php

declare(strict_types=1);

namespace App\Utility;

/**
 * Class DateRanges
 * @package App\Utility
 */
class DateRanges
{

    public const DATE_FORMAT = 'Y-m-d';

    /**
     * @return array
     */
    public static function thisWeek(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('this week monday')),
            'end' => date(self::DATE_FORMAT, strtotime('this week sunday')),
        ];
    }

    /**
     * @return array
     */
    public static function thisMonth(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('first day of this month')),
            'end' => date(self::DATE_FORMAT, strtotime('last day of this month')),
        ];
    }

    /**
     * @return array
     */
    public static function thisYear(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('this year January 1st')),
            'end' => date(self::DATE_FORMAT, strtotime('this year December 31st')),
        ];
    }

    /**
     * @return array
     */
    public static function lastWeek(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('last week monday')),
            'end' => date(self::DATE_FORMAT, strtotime('last sunday')),
        ];
    }

    /**
     * @return array
     */
    public static function lastMonth(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('first day of last month')),
            'end' => date(self::DATE_FORMAT, strtotime('last day of last month')),
        ];
    }

    /**
     * @return array
     */
    public static function lastYear(): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime('last year January 1st')),
            'end' => date(self::DATE_FORMAT, strtotime('last year December 31st')),
        ];
    }

    /**
     * @return string
     */
    public static function yesterday(): string
    {
        return date(self::DATE_FORMAT, strtotime("-1 days"));
    }

    /**
     * @param int $nDays
     * @return array
     */
    public static function lastNDays(int $nDays): array
    {
        return [
            'start' => date(self::DATE_FORMAT, strtotime("- {$nDays} days")),
            'end' => date(self::DATE_FORMAT),
        ];
    }

}