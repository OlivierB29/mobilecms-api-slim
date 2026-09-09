<?php

declare(strict_types=1);

namespace App\Application\Actions\Cms;

use DateTimeImmutable;
use Psr\Http\Message\ResponseInterface as Response;

class EventsActionGetAction extends CmsAction
{
    private static string $SITEPREFIX = 'http://localhost:5173/#/calendrier/detail/';

    protected function action(): Response
    {
        $eventsResponse = $this->getService()->getAllObjects('calendar');
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//MobileCMS//Agenda//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:MobileCMS Agenda',
        ];

        foreach ($eventsResponse->getResult() as $eventReference) {
            $eventResponse = $this->getService()->getRecord('calendar', (string) $eventReference->id);
            if ($eventResponse->getCode() !== 200) {
                continue;
            }

            $event = $eventResponse->getResult();
            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.self::escape((string) ($event->id ?? $eventReference->id)).'@mobilecms';
            $lines[] = 'URL:'.self::escape(self::$SITEPREFIX.(string) ($event->id ?? $eventReference->id));

            if (!empty($event->startdate)) {
                $lines[] = 'DTSTART:'.self::formatDateTime((string) $event->date, (string) $event->startdate);
                if (!empty($event->enddate)) {
                    $lines[] = 'DTEND:'.self::formatDateTime((string) $event->date, (string) $event->enddate);
                }
            } else {
                $startDate = self::parseDate((string) $event->date);
                $lines[] = 'DTSTART;VALUE=DATE:'.$startDate->format('Ymd');
                $lines[] = 'DTEND;VALUE=DATE:'.$startDate->modify('+1 day')->format('Ymd');
            }

            $lines[] = 'SUMMARY:'.self::escape((string) ($event->title ?? ''));
            $description = strip_tags((string) ($event->description ?? ''));
            if ($description !== '') {
                $lines[] = 'DESCRIPTION:'.self::escape($description);
            }
            if (!empty($event->location)) {
                $lines[] = 'LOCATION:'.self::escape((string) $event->location);
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';
        $this->response->getBody()->write(implode("\r\n", $lines)."\r\n");

        return $this->response
            ->withHeader('Content-Type', 'text/calendar; charset=utf-8')
            ->withHeader('Content-Disposition', 'inline; filename="events.ics"');
    }

    private static function parseDate(string $date): DateTimeImmutable
    {
        return new DateTimeImmutable($date);
    }

    private static function formatDateTime(string $date, string $time): string
    {
        $value = $time;
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $time) !== 1) {
            $value = trim($date.' '.$time);
        }

        return (new DateTimeImmutable($value))->setTimezone(new \DateTimeZone('UTC'))->format('Ymd\THis\Z');
    }

    private static function escape(string $value): string
    {
        return str_replace(["\\", ";", ",", "\r\n", "\r", "\n"], ["\\\\", "\\;", "\\,", '\\n', '\\n', '\\n'], $value);
    }
}
