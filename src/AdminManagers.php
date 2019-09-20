<?php

namespace Modules\Admin\Managers;

use Illuminate\Support\Facades\Facade;

/**
 * @method  static array getManagersList()
 * @method  static array getActiveManagersEmails(?array $except = null)
 * @method  static boolean check()
 * @method  static string name()
 * @method  static string get($key)
 * @method  static string path($path = '')
 * @method  static string trans($key, $parameters = [], $locale = null)
 * @method  static array|string|null  config($key = null)
 * @method  static mixed view($view)
 */
class AdminManagers extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'admin_managers';
    }
}
