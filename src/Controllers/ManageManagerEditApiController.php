<?php

namespace Modules\Admin\Managers\Controllers;

use Carbon\Carbon;
use Core\Foundation\Templater\Templater;
use Core\Http\Controllers\APIFormController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Admin\Managers\AdminManagers;
use Modules\Admin\Managers\Models\Manager;
use Modules\Admin\Managers\Models\ManagerDetails;
use Modules\Admin\PermissionGroups\Models\PermissionGroup;

class ManageManagerEditApiController extends APIFormController
{
    public $addCaption = 'admin_managers::manage.add_manager';
    public $editCaption = 'admin_managers::manage.edit_manager';
    public $create = 'manage/api/module/admin_managers/manager_edit/create';
    public $save = 'manage/api/module/admin_managers/manager_edit/save';
    public $redirect = '/managers/edit/';

    /**
     * Make manager add form.
     *
     * @return  JsonResponse
     */
    public function getAdd(): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(AdminManagers::path('Templates' . DIRECTORY_SEPARATOR . 'manager_add.php'));

        $template->fillDefaults();
        $template = $this->markDefaultPolicies($template);

        return $this->responseFormComponent(0, $template, $this->addCaption, $this->create);
    }

    /**
     * Make user add form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function getEdit(Request $request): JsonResponse
    {
        if (
            !AdminAuthorization::can('admin_managers::view')
            && !AdminAuthorization::can('admin_managers::edit')
            && !AdminAuthorization::can('admin_managers::edit_login')
        ) {
            return $this->returnNotAuthorizedResponse();
        }
        /** @var Manager $manager */
        $id = $request->input('id');
        $manager = Manager::withTrashed()->with('details')->where('id', $id)->firstOrFail();

        $template = $this->makeTemplate($manager, 'manager_edit.php');
        $template = $this->markDefaultPolicies($template);

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Create new manager.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postCreate(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::add')) {
            return $this->returnNotAuthorizedResponse();
        }

        $template = new Templater(AdminManagers::path('Templates' . DIRECTORY_SEPARATOR . 'manager_add.php'));
        $template->resolvePermissions();
        $template->fillValuesFromRequest($request);

        if (!$template->validate()) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        /** @var Manager $manager */
        $manager = $this->updateUserData(new Manager(), $values);

        // Refill template
        $template = $this->makeTemplate($manager, 'manager_edit.php');
        $id = $manager->getAttribute('id');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save, $this->redirect . $id);
    }

    /**
     * Save user.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postSave(Request $request): JsonResponse
    {
        if (
            !AdminAuthorization::can('admin_managers::view')
            && !AdminAuthorization::can('admin_managers::edit')
            && !AdminAuthorization::can('admin_managers::edit_login')
        ) {
            return $this->returnNotAuthorizedResponse();
        }
        /** @var Manager $manager */
        $id = $request->input('id');
        $manager = Manager::withTrashed()->with('details')->where('id', $id)->firstOrFail();
        $template = new Templater(AdminManagers::path('Templates' . DIRECTORY_SEPARATOR . 'manager_edit.php'));
        $template->resolvePermissions();
        $template->fillValuesFromRequest($request);

        if (!$template->validate(['id' => $manager->getAttribute('id')])) {
            return $this->responseValidationError($template->getValidationErrors());
        }

        $values = $template->getEditableValues();

        $manager = $this->updateUserData($manager, $values);

        // Refill template
        $template = $this->makeTemplate($manager, 'manager_edit.php');

        return $this->responseFormComponent($id, $template, $this->editCaption, $this->save);
    }

    /**
     * Fill template with data.
     *
     * @param string $filename
     * @param Manager $manager
     *
     * @return  Templater
     */
    protected function makeTemplate(Manager $manager, $filename): Templater
    {
        $template = new Templater(AdminManagers::path('Templates' . DIRECTORY_SEPARATOR . $filename));

        $manager->load('details');

        $details = $this->getAttributes($manager->getRelation('details'), [
            'last_name' => 'details_last_name',
            'first_name' => 'details_first_name',
            'middle_name' => 'details_middle_name',
            'phone' => 'details_phone',
            'nickname' => 'details_nickname',
        ]);

        $template->fillValuesFromObject($manager);

        $template->setValues(array_merge($details, ['password' => null]));

        if ($manager->exists) {
            $policies = $this->getPermissionsGroups($manager->getAttribute('id'));
            $template->setValues(['policies' => $policies]);
        }

        $template = $this->markDefaultPolicies($template);

        return $template;
    }

    /**
     * Update manager's data
     *
     * @param Manager $manager
     * @param array $data
     *
     * @return  Manager
     */
    protected function updateUserData(Manager $manager, array $data): Manager
    {
        // Check for password
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
            $data['last_password_change'] = Carbon::now();
        } else if (!$manager->exists) {
            $data['password'] = bcrypt(str_random(8));
        } else {
            unset($data['password']);
        }

        $this->setAttributes($manager, $data, ['blocked', 'password', 'email', 'last_password_change']);

        $manager->save();

        $manager->loadMissing('details');

        $details = $manager->getRelation('details');

        $details = $this->setAttributes($details ?? new ManagerDetails(['manager_id' => $manager->getAttribute('id')]),
            $data, [
                'details_last_name' => 'last_name',
                'details_first_name' => 'first_name',
                'details_middle_name' => 'middle_name',
                'details_phone' => 'phone',
                'details_nickname' => 'nickname',
            ]);

        $details->save();

        if (isset($data['policies'])) {
            $this->syncPermissionGroups($manager->getAttribute('id'), $data['policies']);
        }

        return $manager;
    }

    /**
     * Mark default policies as checked and disabled.
     *
     * @param Templater $template
     *
     * @return  Templater
     */
    protected function markDefaultPolicies(Templater $template): Templater
    {
        $default = PermissionGroup::where('default', 1)->pluck('id')->toArray();

        $policiesField = $template->getField('policies');
        $policiesField['disabled'] = $default;
        $policiesField['checked'] = $default;
        $template->setField('policies', $policiesField);
        return $template;
    }

    /**
     * Sync permissions groups to manager.
     *
     * @param int $id
     * @param array $groups
     *
     * @return  void
     */
    protected function syncPermissionGroups(int $id, array $groups): void
    {
        DB::table('manager_has_permission_group')
            ->where('manager_id', $id)
            ->whereNotIn('group_id', $groups)
            ->delete();

        $actual = $this->getPermissionsGroups($id);

        $missing = array_diff($groups, $actual);

        foreach ($missing as &$item) {
            $item = ['manager_id' => $id, 'group_id' => $item];
        }
        unset($item);

        DB::table('manager_has_permission_group')->insert($missing);
    }

    /**
     * Get permissions group list for manager by id.
     *
     * @param int $id
     *
     * @return  array
     */
    protected function getPermissionsGroups(int $id): array
    {
        $permissions = DB::table('manager_has_permission_group')
            ->where('manager_id', $id)
            ->pluck('group_id');

        return $permissions->toArray();
    }
}