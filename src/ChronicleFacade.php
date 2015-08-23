<?php

namespace Kenarkose\Transit\Facade;


use Illuminate\Support\Facades\Facade;

class ChronicleFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'chronicle';
    }

}