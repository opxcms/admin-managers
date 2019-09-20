<?php


namespace Modules\Admin\Managers\Controllers;

use Core\Traits\NotAuthorizedResponse;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Modules\Admin\Authorization\AdminAuthorization;
use Modules\Admin\Managers\Models\Manager;
use Exception;

class ManageManagerActionsApiController extends BaseController
{
    use NotAuthorizedResponse;

    /**
     * Delete users with given ids.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @throws Exception
     */
    public function postDelete(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::delete')) {
            return $this->returnNotAuthorizedResponse();
        }

        $ids = $request->all();

        /** @var EloquentBuilder $managers */
        $managers = Manager::query()->whereIn('id', $ids)->get();

        if ($managers->count() > 0) {
            /** @var Manager $manager */
            foreach ($managers as $manager) {
                $manager->delete();
            }
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Restore users with given ids.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function postRestore(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::delete')) {
            return $this->returnNotAuthorizedResponse();
        }

        $ids = $request->all();

        /** @var EloquentBuilder $managers */
        $managers = Manager::query()->whereIn('id', $ids)->onlyTrashed()->get();

        if ($managers->count() > 0) {
            /** @var Manager $manager */
            foreach ($managers as $manager) {
                $manager->restore();
            }
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Unblock users with given ids.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postEnable(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::disable')) {
            return $this->returnNotAuthorizedResponse();
        }

        $ids = $request->all();

        /** @var EloquentBuilder $managers */
        $managers = Manager::query()->whereIn('id', $ids)->get();

        if ($managers->count() > 0) {
            /** @var Manager $manager */
            foreach ($managers as $manager) {
                if ($manager->blocked) {
                    $manager->blocked = false;
                    $manager->save();
                }
            }
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Block users with given ids.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function postDisable(Request $request): JsonResponse
    {
        if (!AdminAuthorization::can('admin_managers::disable')) {
            return $this->returnNotAuthorizedResponse();
        }

        $ids = $request->all();

        /** @var EloquentBuilder $managers */
        $managers = Manager::query()->whereIn('id', $ids)->get();

        if ($managers->count() > 0) {
            /** @var Manager $manager */
            foreach ($managers as $manager) {
                if (!$manager->blocked) {
                    $manager->blocked = true;
                    $manager->save();
                }
            }
        }

        return response()->json(['message' => 'success']);
    }
}