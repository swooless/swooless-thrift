<?php declare(strict_types=1);

namespace App\Console;

use App\ThriftServer;
use DI\Annotation\Inject;
use Swooless\Config\Loader\FileLoader;
use Swooless\Config\Loader\LoaderInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class StartCommand extends Command
{
    /**
     * @Inject()
     * @var FileLoader
     */
    private $loader;

    /**
     * @Inject()
     * @var ThriftServer
     */
    private $server;

    protected function configure()
    {
        $this->setName('start')
            ->setDescription('Start Server')
            ->addOption('daemon', 'd', null, '以守护进程的方式运行')
            ->addOption('workspace', 'w', InputOption::VALUE_OPTIONAL, '设置工作目录，默认为phar包')
            ->addOption('zookeeper', 'z', InputOption::VALUE_OPTIONAL, 'zk 链接地址');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        try {
            $workspaceDir = $input->getOption('workspace');

            if (is_file($workspaceDir . '/.env')) {
                $this->loader->init(['path' => [$workspaceDir . '/.env']]);
                $this->loader->loadToEnv();
            }

            $daemon = $input->getOption('daemon');
            putenv("SWOOLE_HTTP_DAEMONIZE={$daemon}");

            $zkHost = $input->getOption('zookeeper');
            if ($zkHost) {
                putenv("ZK_HOST={$zkHost}");

                /** @var LoaderInterface $load */
                $load = app(LoaderInterface::class);
                $envPath = sprintf('/baozun/pim/%s/env', env('APP_NAME', 'platform-control'));
                $load->init([
                    'host' => $zkHost,
                    'path' => [
                        '/baozun/pim/env',
                        $envPath
                    ]
                ]);
                $load->loadToEnv();
            }

            $this->server->run();

            $time = time() - $start;
            $output->writeln(PHP_EOL . "End of Server, time consuming: {$time}s");

        } catch (Throwable $throwable) {
            $output->writeln("ERROR: {$throwable->getMessage()}");
        }
    }
}