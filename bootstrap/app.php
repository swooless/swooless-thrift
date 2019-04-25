<?php declare(strict_types=1);

use App\Console\BuildCommand;
use App\Console\StartCommand;
use App\Console\StopCommand;
use Swooless\Config\Loader\FileLoader;
use Symfony\Component\Console\Application;

require __DIR__ . "/../vendor/autoload.php";

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        error_log(json_encode($error));
    }
});

// chu
if (!defined("APP_ROOT")) {
    $appRoot = substr(__DIR__, 0, strlen(__DIR__) - 10);
    define('APP_ROOT', $appRoot);
}

// 加载本地配置文件
if (is_file(APP_ROOT . '/.env')) {
    $load = new FileLoader();
    $load->init(['path' => [APP_ROOT . '/.env']]);
    $load->loadToEnv();
}

$builder = new DI\ContainerBuilder(App\Application::class);
$builder->useAnnotations(true);
$builder->useAutowiring(true);
$builder->addDefinitions(APP_ROOT . '/config/di.php');

try {
    $app = $builder->build();
} catch (Exception $e) {
    error_log($e->getMessage());
    error_log('init error!');
}

try {
    $console = $app->get(Application::class);
    $console->add($app->get(StartCommand::class));
    $console->add($app->get(StopCommand::class));
    $console->add($app->get(BuildCommand::class));
} catch (Exception $e) {
    error_log("console init fail!");
    error_log($e->getMessage());
}

$app->set('console', $console);

return $app;
