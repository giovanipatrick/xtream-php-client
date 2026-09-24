<?php

declare(strict_types=1);

namespace Xtream\Exception;

final class HttpException extends XtreamException
{
    /** @var int */
    private $statusCode;

    public function __construct(int $statusCode)
    {
        $this->statusCode = $statusCode;

        parent::__construct(
            sprintf('The Xtream API returned HTTP status %d.', $statusCode),
            $statusCode
        );
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
