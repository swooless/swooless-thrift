<?php declare(strict_types=1);

namespace App;

use DI\Container;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Proxy\ProxyFactory;
use Psr\Container\ContainerInterface;
use swoole_server;
use Swooless\Registry\RedisRegistry;
use Swooless\Registry\ServerNode;

use Swooless\Registry\SPI;
use Swooless\ThriftServer\Server\SServer;
use Swooless\ThriftServer\Server\SServerSocket;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Factory\TTransportFactory;
use Thrift\Server\TServer;
use Throwable;

class Application extends Container
{
    /** @var Application */
    protected static $instance;

    /** @var SServer */
    private $server;

    public function __construct(MutableDefinitionSource $definitionSource = null, ProxyFactory $proxyFactory = null, ContainerInterface $wrapperContainer = null)
    {
        parent::__construct($definitionSource, $proxyFactory, $wrapperContainer);
        static::$instance = $this;
    }

    public static function getInstance(): Application
    {
        return static::$instance;
    }

    /**
     * @return TServer
     */
    public function server()
    {
        try {
            $processor = $this->get("ServerProcessor");

            $transportFactory = new TTransportFactory();
            $protocolFactory = new TBinaryProtocolFactory(true, true);
            $transport = new SServerSocket($this->getIp(), env('RPC_PORT', null));
            $this->server = new SServer($processor, $transport, $transportFactory, $transportFactory, $protocolFactory, $protocolFactory);
            $this->server->setServerName(md5(APP_ROOT . __FILE__));

            $this->monitor($transport);
            return $this->server;
        } catch (Throwable $tx) {
            print 'TException: ' . $tx->getMessage() . "" . PHP_EOL;
        }
    }

    public function run()
    {
        $this->server()->serve();
    }

    private function monitor(SServerSocket &$socket): void
    {
        $socket->on('start', function ($server) {

            $this->server->lock()->lock($server->master_pid);
            /** @var swoole_server $server */
            try {
                echo "[ {$server->master_pid} ] : Swoole Server Start By "
                    . $server->host . ':' . $server->port . PHP_EOL;

                go(function () use ($server) {
                    /** @var RedisRegistry $registry */
                    $registry = app(SPI::class);

                    $node = new ServerNode();
                    $node->setName((string)app('SERVER_NAME'));
                    $node->setHost($server->host);
                    $node->setPort(intval($server->port));
                    $registry->addProvider($node);
                });

            } catch (Throwable $throwable) {
                echo $throwable->getMessage() . PHP_EOL;
            }
        });

        $socket->on('shutdown', function ($server) {
            /** @var swoole_server $server */
            try {
                go(function () use ($server) {
                    /** @var RedisRegistry $registry */
                    $registry = app(SPI::class);
                    $node = new ServerNode();
                    $node->setName((string)app('SERVER_NAME'));
                    $node->setHost($server->host);
                    $node->setPort(intval($server->port));
                    $registry->removeProvider($node);
                });
            } catch (Throwable $e) {
                error_log($e->getMessage());
            }
        });

        $socket->on('task', function () {
            echo 'task' . PHP_EOL;
        });

        $socket->on('finish', function () {
            echo 'finish' . PHP_EOL;
        });

        $socket->on('WorkerStart', function ($server, $id) {
            /** @var swoole_server $server */
            echo "Worker Start [{$server->worker_pid}:{$id}]" . PHP_EOL;
        });
    }

    private function getIp(): string
    {
        $ip = '127.0.0.1';
        $ips = swoole_get_local_ip();

        if (is_array($ips) && !in_array($ip, $ips)) {
            $ip = array_shift($ips);
        }
        return env('RPC_HOST', $ip);
    }
}