<?php declare(strict_types=1);

use App\Console\BuildCommand;
use App\Console\StartCommand;
use App\Console\StopCommand;
use Swooless\Config\Loader\FileLoader;

require __DIR__ . "/../vendor/autoload.php";

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error) {
        error_log(json_encode($error));
    }
});

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
$builder->useAnnotations(false);
$builder->useAutowiring(false);

$builder->addDefinitions(APP_ROOT . '/config/di.php');

try {
    $app = $builder->build();
} catch (Exception $e) {
    error_log($e->getMessage());
    error_log('init error!');
}

$console = new Symfony\Component\Console\Application();
$console->add(new StartCommand());
$console->add(new StopCommand());
$console->add(new BuildCommand());

$app->set('console', $console);

return $app;
