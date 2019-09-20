<?php

namespace Modules\Admin\Managers\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Core\Http\Controllers\APIListController;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Admin\Managers\Models\Manager;

class ManageManagersListApiController extends APIListController
{
    protected $caption = 'admin_managers::manage.managers';
    protected $source = 'manage/api/module/admin_managers/managers_list/managers';

    protected $icons = [
        'add' => 'manage/assets/module/admin_managers/system/add-manager.svg',
    ];


    protected $filters = [
        'blocked' => [
            'caption' => 'filters.filter_by_blocked',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'yes',
            'options' => ['yes' => 'filters.filter_value_blocked', 'no' => 'filters.filter_value_unblocked'],
        ],
        'show_deleted' => [
            'caption' => 'filters.filter_by_deleted',
            'type' => 'checkbox',
            'enabled' => false,
            'value' => 'yes',
            'options' => ['yes' => 'filters.filter_value_deleted', 'only_deleted' => 'filters.filter_value_only_deleted'],
        ],
    ];

    protected $order = [
        'current' => 'id',
        'direction' => 'asc',
        'fields' => [
            'id' => 'admin_managers::manage.sort_by_id',
            'email' => 'admin_managers::manage.sort_by_email',
            'last_name' => 'admin_managers::manage.sort_by_last_name',
            'first_name' => 'admin_managers::manage.sort_by_first_name',
            'middle_name' => 'admin_managers::manage.sort_by_middle_name',
            'nickname' => 'admin_managers::manage.sort_by_nickname',
        ],
    ];

    protected $search = [
        'nickname' => [
            'caption' => 'admin_managers::manage.search_by_nickname',
            'default' => true,
        ],
        'email' => [
            'caption' => 'admin_managers::manage.search_by_email',
            'default' => true,
        ],
        'phone' => [
            'caption' => 'admin_managers::manage.search_by_phone',
            'default' => true,
        ],
        'last_name' => [
            'caption' => 'admin_managers::manage.search_by_last_name',
            'default' => true,
        ],
        'first_name' => [
            'caption' => 'admin_managers::manage.search_by_first_name',
            'default' => true,
        ],
        'middle_name' => [
            'caption' => 'admin_managers::manage.search_by_middle_name',
            'default' => true,
        ],
    ];

