<?php

if ( ! function_exists('chronicle'))
{

    /**
     * Shorthand for the chronicle instance
     *
     * @return Kenarkose\Chronicle\Chronicle
     */
    function chronicle()
    {
        return app()->make('chronicle');
    }

}