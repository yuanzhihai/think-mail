<?php

namespace mailer\facade;

use think\Facade;

/**
 * Class Mailer
 *
 * @package mailer\facade
 * @mixin \mailer\Mailer
 */
class Mailer extends Facade
{
    protected static function getFacadeClass()
    {
        return \mailer\Mailer::class;
    }
}