    /**
     * Returns list component with associated settings.
     *
     * @return  JsonResponse
     */
    public function getIndex(): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::list')) {
            return $this->returnNotAuthorizedResponse();
        }

        return $this->responseListComponent();
    }

    /**
     * Get list of managers with sorting, filters and search.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postManagers(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::list')) {
            return $this->returnNotAuthorizedResponse();
        }

        $order = $request->input('order');
        $filters = $request->input('filters');
        $search = $request->input('search');

        $managers = $this->makeQuery();

        $managers = $this->applyOrder($managers, $order);
        $managers = $this->applyFilters($managers, $filters);
        $managers = $this->applySearch($managers, $search);

        $managers = $managers->paginate(50);

        /** @var Collection $managers */
        if ($managers->count() > 0) {
            $managers->transform(function ($manager) {
                return $this->formatUser($manager);
            });
        }

        return response()->json($managers);
    }

    /**
     * Make base list query.
     *
     * @return  EloquentBuilder
     */
    protected function makeQuery(): EloquentBuilder
    {
        /** @var EloquentBuilder $query */
        $query = Manager::query()->select('managers.*');
        $query = $query
            ->leftJoin('manager_details', 'manager_details.manager_id', '=', 'managers.id')
            ->addSelect([
                'manager_details.first_name as details_first_name',
                'manager_details.middle_name as details_middle_name',
                'manager_details.last_name as details_last_name',
                'manager_details.phone as details_phone',
                'manager_details.nickname as details_nickname',
            ])
            ->groupBy([
                'managers.id',
                'manager_details.first_name',
                'manager_details.middle_name',
                'manager_details.last_name',
                'manager_details.phone',
                'manager_details.nickname',
            ]);

        return $query;
    }

    /**
     * Format user record for displaying in list.
     *
     * @param Manager $manager
     *
     * @return  array
     */
    protected function formatUser(Manager $manager): array
    {
        $id = $manager->getAttribute('id');
        $name = trim(
            $manager->getAttribute('details_last_name') . ' ' .
            $manager->getAttribute('details_first_name') . ' ' .
            $manager->getAttribute('details_middle_name')
        );
        $nickname = $manager->getAttribute('details_nickname');
        $email = $manager->getAttribute('email');
        $blocked = $manager->getAttribute('blocked');
        $phone = $manager->getAttribute('details_phone');
        $isDeleted = $manager->getAttribute('deleted_at') !== null;

        return $this->makeListRecord(
            $id,
            $name,
            $nickname,
            null,
            [$email, $phone],
            !$blocked,
            $isDeleted
        );
    }

    /**
     * Apply order to query.
     *
     * @param EloquentBuilder $query
     * @param array $order
     *
     * @return  EloquentBuilder
     */
    protected function applyOrder(EloquentBuilder $query, $order): EloquentBuilder
    {
        $direction = $order['direction'];
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = $this->order['direction'];
        }

        switch ($order['by'] ?? '') {
            case 'id':
                $query->orderBy('id', $direction);
                break;
            case 'email':
                $query->orderBy('email', $direction);
                break;
            case 'last_name':
                $query->orderByRaw('ISNULL(details_last_name) asc')->orderBy('details_last_name', $direction);
                break;
            case 'first_name':
                $query->orderByRaw('ISNULL(details_first_name) asc')->orderBy('details_first_name', $direction);
                break;
            case 'middle_name':
                $query->orderByRaw('ISNULL(details_middle_name) asc')->orderBy('details_middle_name', $direction);
                break;
            case 'nickname':
                $query->orderByRaw('ISNULL(details_middle_name) asc')->orderBy('details_nickname', $direction);
                break;
            default:
                $query->orderBy('id', $direction);
        }
        return $query;
    }

    /**
     * Apply filters to query.
     *
     * @param EloquentBuilder $query
     * @param array $filters
     *
     * @return  EloquentBuilder
     */
    protected function applyFilters(EloquentBuilder $query, $filters): EloquentBuilder
    {
        if (isset($filters['blocked'])) {
            $query->where('blocked', $filters['blocked'] === 'yes');
        }

        if (isset($filters['show_deleted'])) {
            if ($filters['show_deleted'] === 'yes') {
                $query->withTrashed();
            } elseif ($filters['show_deleted'] === 'only_deleted') {
                $query->onlyTrashed();
            }
        }
        return $query;
    }

    /**
     * Apply search to query.
     *
     * @param EloquentBuilder $query
     * @param array $search
     *
     * @return  EloquentBuilder
     */
    protected function applySearch(EloquentBuilder $query, $search): EloquentBuilder
    {
        if (!empty($search['subject']) && !empty($search['fields'])) {

            $subject = str_replace('*', '%', $search['subject']);
            $fields = explode(',', $search['fields']);

            $query = $query->where(static function ($q) use ($fields, $subject) {
                /** @var Builder $q */
                if (in_array('id', $fields, true)) {
                    $q->orWhere('managers.id', 'LIKE', $subject);
                }
                if (in_array('email', $fields, true)) {
                    $q->orWhere('managers.email', 'LIKE', $subject);
                }
                if (in_array('phone', $fields, true)) {
                    $q->orWhere('manager_details.phone', 'LIKE', $subject);
                }
                if (in_array('last_name', $fields, true)) {
                    $q->orWhere('manager_details.last_name', 'LIKE', $subject);
                }
                if (in_array('first_name', $fields, true)) {
                    $q->orWhere('manager_details.first_name', 'LIKE', $subject);
                }
                if (in_array('middle_name', $fields, true)) {
                    $q->orWhere('manager_details.middle_name', 'LIKE', $subject);
                }
                if (in_array('nickname', $fields, true)) {
                    $q->orWhere('manager_details.nickname', 'LIKE', $subject);
                }
            });
        }
        return $query;
    }

    /**
     * Get link to add manager if can.
     *
     * @return  string|null
     */
    protected function getAddLink(): ?string
    {
        return AdminAuthorization::can('admin_managers::add') ? 'admin_managers::managers_add' : null;
    }

    /**
     * Get link to edit manager if can.
     *
     * @return  string|null
     */
    protected function getEditLink(): ?string
    {
        if (
            AdminAuthorization::can('admin_managers::view')
            || AdminAuthorization::can('admin_managers::edit')
            || AdminAuthorization::can('admin_managers::edit_login')
        ) {
            return 'admin_managers::managers_edit';
        }

        return null;
    }

    /**
     * Get edit link if can.
     *
     * @return  string
     */
    protected function getEnableLink(): ?string
    {
        return AdminAuthorization::can('admin_managers::disable') ? 'manage/api/module/admin_managers/manager_actions/enable' : null;
    }

    /**
     * Get edit link if can.
     *
     * @return  string
     */
    protected function getDisableLink(): ?string
    {
        return AdminAuthorization::can('admin_managers::disable') ? 'manage/api/module/admin_managers/manager_actions/disable' : null;
    }

    /**
     * Get edit link if can.
     *
     * @return  string
     */
    protected function getDeleteLink(): ?string
    {
        return AdminAuthorization::can('admin_managers::delete') ? 'manage/api/module/admin_managers/manager_actions/delete' : null;
    }

    /**
     * Get edit link if can.
     *
     * @return  string
     */
    protected function getRestoreLink(): ?string
    {
        return AdminAuthorization::can('admin_managers::delete') ? 'manage/api/module/admin_managers/manager_actions/restore' : null;
    }
}