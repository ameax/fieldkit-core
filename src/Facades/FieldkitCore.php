<?php

namespace Ameax\FieldkitCore\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ameax\FieldkitCore\FieldkitCore
 */
class FieldkitCore extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ameax\FieldkitCore\FieldkitCore::class;
    }
}
