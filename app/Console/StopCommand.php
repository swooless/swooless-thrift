<?php declare(strict_types=1);

namespace App\Console;

use App\ThriftServer;
use DI\Annotation\Inject;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class StopCommand extends Command
{
    /**
     * @Inject()
     * @var ThriftServer
     */
    private $server;

    protected function configure()
    {
        $this->setName('stop')
            ->setDescription('Stop Server');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->server->stop();
        } catch (Throwable $throwable) {
            $output->writeln("ERROR: {$throwable->getMessage()}");
        }
    }
}