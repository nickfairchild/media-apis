<?php

namespace Nick\Media\Facades;

use Illuminate\Support\Facades\Facade;

class FanartFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'fanart';
    }
}