# Ranger
Ranger is a formatter for date and time ranges, based (somewhat loosely) on Adam Shaw's `formatRange` algorithm in [fullCalendar](https://github.com/fullcalendar/fullcalendar).

## Some Examples

```php
<?php
use OpenPsa\Ranger\Ranger;

$ranger = new Ranger('en');
echo $ranger->format('2013-10-05', '2013-10-20');
// Oct 5–20, 2013
echo $ranger->format('2013-10-05', '2013-11-20');
// Oct 5 – Nov 20, 2013

$ranger = new Ranger('en_GB');
echo $ranger->format('2013-10-05', '2013-10-20');
// 5–20 Oct 2013
echo $ranger->format('2013-10-05', '2013-11-20');
// 5 Oct – 20 Nov 2013

$ranger = new Ranger('de');
echo $ranger->format('2013-10-05', '2013-10-20');
// 05.–20.10.2013
echo $ranger->format('2013-10-05', '2013-11-20');
// 05.10.–20.11.2013
```

## Usage

To use Ranger, you will need to have the [`php-intl`](http://php.net/manual/en/book.intl.php) extension installed. If the you only need it to work in the `"en"`  locale, you can alternatively require [`symfony/intl`](https://github.com/symfony/intl) in your `composer.json`.

Instantiate ranger with the name of your locale as the parameter. You can also pass `null` to use the `ini.default_locale` setting. Afterwards, you can call `format()` with two date parameters. Accepted types are

 - `DateTime` objects
 - strings (any format that `DateTime` can read)
 - Unix timestamps
 - `null` (which means current date).

### Output Customization

```php
<?php
use OpenPsa\Ranger\Ranger;
use IntlDateFormatter;

$ranger = new Ranger('en');
$ranger
    ->setRangeSeparator(' and ')
    ->setDateTimeSeparator(', between ')
    ->setDateType(IntlDateFormatter::LONG)
    ->setTimeType(IntlDateFormatter::SHORT);

echo $ranger->format('2013-10-05 10:00:01', '2013-10-05 13:30:00');
// October 5, 2013, between 10:00 AM and 1:30 PM
```
