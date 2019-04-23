<?php
/**
 * Created by PhpStorm.
 * User: alan
 * Date: 18-11-14
 * Time: 下午3:19
 */

namespace App\Tools;

use Monolog\Handler\StreamHandler;

class Logger
{
    /** @var Logger */
    private static $log;

    public static function log($info, $level = \Monolog\Logger::DEBUG)
    {
        $logPath = realpath(env('SWOOLE_LOGS_PATH', APP_ROOT . '/logs'));

        if ($logPath == false) {
            $logPath = sys_get_temp_dir();
        }

        if (!is_dir($logPath)) {
            mkdir($logPath, 0666, true);
        }

        $logPath .= DIRECTORY_SEPARATOR . date('Ymd');

        if (!(self::$log instanceof \Monolog\Logger)) {
            self::$log = new \Monolog\Logger(env('SERVER_NAME', 'swoole-app'));
            try {
                self::$log->pushHandler(new StreamHandler($logPath . '-app-debug.log', \Monolog\Logger::DEBUG));
                self::$log->pushHandler(new StreamHandler($logPath . '-app-error.log', \Monolog\Logger::ERROR));
            } catch (\Exception $e) {

            }
        }

        if (!is_string($info)) {
            $info = json_encode($info);
        }

        self::$log->log($level, $info);
    }
}