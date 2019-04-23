<?php declare(strict_types=1);

namespace App\Console;

use ArrayIterator;
use Phar;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build phar package!');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            shell_exec("composer install --no-dev -d " . APP_ROOT);
            $this->build($output);
            shell_exec("composer install -d " . APP_ROOT);
        } catch (Throwable $throwable) {
            $output->writeln("ERROR: {$throwable->getMessage()}");
        }
    }

    private function build(OutputInterface $output)
    {
        $name = strtolower(app('SERVER_NAME'));
        $file = APP_ROOT . "/dist/{$name}.phar";
        if (is_file($file)) unlink($file);
        $folder = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_ROOT));
        $items = [];
        foreach ($folder as $item) {
            //排除掉不需要的文件和目录
            if (strpos($item->getPathName(), '/.git/')) {
                continue;
            }
            if (strpos($item->getPathName(), '/.idea/')) {
                continue;
            }
            if (strpos($item->getPathName(), '/tests/')) {
                continue;
            }
            if (strpos($item->getPathName(), '/logs/')) {
                continue;
            }
            if (strpos($item->getPathName(), '/bin/')) {
                continue;
            }

            $output->writeln("add file: $item");

            $filename = pathinfo($item->getPathName(), PATHINFO_BASENAME);
            if (substr($filename, 0, 1) != '.' || $filename == '.env') {
                $items[substr($item->getPathName(), strlen(APP_ROOT))] = $item->getPathName();
            }
        }

        $phar = new Phar($file);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($items));
        $phar->delete('phpunit.xml');
        $phar->setDefaultStub("index.php");
        $phar->stopBuffering();
    }
}