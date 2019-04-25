<?php declare(strict_types=1);

namespace App;

use DI\Container;
use DI\Definition\Source\MutableDefinitionSource;
use DI\Proxy\ProxyFactory;
use Psr\Container\ContainerInterface;

class Application extends Container
{
    /** @var Application */
    protected static $instance;

    public function __construct(
        MutableDefinitionSource $definitionSource = null,
        ProxyFactory $proxyFactory = null,
        ContainerInterface $wrapperContainer = null)
    {
        parent::__construct($definitionSource, $proxyFactory, $wrapperContainer);
        static::$instance = $this;
    }

    public static function getInstance(): Application
    {
        return static::$instance;
    }
}