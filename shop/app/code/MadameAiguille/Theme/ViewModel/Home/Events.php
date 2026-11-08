<?php
/**
 * Rendez-vous à venir de l'accueil, prêts à afficher : date en toutes lettres dans la langue du magasin
 * (« Samedi 14 novembre », « Du 12 au 14 décembre »), titre, lieu et texte.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\ViewModel\Home;

use MadameAiguille\Theme\Model\Home\Events as EventsModel;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Events implements ArgumentInterface
{
    public function __construct(
        private readonly EventsModel $events,
        private readonly TimezoneInterface $timezone,
        private readonly ResolverInterface $localeResolver
    ) {
    }

    /**
     * @return list<array{when: string, datetime: string, title: string, place: string, text: string}>
     */
    public function getEvents(): array
    {
        $today = \DateTimeImmutable::createFromMutable($this->timezone->date());
        $locale = $this->localeResolver->getLocale();

        return array_map(
            static fn (array $event): array => [
                'when' => self::when($event['start'], $event['end'], $today, $locale),
                'datetime' => $event['start']->format('Y-m-d'),
                'title' => $event['title'],
                'place' => $event['place'],
                'text' => $event['text'],
            ],
            $this->events->upcoming($today)
        );
    }

    /**
     * « Samedi 14 novembre », « Du 12 au 14 décembre », « Du 30 novembre au 2 décembre » ; l'année n'est écrite
     * que si elle n'est pas l'année en cours.
     */
    public static function when(
        \DateTimeImmutable $start,
        \DateTimeImmutable $end,
        \DateTimeImmutable $today,
        string $locale
    ): string {
        $year = $end->format('Y') !== $today->format('Y') ? ' y' : '';
        if ($start == $end) {
            return self::capitalize(self::format($start, 'EEEE d MMMM' . $year, $locale));
        }

        $sameMonth = $start->format('Y-m') === $end->format('Y-m');
        $from = self::format($start, $sameMonth ? 'd' : 'd MMMM', $locale);

        return (string) __('Event from %1 to %2', $from, self::format($end, 'd MMMM' . $year, $locale));
    }

    private static function format(\DateTimeImmutable $date, string $pattern, string $locale): string
    {
        $formatter = new \IntlDateFormatter($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, 'UTC');
        $formatter->setPattern($pattern);

        return (string) $formatter->format(new \DateTimeImmutable($date->format('Y-m-d'), new \DateTimeZone('UTC')));
    }

    private static function capitalize(string $text): string
    {
        return mb_convert_case(mb_substr($text, 0, 1), MB_CASE_UPPER) . mb_substr($text, 1);
    }
}
