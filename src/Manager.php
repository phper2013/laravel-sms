<?php

namespace Laravelsms\Sms;

use InvalidArgumentException;

class Manager
{
    protected $app;
    protected $factories;

    public function __construct($app)
    {
        $this->app = $app;
        $this->factories = new Factory($app);
    }

    public function driver($name = null)
    {
        $factories = $this->factories->getFactories();

        if ($name == 'fallback') {
            $name = $this->getFallbackDriver();
        } else {
            $name = $name ?: $this->getDefaultDriver();
        }

        if (!array_key_exists($name, $factories)) {
            throw new InvalidArgumentException("Driver '$name' is not supported.");
        }

        $className = $factories[$name];
        $config = $this->getConfig($name);

        return new $className($config);
    }

    protected function getConfig($name)
    {
        return $this->app['config']["sms.agents.{$name}"];
    }

    protected function getDefaultDriver()
    {
        return $this->app['config']['sms.default'];
    }

    protected function getFallbackDriver()
    {
        return $this->app['config']['sms.fallback'];
    }
}
