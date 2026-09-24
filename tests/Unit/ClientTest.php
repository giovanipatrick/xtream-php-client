<?php

declare(strict_types=1);

namespace Xtream\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xtream\Client;
use Xtream\Exception\InvalidConfigurationException;

final class ClientTest extends TestCase
{
    public function testItNormalizesTheBaseUrlAndUsesTheDefaultFormat()
    {
        $client = new Client([
            'url' => 'https://example.com///',
            'username' => 'user',
            'password' => 'secret',
        ]);

        self::assertSame('https://example.com', $client->getBaseUrl());
        self::assertSame('ts', $client->getPreferredFormat());
    }

    public function testItAcceptsACustomPreferredFormat()
    {
        $client = new Client([
            'url' => 'https://example.com',
            'username' => 'user',
            'password' => 'secret',
            'preferred_format' => 'ts',
        ]);

        self::assertSame('ts', $client->getPreferredFormat());
    }

    public function testItRejectsMissingRequiredOptions()
    {
        $invalidOptions = [
            [
                'username' => 'user',
                'password' => 'secret',
            ],
            [
                'url' => 'https://example.com',
                'password' => 'secret',
            ],
            [
                'url' => 'https://example.com',
                'username' => 'user',
            ],
        ];

        foreach ($invalidOptions as $options) {
            try {
                new Client($options);
                self::fail('Expected invalid configuration to be rejected.');
            } catch (InvalidConfigurationException $exception) {
                self::assertNotSame('', $exception->getMessage());
            }
        }
    }

    public function testItRejectsInvalidBaseUrls()
    {
        $this->expectException(InvalidConfigurationException::class);

        new Client([
            'url' => 'ftp://example.com/list?token=secret',
            'username' => 'user',
            'password' => 'secret',
        ]);
    }
}
