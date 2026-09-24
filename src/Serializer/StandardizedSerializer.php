<?php

declare(strict_types=1);

namespace Xtream\Serializer;

use InvalidArgumentException;

final class StandardizedSerializer extends AbstractSerializer
{
    public function getType(): string
    {
        return 'Standardized';
    }

    public function serialize(string $resource, array $payload): array
    {
        switch ($resource) {
            case 'profile':
                return $this->profile($payload);
            case 'serverInfo':
                return $this->serverInfo($payload);
            case 'channelCategories':
            case 'movieCategories':
            case 'showCategories':
                return $this->categories($payload);
            case 'channels':
                return $this->channels($payload);
            case 'movies':
                return $this->movies($payload);
            case 'movie':
                return $this->movie($payload);
            case 'shows':
                return $this->shows($payload);
            case 'show':
                return $this->show($payload);
            case 'shortEPG':
                return $this->epg($payload, false);
            case 'fullEPG':
                return $this->epg($payload, true);
            default:
                return $payload;
        }
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function profile(array $payload): array
    {
        $input = $this->camelize($payload);
        $result = $input;

        unset(
            $result['auth'],
            $result['expDate'],
            $result['maxConnections'],
            $result['activeCons'],
            $result['createdAt']
        );

        $result['id'] = (string) $this->value($input, 'username', '');
        $result['isTrial'] = (string) $this->value($input, 'isTrial', '0') === '1';
        $result['maxConnections'] = (int) $this->value($input, 'maxConnections', 0);
        $result['activeConnections'] = (int) $this->value($input, 'activeCons', 0);
        $result['createdAt'] = $this->timestampDate($this->value($input, 'createdAt', 0));
        $result['expiresAt'] = $this->timestampDate($this->value($input, 'expDate', 0));

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function serverInfo(array $payload): array
    {
        $input = $this->camelize($payload);
        $result = $input;

        unset($result['timestampNow']);
        $result['id'] = (string) $this->value($input, 'url', '');
        $result['timeNow'] = $this->timestampDate($this->value($input, 'timestampNow', 0));

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function categories(array $payload): array
    {
        $result = [];

        foreach ($this->camelize($payload) as $category) {
            $result[] = [
                'id' => (string) $this->value($category, 'categoryId', ''),
                'name' => $this->value($category, 'categoryName'),
                'parentId' => (string) $this->value($category, 'parentId', '0'),
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function channels(array $payload): array
    {
        $result = [];

        foreach ($this->camelize($payload) as $channel) {
            $result[] = [
                'id' => (string) $this->value($channel, 'streamId', ''),
                'name' => $this->value($channel, 'name'),
                'number' => $this->value($channel, 'num'),
                'tvArchive' => (int) $this->value($channel, 'tvArchive', 0) === 1,
                'tvArchiveDuration' => $this->value($channel, 'tvArchiveDuration'),
                'logo' => $this->value($channel, 'streamIcon'),
                'epgId' => $this->value($channel, 'epgChannelId'),
                'createdAt' => $this->timestampDate($this->value($channel, 'added', 0)),
                'categoryIds' => $this->stringList($this->value($channel, 'categoryIds', [])),
                'url' => $this->value($channel, 'url'),
            ];
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function movies(array $payload): array
    {
        $result = [];

        foreach ($this->camelize($payload) as $movie) {
            $result[] = [
                'id' => (string) $this->value($movie, 'streamId', ''),
                'name' => $this->value($movie, 'title'),
                'plot' => $this->value($movie, 'plot'),
                'genre' => $this->csv($this->value($movie, 'genre')),
                'cast' => $this->csv($this->value($movie, 'cast')),
                'director' => $this->csv($this->value($movie, 'director')),
                'poster' => $this->value($movie, 'streamIcon'),
                'duration' => (int) $this->value($movie, 'episodeRunTime', 0) * 60,
                'voteAverage' => (float) $this->value($movie, 'rating', 0),
                'releaseDate' => $this->optionalDate($this->value($movie, 'releaseDate')),
                'youtubeId' => $this->value($movie, 'youtubeTrailer'),
                'createdAt' => $this->timestampDate($this->value($movie, 'added', 0)),
                'categoryIds' => $this->stringList($this->value($movie, 'categoryIds', [])),
                'url' => $this->value($movie, 'url'),
            ];
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function movie(array $payload): array
    {
        $input = $this->camelize($payload, true);
        $info = $this->value($input, 'info', []);
        $data = $this->value($input, 'movieData', []);

        if (!is_array($info) || !is_array($data)) {
            throw new InvalidArgumentException('Movie serialization requires info and movie_data arrays.');
        }

        return [
            'id' => (string) $this->value($data, 'streamId', ''),
            'name' => $this->value($info, 'name'),
            'originalName' => $this->value($info, 'oName'),
            'description' => $this->value($info, 'description'),
            'plot' => $this->value($info, 'plot'),
            'country' => $this->value($info, 'country'),
            'informationUrl' => $this->value($info, 'kinopoiskUrl'),
            'cover' => $this->firstValue($this->value($info, 'backdropPath', [])),
            'poster' => $this->value($info, 'movieImage'),
            'duration' => $this->value($info, 'durationSecs'),
            'durationFormatted' => $this->value($info, 'duration'),
            'voteAverage' => (float) $this->value($info, 'rating', 0),
            'director' => $this->csv($this->value($info, 'director')),
            'actors' => $this->csv($this->value($info, 'actors')),
            'cast' => $this->csv($this->value($info, 'cast')),
            'genre' => $this->csv($this->value($info, 'genre')),
            'categoryIds' => $this->stringList($this->value($data, 'categoryIds', [])),
            'tmdbId' => (string) $this->value($info, 'tmdbId', ''),
            'youtubeId' => $this->value($info, 'youtubeTrailer'),
            'releaseDate' => $this->optionalDate($this->value($info, 'releaseDate')),
            'createdAt' => $this->timestampDate($this->value($data, 'added', 0)),
            'rating' => [
                'mpaa' => $this->value($info, 'mpaaRating'),
                'age' => (int) $this->value($info, 'age', 0),
            ],
            'subtitles' => $this->value($info, 'subtitles', []),
            'video' => $this->value($info, 'video'),
            'audio' => $this->value($info, 'audio'),
            'bitrate' => $this->value($info, 'bitrate'),
            'url' => $this->value($input, 'url'),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function shows(array $payload): array
    {
        $result = [];

        foreach ($this->camelize($payload) as $show) {
            $result[] = $this->showSummary($show);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function show(array $payload): array
    {
        $input = $this->camelize($payload, true);
        $info = $this->value($input, 'info', []);
        $seasons = $this->value($input, 'seasons', []);
        $episodesBySeason = $this->value($input, 'episodes', []);

        if (!is_array($info) || !is_array($seasons) || !is_array($episodesBySeason)) {
            throw new InvalidArgumentException('Show serialization requires info, seasons, and episodes arrays.');
        }

        $showId = (string) $this->value($info, 'seriesId', '');

        if ($showId === '') {
            throw new InvalidArgumentException('Show serialization requires a series_id.');
        }

        $mappedEpisodes = [];

        foreach ($episodesBySeason as $episodeList) {
            if (!is_array($episodeList)) {
                continue;
            }

            foreach ($episodeList as $episode) {
                if (!is_array($episode)) {
                    continue;
                }

                $episodeInfo = $this->value($episode, 'info', []);
                $episodeInfo = is_array($episodeInfo) ? $episodeInfo : [];
                $seasonNumber = (int) $this->value($episode, 'season', 0);
                $seasonId = $this->seasonId($seasons, $seasonNumber);

                $mappedEpisodes[] = [
                    'id' => (string) $this->value($episode, 'id', ''),
                    'number' => (int) $this->value($episode, 'episodeNum', 0),
                    'plot' => $this->value($episodeInfo, 'plot'),
                    'title' => $this->value($episode, 'title'),
                    'tmdbId' => (string) $this->value($episodeInfo, 'tmdbId', ''),
                    'poster' => $this->value($episodeInfo, 'movieImage'),
                    'voteAverage' => (float) $this->value($episodeInfo, 'rating', 0),
                    'cover' => $this->value($episodeInfo, 'coverBig'),
                    'duration' => $this->value($episodeInfo, 'durationSecs'),
                    'durationFormatted' => $this->value($episodeInfo, 'duration'),
                    'releaseDate' => $this->optionalDate($this->value($episodeInfo, 'releaseDate')),
                    'createdAt' => $this->timestampDate($this->value($episode, 'added', 0)),
                    'showId' => $showId,
                    'seasonId' => $seasonId,
                    'url' => $this->value($episode, 'url'),
                    'subtitles' => $this->value($episode, 'subtitles', []),
                    'video' => $this->value($episodeInfo, 'video'),
                    'audio' => $this->value($episodeInfo, 'audio'),
                    'bitrate' => $this->value($episodeInfo, 'bitrate'),
                ];
            }
        }

        if ($seasons === []) {
            $seasons = $this->generatedSeasons($episodesBySeason);
        }

        $mappedSeasons = [];

        foreach ($seasons as $season) {
            if (!is_array($season)) {
                continue;
            }

            $seasonId = (string) $this->value($season, 'id', $this->value($season, 'seasonNumber', ''));
            $seasonEpisodes = array_values(array_filter(
                $mappedEpisodes,
                static function (array $episode) use ($seasonId): bool {
                    return $episode['seasonId'] === $seasonId;
                }
            ));

            $mappedSeasons[] = [
                'id' => $seasonId,
                'name' => $this->value($season, 'name'),
                'episodeCount' => (int) $this->value($season, 'episodeCount', count($seasonEpisodes)),
                'overview' => $this->value($season, 'overview', ''),
                'voteAverage' => (float) $this->value($season, 'voteAverage', 0),
                'releaseDate' => $this->optionalDate($this->value($season, 'airDate')),
                'number' => (int) $this->value($season, 'seasonNumber', 0),
                'cover' => $this->value($season, 'coverBig', $this->value($season, 'cover')),
                'showId' => $showId,
                'episodes' => $seasonEpisodes,
            ];
        }

        $summary = $this->showSummary($info);
        $summary['seasons'] = $mappedSeasons;

        return $summary;
    }

    /**
     * @param array<string, mixed> $show
     *
     * @return array<string, mixed>
     */
    private function showSummary(array $show): array
    {
        return [
            'id' => (string) $this->value($show, 'seriesId', ''),
            'name' => $this->value($show, 'title', $this->value($show, 'name')),
            'plot' => $this->value($show, 'plot'),
            'cast' => $this->csv($this->value($show, 'cast')),
            'director' => $this->csv($this->value($show, 'director')),
            'genre' => $this->csv($this->value($show, 'genre')),
            'voteAverage' => (float) $this->value($show, 'rating', 0),
            'poster' => $this->value($show, 'cover'),
            'cover' => $this->firstValue($this->value($show, 'backdropPath', [])),
            'duration' => (int) $this->value($show, 'episodeRunTime', 0) * 60,
            'releaseDate' => $this->optionalDate($this->value($show, 'releaseDate')),
            'updatedAt' => $this->timestampDate($this->value($show, 'lastModified', 0)),
            'categoryIds' => $this->stringList($this->value($show, 'categoryIds', [])),
            'youtubeId' => $this->value($show, 'youtubeTrailer'),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return array<int, array<string, mixed>>
     */
    private function epg(array $payload, bool $full): array
    {
        $input = $this->camelize($payload, true);
        $listings = $this->value($input, 'epgListings', []);
        $result = [];

        if (!is_array($listings)) {
            return $result;
        }

        foreach ($listings as $listing) {
            if (!is_array($listing)) {
                continue;
            }

            $item = [
                'id' => $this->value($listing, 'id'),
                'epgId' => $this->value($listing, 'epgId'),
                'channelId' => $this->value($listing, 'channelId'),
                'start' => $this->optionalDate($this->value($listing, 'start')),
                'end' => $full
                    ? $this->optionalDate($this->value($listing, 'end'))
                    : $this->timestampDate($this->value($listing, 'end', 0)),
                'title' => $this->decoded($this->value($listing, 'title')),
                'description' => $this->decoded($this->value($listing, 'description')),
                'language' => $this->value($listing, 'lang'),
            ];

            if ($full) {
                $item['nowPlaying'] = (bool) $this->value($listing, 'nowPlaying', false);
                $item['hasArchive'] = (bool) $this->value($listing, 'hasArchive', false);
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * @param mixed $value
     *
     * @return mixed|null
     */
    private function firstValue($value)
    {
        return is_array($value) && $value !== [] ? reset($value) : null;
    }

    /**
     * @param array<int, array<string, mixed>> $seasons
     */
    private function seasonId(array $seasons, int $number): string
    {
        foreach ($seasons as $season) {
            if (
                is_array($season)
                && (int) $this->value($season, 'seasonNumber', -1) === $number
            ) {
                return (string) $this->value($season, 'id', $number);
            }
        }

        return (string) $number;
    }

    /**
     * @param array<string, array<int, array<string, mixed>>> $episodesBySeason
     *
     * @return array<int, array<string, mixed>>
     */
    private function generatedSeasons(array $episodesBySeason): array
    {
        $result = [];

        foreach ($episodesBySeason as $number => $episodes) {
            if (!is_array($episodes) || $episodes === [] || !is_array($episodes[0])) {
                continue;
            }

            $firstInfo = $this->value($episodes[0], 'info', []);
            $firstInfo = is_array($firstInfo) ? $firstInfo : [];

            $result[] = [
                'id' => (int) $number,
                'name' => 'Season ' . $number,
                'episodeCount' => count($episodes),
                'overview' => '',
                'airDate' => $this->value($firstInfo, 'releaseDate'),
                'cover' => $this->value($firstInfo, 'movieImage'),
                'seasonNumber' => (int) $number,
                'voteAverage' => (float) $this->value($firstInfo, 'rating', 0),
                'coverBig' => $this->value($firstInfo, 'movieImage'),
            ];
        }

        return $result;
    }
}
