<?php
/**
 * @copyright CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @author CONTENT CONTROL GmbH, http://www.contentcontrol-berlin.de
 * @license https://opensource.org/licenses/MIT MIT
 */

namespace OpenPsa\Ranger;

use IntlDateFormatter;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RangerTest extends TestCase
{
    /**
     * @dataProvider providerDateRange
     */
    #[DataProvider('providerDateRange')]
    public function testDateRange($language, $start, $end, $expected)
    {
        $formatter = new Ranger($language);
        $this->assertEquals($expected, $formatter->format($start, $end));
    }

    public static function providerDateRange()
    {
        return [
            ['en', '2013-10-05', '2013-10-20', 'Oct 5–20, 2013'],
            ['en', '2013-10-05', '2013-11-20', 'Oct 5 – Nov 20, 2013'],
            ['en', '2012-10-05', '2013-10-20', 'Oct 5, 2012 – Oct 20, 2013'],
            ['de', '2012-10-05', '2012-10-20', '05.–20.10.2012'],
            ['de', '2012-10-05', '2012-11-20', '05.10.–20.11.2012'],
            ['de', '2012-10-05', '2013-10-20', '05.10.2012 – 20.10.2013']
        ];
    }

    /**
     * @dataProvider providerDateTimeRange
     */
    #[DataProvider('providerDateTimeRange')]
    public function testDateTimeRange($language, $start, $end, $expected)
    {
        $formatter = new Ranger($language);
        $formatter->setTimeType(IntlDateFormatter::SHORT);
        $this->assertEquals($expected, $formatter->format($start, $end));
    }

    private static function get_space() : string
    {
        if (version_compare(INTL_ICU_VERSION, '72.1', '<')) {
            return ' '; // Space (U+0020)
        }
        return ' '; // Narrow non-breaking space (U+202F)
    }

    public static function providerDateTimeRange()
    {
        $space = self::get_space();
        return [
            ['en', '2013-10-05 01:01:01', '2013-10-20 00:00:00', 'Oct 5, 2013, 1:01' . $space . 'AM – Oct 20, 2013, 12:00' . $space . 'AM'],
            ['en', '2013-10-05 10:00:01', '2013-10-05 13:30:00', 'Oct 5, 2013, 10:00' . $space . 'AM – 1:30' . $space . 'PM'],
            ['de', '2013-10-05 01:01:01', '2013-10-20 00:00:00', '05.10.2013, 01:01 – 20.10.2013, 00:00'],
            ['de', '2013-10-05 10:00:01', '2013-10-05 13:30:00', '05.10.2013, 10:00 – 13:30'],
        ];
    }

    /**
     * @dataProvider providerFullDateRange
     */
    #[DataProvider('providerFullDateRange')]
    public function testFullDateRange($language, $start, $end, $expected)
    {
        $formatter = new Ranger($language);
        $formatter->setDateType(IntlDateFormatter::FULL);
        $this->assertEquals($expected, $formatter->format($start, $end));
    }

    public static function providerFullDateRange()
    {
        return [
            ['en', '2013-10-05', '2013-10-20', 'Saturday, October 5 – Sunday, October 20, 2013'],
            ['en', '2013-10-05', '2013-11-20', 'Saturday, October 5 – Wednesday, November 20, 2013'],
            ['en', '2012-10-05', '2013-10-20', 'Friday, October 5, 2012 – Sunday, October 20, 2013'],
            ['de', '2012-10-05', '2012-10-20', 'Freitag, 5. – Samstag, 20. Oktober 2012'],
            ['de', '2012-10-05', '2012-11-20', 'Freitag, 5. Oktober – Dienstag, 20. November 2012'],
            ['de', '2012-10-05', '2013-10-20', 'Freitag, 5. Oktober 2012 – Sonntag, 20. Oktober 2013']
        ];
    }

    /**
     * @dataProvider providerShortDateRange
     */
    #[DataProvider('providerShortDateRange')]
    public function testShortDateRange($language, $start, $end, $expected)
    {
        $formatter = new Ranger($language);
        $formatter->setDateType(IntlDateFormatter::SHORT);
        $this->assertEquals($expected, $formatter->format($start, $end));
    }

    public static function providerShortDateRange()
    {
        return [
            ['en', '2012-10-05', '2013-10-20', '10/5/12 – 10/20/13'],
            ['en', '2012-10-05', '2012-10-05', '10/5/12'],
            ['de', '2012-10-05', '2012-10-20', '05.–20.10.12'],
            ['de', '2012-10-05', '2012-11-20', '05.10.–20.11.12'],
            ['de', '2012-10-05', '2012-10-05', '05.10.12'],
            ['de', '2012-10-05 00:00:01', '2012-10-05 23:59:59', '05.10.12'],
            ['de', '2012-10-05', '2013-10-20', '05.10.12 – 20.10.13']
        ];
    }

    public function testCustomOptions()
    {
        $ranger = new Ranger('en');
        $ranger
            ->setRangeSeparator(' -- ')
            ->setDateTimeSeparator(': ')
            ->setDateType(IntlDateFormatter::LONG)
            ->setTimeType(IntlDateFormatter::SHORT);

        $formatted = $ranger->format('2013-10-05 10:00:01', '2013-10-05 13:30:00');
        $space = self::get_space();
        $this->assertEquals('October 5, 2013: 10:00' . $space . 'AM -- 1:30' . $space . 'PM', $formatted);
    }

    public function testEscapeCharParsing()
    {
        $ranger = new Ranger('en');
        $ranger
            ->setRangeSeparator(' and ')
            ->setDateTimeSeparator(', between ')
            ->setDateType(IntlDateFormatter::LONG)
            ->setTimeType(IntlDateFormatter::SHORT);

        $formatted = $ranger->format('2013-10-05 10:00:01', '2013-10-05 13:30:00');
        $space = self::get_space();
        $this->assertEquals('October 5, 2013, between 10:00' . $space . 'AM and 1:30' . $space . 'PM', $formatted);
    }

    public function testDateTime()
    {
        $ranger = new Ranger('en');
        $start = new DateTime('2013-10-05');
        $end = new DateTime('2013-10-20');

        $formatted = $ranger->format($start, $end);
        $this->assertEquals('Oct 5–20, 2013', $formatted);
    }

    public function testDateTimeImmutable()
    {
        $ranger = new Ranger('en');
        $start = new DateTimeImmutable('2013-10-05');
        $end = new DateTimeImmutable('2013-10-20');

        $formatted = $ranger->format($start, $end);
        $this->assertEquals('Oct 5–20, 2013', $formatted);
    }

    public function testTimestamp()
    {
        $ranger = new Ranger('en');
        $formatted = $ranger->format(1380931200, 1382227200);
        $this->assertEquals('Oct 5–20, 2013', $formatted);
    }

    public function testTimestampTimezone()
    {
        $backup = date_default_timezone_get();
        if (!date_default_timezone_set('Europe/Berlin')) {
            $this->markTestSkipped("Couldn't set timezone");
        }
        $ranger = new Ranger('de');
        $ranger->setTimeType(IntlDateFormatter::SHORT);
        $formatted = $ranger->format(1457478001, 1457481600);
        date_default_timezone_set($backup);
        $this->assertEquals('09.03.2016, 00:00 – 01:00', $formatted);
    }

    public function testOffsetTimezone()
    {
        $backup = date_default_timezone_get();
        if (!date_default_timezone_set('UTC')) {
            $this->markTestSkipped("Couldn't set timezone");
        }
        $start = new DateTime('2013-10-05');
        $end = new DateTime('2013-10-20');
        $tz = new \DateTimeZone('-0500');
        $start->setTimezone($tz);
        $end->setTimezone($tz);
        $ranger = new Ranger('en');
        $ranger->setTimeType(IntlDateFormatter::SHORT);
        $formatted = $ranger->format($start, $end);
        date_default_timezone_set($backup);
        $space = self::get_space();
        $this->assertEquals('Oct 4, 2013, 7:00' . $space . 'PM – Oct 19, 2013, 7:00' . $space . 'PM', $formatted);
    }

    /**
     * @dataProvider providerNoDate
     */
    #[DataProvider('providerNoDate')]
    public function testNoDate($language, $start, $end, $expected)
    {
        $formatter = new Ranger($language);
        $formatter
            ->setDateType(IntlDateFormatter::NONE)
            ->setTimeType(IntlDateFormatter::SHORT);
        $this->assertEquals($expected, $formatter->format($start, $end));
    }

    public static function providerNoDate()
    {
        $space = self::get_space();

        return [
            ['en', '2013-10-05 10:00:00', '2013-10-05 13:30:00', '10:00' . $space . 'AM – 1:30' . $space . 'PM'],
            ['en', '2013-10-05 12:20:00', '2013-10-05 13:30:00', '12:20 – 1:30' . $space . 'PM'],
            ['en', '12:20:00', '13:30:00', '12:20 – 1:30' . $space . 'PM'],
            // get a little weird
            ['en', '2013-10-05 12:20:00', '2013-10-07 13:30:00', '12:20 – 1:30' . $space . 'PM'],
            ['en', '2012-06-05 10:20:00', '2013-10-07 13:30:00', '10:20' . $space . 'AM – 1:30' . $space . 'PM'],
        ];
    }

    public function testNoMutation()
    {
        // changing formats should not change the stored dates
        $start = new \DateTime('2012-01-10 10:00:00');
        $end = new \DateTime('2012-01-17 11:00:00');
        $r = new Ranger('en');
        $r->setDateType(\IntlDateFormatter::NONE);
        $r->setTimeType(\IntlDateFormatter::SHORT);
        $r->format($start, $end);
        $r->setDateType(\IntlDateFormatter::MEDIUM);
        $r->setTimeType(\IntlDateFormatter::NONE);
        $formatted = $r->format($start, $end);
        $this->assertEquals('Jan 10–17, 2012', $formatted);
    }

    public function testNoMutation2()
    {
        // checks same as above but a different approach
        $start = new \DateTime('2012-01-10 10:00:00');
        $end = new \DateTime('2012-01-17 11:00:00');
        $r = new Ranger('en');
        $r->setDateType(\IntlDateFormatter::MEDIUM);
        $v1 = $r->format($start, $end);
        $r->setDateType(\IntlDateFormatter::NONE);
        $r->format($start, $end);
        $r->setDateType(\IntlDateFormatter::MEDIUM);
        $v2 = $r->format($start, $end);
        $this->assertEquals($v1, $v2);
    }

    public function testIssue4()
    {
        $r = new Ranger('es');
        $r->setDateType(\IntlDateFormatter::LONG);
        $result = $r->format('2020-12-03', '2020-12-04');
        $this->assertEquals('3 – 4 de diciembre de 2020', $result);
    }

    public function testIssue8()
    {
        $r = new Ranger('de');
        $r->setTimeType(IntlDateFormatter::SHORT);
        $result = $r->format('2022-05-04 20:00:00', '2022-05-04 20:00:00');
        $this->assertEquals('04.05.2022, 20:00', $result);
    }

    public function testIssue9()
    {
        $r = new Ranger('en');
        $this->assertEquals('Sep 5–12, 2022', $r->format(1662336000, 1662940800));
        $this->assertEquals('Sep 5–12, 2022', $r->format(1662336000.5, 1662940800.5));
        $this->assertEquals('Sep 5–12, 2022', $r->format("1662336000", "1662940800"));
        $this->assertEquals('Sep 5–12, 2022', $r->format("1662336000.5", "1662940800.5"));
    }

    public function testIssue14()
    {
        $r = new Ranger('zh_TW');
        $r
            ->setDateType(IntlDateFormatter::MEDIUM)
            ->setTimeType(IntlDateFormatter::SHORT);

        if (version_compare(INTL_ICU_VERSION, '70.1', '<')) {
            $day_period = '下午';
        } else {
            $day_period = '晚上';
        }
        $this->assertEquals('2024年3月29日, ' . $day_period . '7:00', $r->format(1711738800, 1711738800));
    }

    public function testIssue15()
    {
        $r = new Ranger('zh_TW');
        $r
        ->setDateType(IntlDateFormatter::MEDIUM)
        ->setTimeType(IntlDateFormatter::SHORT);

        if (version_compare(INTL_ICU_VERSION, '70.1', '<')) {
            $day_period = '下午';
        } else {
            $day_period = '晚上';
        }
        $this->assertEquals('2024年3月29日, ' . $day_period . '7:00 – 2024年3月30日, ' . $day_period . '8:00', $r->format(1711738800, 1711828800));
    }
}
