<?php

namespace Modules\Admin\Managers;

use Core\Foundation\Module\BaseModule;
use Modules\Admin\Managers\Models\Manager;
use Modules\Admin\Managers\Models\ManagerDetails;

class Managers extends BaseModule
{
    /** @var string  Module name */
    protected $name = 'admin_managers';

    /** @var string  Module path */
    protected $path = __DIR__;

    /**
     * Get list of managers.
     *
     * @return  array
     */
    public function getManagersList(): array
    {
        $managers = Manager::with('details')->get();

        $all = [];

        foreach ($managers as $manager) {
            /** @var Manager $manager */
            /** @var ManagerDetails $details */
            $details = $manager->details;
            $name = $details ? $details->fullName() : $manager->getAttribute('email');
            $nickname = $details ? $details->nickname() : '';

            $all[] = [
                'id' => $manager->getAttribute('id'),
                'caption' => $name . ($nickname ? ' "' . $nickname . '"' : ''),
            ];
        }

        return $all;
    }

    /**
     * Get emails of active managers.
     *
     * @param array|null $except
     *
     * @return  array
     */
    public function getActiveManagersEmails(?array $except = null): array
    {
        $emails = Manager::where('blocked', false)->pluck('email')->toArray();

        if($except !== null) {
            $emails = array_diff($emails, $except);
        }

        return $emails;
    }
}
