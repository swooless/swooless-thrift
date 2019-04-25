<?php declare(strict_types=1);

namespace Tests\Rpc;

use PHPUnit\Framework\TestCase;
use Swooless\Protocol\Demo\ServerClient;

class ServerTest extends TestCase
{
    public function testVersion()
    {
        /** @var ServerClient $client */
        $client = app(ServerClient::class);
        $version = $client->version();
        self::assertTrue('1.0.0' == $version);
    }
}