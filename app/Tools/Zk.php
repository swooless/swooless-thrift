<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 18-11-23
 * Time: 上午9:54
 */

namespace App\Tools;

class Zk
{
    /** @var \Zookeeper */
    private $zk;

    /**
     * Zk constructor.
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function __construct()
    {
        $this->zk = app('zookeeper');
    }

    /**
     * @param string $path
     * @param null|string $value
     * @return string
     * @throws \Exception
     */
    public function createMulti(string $path, $value = null): string
    {
        $path = str_replace('\\', '/', $path);
        $tempPath = '';
        echo $path . PHP_EOL;
        if (!$this->zk->exists($path, function ($result) {
            echo 'RES: ' . $result . PHP_EOL;
        })) {
            echo $path . PHP_EOL;
            $pathArr = explode('/', $path);
            $pathArr = array_filter($pathArr);

            $acl = [
                [
                    'perms' => \Zookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id' => 'anyone',
                ]
            ];

            foreach ($pathArr as $index => $node) {
                $tempPath .= '/' . $node;

                echo $tempPath . PHP_EOL;
                if (!$this->zk->exists($tempPath)) {
                    $createPath = $this->zk->create($tempPath, null, $acl);

                    if ($createPath != $tempPath) {
                        throw new \Exception('create fail');
                    }
                }
            }
        } else {
            $tempPath = $path;
        }

        if (!is_null($value)) {
            $this->zk->set($tempPath, $value);
        }

        echo 'end' . PHP_EOL;

        echo $tempPath . PHP_EOL;
        return $tempPath;
    }

    public function getChildren(string $path): array
    {
        $path = str_replace('\\', '/', $path);
        return $this->zk->getChildren($path);
    }

    public function delete(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        return $this->zk->delete($path);
    }
}