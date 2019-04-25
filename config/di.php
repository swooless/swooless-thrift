<?php declare(strict_types=1);

use App\Handler\DemoHandler;
use Swooless\Config\Loader\ZKLoader;
use Swooless\Protocol\Demo\Constant;
use Swooless\Protocol\Demo\ServerClient;
use Swooless\Protocol\Demo\ServerProcessor;
use Swooless\Registry\RedisRegistry;
use Swooless\Registry\SPI;
use Swooless\Config\Loader\LoaderInterface;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TSocket;

use function DI\create;
use function DI\autowire;

return [
    'SERVER_NAME' => function () {
        Constant::get('SERVER_NAME');
    },

    'ServerProcessor' => create(ServerProcessor::class)
        ->constructor(create(DemoHandler::class)),

    LoaderInterface::class => autowire(ZKLoader::class),

    SPI::class => autowire(RedisRegistry::class),

    ServerClient::class => function () {
        $timeout = 3;
        /** @var RedisRegistry $registry */
        $registry = app(SPI::class);

        $node = $registry->getProvider((string)app('SERVER_NAME'));
        $socket = new TSocket($node->getHost(), $node->getPort());
        $socket->setSendTimeout($timeout * 1000);
        $socket->setRecvTimeout($timeout * 1000);
        $transport = new TFramedTransport($socket);
        $protocol = new TBinaryProtocol($transport);
        $client = new ServerClient($protocol);
        $transport->open();
        return $client;
    }
];
