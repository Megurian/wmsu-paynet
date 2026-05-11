<?php

namespace App\Services;

use App\Models\Religion;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ReligionResolver
{
    private ?Collection $religions = null;

    private array $emptyMarkers = [
        'none',
        'null',
        'n/a',
        'na',
        'n a',
        'not applicable',
        'notapplicable',
        'not available',
        'no religion',
        'no religious affiliation',
        'none provided',
        'not specified',
        'unspecified',
        '-',
        '--',
    ];

    public function resolveId(?string $value): ?int
    {
        $value = trim((string) $value);

        if ($this->isEmptyReligionValue($value)) {
            return null;
        }

        if ($this->isBroadChristianLabel($value)) {
            throw ValidationException::withMessages([
                'religion' => 'Please use a specific Christian denomination instead of "Christian".',
            ]);
        }

        if (is_numeric($value)) {
            $existingById = Religion::find((int) $value);

            if ($existingById) {
                return $existingById->id;
            }
        }

        $canonicalName = $this->canonicalizeReligionName($value);

        if ($matched = $this->matchExistingReligionByName($canonicalName)) {
            return $matched->id;
        }

        if ($matched = $this->matchExistingReligionByName($value)) {
            return $matched->id;
        }

        return Religion::firstOrCreate([
            'name' => $canonicalName,
        ])->id;
    }

    public function isBroadChristianValue(?string $value): bool
    {
        return $this->isBroadChristianLabel((string) $value);
    }

    private function isBroadChristianLabel(string $value): bool
    {
        return in_array($this->normalizeReligionName($value), [
            'christian',
            'christianity',
            'christian religion',
            'christians',
            'christian faith',
            'believer',
            'believers',
        ], true);
    }

    private function canonicalizeReligionName(string $value): string
    {
        $normalized = $this->normalizeReligionName($value);

        $aliases = [
            'roman catholic' => 'Roman Catholic',
            'catholic' => 'Roman Catholic',
            'roman catholic church' => 'Roman Catholic',
            'rc' => 'Roman Catholic',
            'rcc' => 'Roman Catholic',
            'catholic christian' => 'Roman Catholic',
            'catholicism' => 'Roman Catholic',
            'christian catholic' => 'Roman Catholic',
            'iglesia ni cristo' => 'Iglesia Ni Cristo',
            'iglesia ni christo' => 'Iglesia Ni Cristo',
            'inc' => 'Iglesia Ni Cristo',
            'iglesia ni cristo inc' => 'Iglesia Ni Cristo',
            'islam' => 'Islam',
            'muslim' => 'Islam',
            'muslim sunni' => 'Islam',
            'muslim shia' => 'Islam',
            'born again' => 'Born Again Christian',
            'born again christian' => 'Born Again Christian',
            'born again christian church' => 'Born Again Christian',
            'born again believer' => 'Born Again Christian',
            'born again believers' => 'Born Again Christian',
            'evangelical' => 'Born Again Christian',
            'evangelical christian' => 'Born Again Christian',
            'evangelical church' => 'Born Again Christian',
            'evangelical protestant' => 'Born Again Christian',
            'non denominational christian' => 'Born Again Christian',
            'protestant' => 'Protestant',
            'protestant christian' => 'Protestant',
            'christian protestant' => 'Protestant',
            'pentecostal' => 'Pentecostal',
            'pentecostal christian' => 'Pentecostal',
            'apostolic' => 'Apostolic',
            'apostolic christian' => 'Apostolic',
            'assemblies of god' => 'Assemblies of God',
            'church of god' => 'Church of God',
            'church of christ' => 'Church of Christ',
            'christian and missionary alliance' => 'Christian and Missionary Alliance',
            'bible baptist' => 'Bible Baptist',
            'baptist' => 'Baptist',
            'baptist christian' => 'Baptist',
            'independent baptist' => 'Baptist',
            'seventh day adventist' => 'Seventh-day Adventist',
            'seventh day adventists' => 'Seventh-day Adventist',
            'sda' => 'Seventh-day Adventist',
            'seventh day adventist church' => 'Seventh-day Adventist',
            'methodist' => 'Methodist',
            'united methodist' => 'Methodist',
            'methodist church' => 'Methodist',
            'jehovah witness' => 'Jehovah\'s Witness',
            'jehovahs witness' => 'Jehovah\'s Witness',
            'jehovah s witness' => 'Jehovah\'s Witness',
            'jehovah witnesses' => 'Jehovah\'s Witness',
            'jw' => 'Jehovah\'s Witness',
            'latter day saints' => 'The Church of Jesus Christ of Latter-day Saints',
            'lds' => 'The Church of Jesus Christ of Latter-day Saints',
            'mormon' => 'The Church of Jesus Christ of Latter-day Saints',
            'latter day saint' => 'The Church of Jesus Christ of Latter-day Saints',
            'church of jesus christ of latter day saints' => 'The Church of Jesus Christ of Latter-day Saints',
            'philippine independent church' => 'Philippine Independent Church',
            'ifi' => 'Philippine Independent Church',
            'independent church of the philippines' => 'Philippine Independent Church',
            'united church of christ in the philippines' => 'United Church of Christ in the Philippines',
            'uccp' => 'United Church of Christ in the Philippines',
            'united church of christ philippines' => 'United Church of Christ in the Philippines',
            'christian and missionary alliance philippines' => 'Christian and Missionary Alliance',
        ];

        if (isset($aliases[$normalized])) {
            return $aliases[$normalized];
        }

        $patternAliases = [
            '/\b(catholic|roman catholic|rc)\b/i' => 'Roman Catholic',
            '/\b(iglesia\s+ni\s+cristo|inc)\b/i' => 'Iglesia Ni Cristo',
            '/\b(born\s*again|new\s*birth)\b/i' => 'Born Again Christian',
            '/\b(evangelical|non[-\s]?denominational)\b/i' => 'Born Again Christian',
            '/\b(pentecostal|apostolic|assemblies of god|church of god|church of christ)\b/i' => match ($normalized) {
                default => null,
            },
        ];

        foreach ($patternAliases as $pattern => $mapped) {
            if (preg_match($pattern, $normalized)) {
                if ($mapped !== null) {
                    return $mapped;
                }
            }
        }

        if (preg_match('/\b(pentecostal)\b/i', $normalized)) {
            return 'Pentecostal';
        }

        if (preg_match('/\b(apostolic)\b/i', $normalized)) {
            return 'Apostolic';
        }

        if (preg_match('/\b(assemblies of god)\b/i', $normalized)) {
            return 'Assemblies of God';
        }

        if (preg_match('/\b(church of god)\b/i', $normalized)) {
            return 'Church of God';
        }

        if (preg_match('/\b(church of christ)\b/i', $normalized)) {
            return 'Church of Christ';
        }

        if (preg_match('/\b(christian and missionary alliance)\b/i', $normalized)) {
            return 'Christian and Missionary Alliance';
        }

        return $this->titleCaseReligionName($value);
    }

    private function titleCaseReligionName(string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);

        return collect(explode(' ', mb_strtolower($value)))
            ->filter()
            ->map(function (string $word) {
                return match ($word) {
                    'ni', 'ng', 'of', 'and', 'the' => $word,
                    'latter-day' => 'Latter-day',
                    'jesus' => 'Jesus',
                    'christ' => 'Christ',
                    'god' => 'God',
                    'inc' => 'INC',
                    default => ucfirst($word),
                };
            })
            ->implode(' ');
    }

    private function normalizeReligionName(?string $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $value = preg_replace('/[\s\p{P}]+/u', ' ', $value) ?? $value;

        return trim(mb_strtolower($value));
    }

    private function isEmptyReligionValue(?string $value): bool
    {
        $normalized = $this->normalizeReligionName($value);

        if ($normalized === '') {
            return true;
        }

        return in_array($normalized, $this->emptyMarkers, true);
    }

    private function matchExistingReligionByName(string $name): ?Religion
    {
        $normalizedName = $this->normalizeReligionName($name);

        if ($normalizedName === '') {
            return null;
        }

        return $this->religions()->first(function (Religion $religion) use ($normalizedName) {
            return $this->normalizeReligionName($religion->name) === $normalizedName;
        });
    }

    private function religions(): Collection
    {
        if ($this->religions === null) {
            $this->religions = Religion::query()->get(['id', 'name']);
        }

        return $this->religions;
    }
}