<?php declare(strict_types=1);

namespace App\Console;

use Phar;
use Throwable;
use ArrayIterator;
use DI\NotFoundException;
use DI\DependencyException;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this->setName('build')
            ->setDescription('Build phar package');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            shell_exec("composer install --no-dev -d " . APP_ROOT);
            $this->build();
            shell_exec("composer install -d " . APP_ROOT);
        } catch (Throwable $throwable) {
            $output->writeln("ERROR: {$throwable->getMessage()}");
            return 254;
        }

        return 0;
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function build()
    {
        $files = $this->filterFile();
        $this->bale($files);
    }


    private function filterFile(): array
    {
        $folder = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_ROOT));
        $items = [];

        foreach ($folder as $item) {
            $path = $item->getPathName();

            //跳过不需要的文件和目录
            if ($this->jumpDir($path)) {
                continue;
            }

            $filename = pathinfo($path, PATHINFO_BASENAME);

            // 过滤隐藏文件
            if (substr($filename, 0, 1) != '.' || $filename == '.env') {
                $items[substr($path, strlen(APP_ROOT))] = $path;
                echo "add file: $item \n";
            }
        }

        return $items;
    }

    private function jumpDir($path): bool
    {
        $ignore = ['.git', '.idea', 'tests', 'logs', 'dist'];

        foreach ($ignore as $item) {
            if (strpos($path, '/' . $item . '/')) {
                return true;
            }
        }

        return false;
    }

    /**
     * 打包成 phar 包
     * @param array $files
     * @throws DependencyException
     * @throws NotFoundException
     */
    private function bale(array $files)
    {
        $name = strtolower(app('SERVER_NAME'));
        $file = APP_ROOT . "/dist/{$name}.phar";
        if (is_file($file)) unlink($file);

        $phar = new Phar($file);
        $phar->startBuffering();
        $phar->buildFromIterator(new ArrayIterator($files));
        $phar->delete('phpunit.xml');
        $phar->delete('composer.lock');
        $phar->delete('composer.json');
        $phar->delete('env-example');
        $phar->delete('Dockerfile');
        $phar->setDefaultStub("index.php");
        $phar->stopBuffering();
    }
}