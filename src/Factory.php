<?php

namespace Laravelsms\Sms;

class Factory
{
    protected $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function getFactories()
    {
        $agents = $this->app['config']['sms.agents'];

        $factories = [];

        foreach ($agents as $key => $value) {
            $factories[$key] = __NAMESPACE__ . '\Agents\\' . $value['executableFile'];
        }

        return $factories;
    }
}
