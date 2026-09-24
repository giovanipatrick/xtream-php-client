<?php

declare(strict_types=1);

namespace Xtream\Serializer;

use DateTimeInterface;

final class JsonApiSerializer extends AbstractSerializer
{
    /** @var StandardizedSerializer */
    private $standardized;

    public function __construct()
    {
        $this->standardized = new StandardizedSerializer();
    }

    public function getType(): string
    {
        return 'JSON:API';
    }

    public function serialize(string $resource, array $payload): array
    {
        $standard = $this->standardized->serialize($resource, $payload);

        switch ($resource) {
            case 'profile':
                return ['data' => $this->singleResource('user-profile', $standard)];
            case 'serverInfo':
                return ['data' => $this->singleResource('server-info', $standard)];
            case 'channelCategories':
                return ['data' => $this->categoryResources($standard, 'channel-category')];
            case 'movieCategories':
                return ['data' => $this->categoryResources($standard, 'movie-category')];
            case 'showCategories':
                return ['data' => $this->categoryResources($standard, 'show-category')];
            case 'channels':
                return ['data' => $this->listingResources($standard, 'channel', 'channel-category')];
            case 'movies':
                return ['data' => $this->listingResources($standard, 'movie', 'movie-category')];
            case 'movie':
                return ['data' => $this->contentResource($standard, 'movie', 'movie-category')];
            case 'shows':
                return ['data' => $this->listingResources($standard, 'show', 'show-category')];
            case 'show':
                return $this->showDocument($standard);
            case 'shortEPG':
            case 'fullEPG':
                return ['data' => $this->listingResources($standard, 'epg-listing')];
            default:
                return ['data' => $standard];
        }
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    private function singleResource(string $type, array $values): array
    {
        $id = (string) $this->value($values, 'id', '');
        unset($values['id']);

        return [
            'type' => $type,
            'id' => $id,
            'attributes' => $this->jsonValue($values),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $categories
     *
     * @return array<int, array<string, mixed>>
     */
    private function categoryResources(array $categories, string $type): array
    {
        $result = [];

        foreach ($categories as $category) {
            $parentId = (string) $this->value($category, 'parentId', '0');
            $resource = [
                'type' => $type,
                'id' => (string) $this->value($category, 'id', ''),
                'attributes' => [
                    'name' => $this->value($category, 'name'),
                ],
            ];

            if ($parentId !== '' && $parentId !== '0') {
                $resource['relationships'] = [
                    'parent' => [
                        'data' => ['type' => $type, 'id' => $parentId],
                    ],
                ];
            }

            $result[] = $resource;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function listingResources(array $items, string $type, string $categoryType = ''): array
    {
        $result = [];

        foreach ($items as $item) {
            $result[] = $this->contentResource($item, $type, $categoryType);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $item
     *
     * @return array<string, mixed>
     */
    private function contentResource(array $item, string $type, string $categoryType = ''): array
    {
        $id = (string) $this->value($item, 'id', '');
        $categoryIds = $this->value($item, 'categoryIds', []);

        unset($item['id'], $item['categoryIds']);

        $resource = [
            'type' => $type,
            'id' => $id,
            'attributes' => $this->jsonValue($item),
        ];

        if ($categoryType !== '' && is_array($categoryIds) && $categoryIds !== []) {
            $resource['relationships'] = [
                'categories' => [
                    'data' => array_map(
                        static function ($categoryId) use ($categoryType): array {
                            return ['type' => $categoryType, 'id' => (string) $categoryId];
                        },
                        $categoryIds
                    ),
                ],
            ];
        }

        return $resource;
    }

    /**
     * @param array<string, mixed> $show
     *
     * @return array<string, mixed>
     */
    private function showDocument(array $show): array
    {
        $showId = (string) $this->value($show, 'id', '');
        $seasons = $this->value($show, 'seasons', []);
        $categoryIds = $this->value($show, 'categoryIds', []);
        $included = [];
        $seasonLinks = [];

        if (!is_array($seasons)) {
            $seasons = [];
        }

        foreach ($seasons as $season) {
            if (!is_array($season)) {
                continue;
            }

            $seasonId = (string) $this->value($season, 'id', '');
            $episodes = $this->value($season, 'episodes', []);
            $episodeLinks = [];

            if (!is_array($episodes)) {
                $episodes = [];
            }

            foreach ($episodes as $episode) {
                if (!is_array($episode)) {
                    continue;
                }

                $episodeId = (string) $this->value($episode, 'id', '');
                $episodeAttributes = $episode;
                unset($episodeAttributes['id'], $episodeAttributes['seasonId'], $episodeAttributes['showId']);

                $episodeLinks[] = ['type' => 'episode', 'id' => $episodeId];
                $included[] = [
                    'type' => 'episode',
                    'id' => $episodeId,
                    'attributes' => $this->jsonValue($episodeAttributes),
                    'relationships' => [
                        'season' => ['data' => ['type' => 'season', 'id' => $seasonId]],
                        'show' => ['data' => ['type' => 'show', 'id' => $showId]],
                    ],
                ];
            }

            $seasonAttributes = $season;
            unset(
                $seasonAttributes['id'],
                $seasonAttributes['showId'],
                $seasonAttributes['episodes']
            );

            $seasonLinks[] = ['type' => 'season', 'id' => $seasonId];
            $included[] = [
                'type' => 'season',
                'id' => $seasonId,
                'attributes' => $this->jsonValue($seasonAttributes),
                'relationships' => [
                    'show' => ['data' => ['type' => 'show', 'id' => $showId]],
                    'episodes' => ['data' => $episodeLinks],
                ],
            ];
        }

        $attributes = $show;
        unset($attributes['id'], $attributes['categoryIds'], $attributes['seasons']);

        $relationships = [
            'seasons' => ['data' => $seasonLinks],
        ];

        if (is_array($categoryIds) && $categoryIds !== []) {
            $relationships['categories'] = [
                'data' => array_map(
                    static function ($categoryId): array {
                        return ['type' => 'show-category', 'id' => (string) $categoryId];
                    },
                    $categoryIds
                ),
            ];
        }

        return [
            'data' => [
                'type' => 'show',
                'id' => $showId,
                'attributes' => $this->jsonValue($attributes),
                'relationships' => $relationships,
            ],
            'included' => $included,
        ];
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function jsonValue($value)
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->jsonValue($item);
        }

        return $value;
    }
}
