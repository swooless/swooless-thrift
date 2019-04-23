<?php declare(strict_types=1);

use App\Application;
use App\Tools\Logger;
use DI\DependencyException;
use DI\NotFoundException;

if (!function_exists('logger')) {
    /**
     * Logger
     *
     * @param $info
     * @param $level
     * @return string
     */
    function logger($info, $level = \Monolog\Logger::DEBUG)
    {
        Logger::log($info, $level);
    }
}

if (!function_exists('redis')) {
    /**
     * @return \Swoole\Coroutine\Redis | Redis
     */
    function redis()
    {
        try {
            if (!app()->has('redis')) {
                if (PHP_SAPI == "cli" && extension_loaded('swoole')) {
                    $redis = new \Swoole\Coroutine\Redis();
                } else {
                    $redis = new Redis();
                }
                $redis->connect(env('REDIS_SERVER_HOST', '127.0.0.1'), intval(env('REDIS_SERVER_PORT', '6379')));
                $redis->auth(env('REDIS_SERVER_PWD', null));
                app()->set('redis', $redis);
            }

            return app('redis');
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}

if (!function_exists('app')) {

    /**
     * @param string $make
     * @return Application|mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    function app(string $make = null)
    {
        if (is_null($make)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($make);
    }
}

if (!function_exists('env')) {

    function env($varName, $default)
    {
        $result = getenv($varName);

        if ($result === false) {
            return $default;
        }

        return $result;
    }
}
