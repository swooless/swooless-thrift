<?php declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ClassScanTest extends TestCase
{
    public function testLoad()
    {
        $folder = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APP_ROOT . '/app/Console'));

        foreach ($folder as $item) {
            $path = $item->getPathName();

            //跳过不需要的文件和目录
            if ($this->jumpDir($path)) {
                continue;
            }

            $fileName = pathinfo($path, PATHINFO_BASENAME);
            $fileExt = pathinfo($path, PATHINFO_EXTENSION);
            $className = substr($fileName, 0, strlen($fileName) - strlen($fileExt) - 1);

            // 过滤隐藏文件
            if ('php' == $fileExt) {
                var_dump($className);

                echo "add file: $item \n";
            }
        }

        self::assertTrue(true);
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
}