<?php

namespace Modules\Admin\Managers\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

//use Core\Modules\AccessControl\Traits\CanResetPassword;
//use Modules\Auth\Notifications\ResetPassword;

class Manager extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    protected $dates = [
        'last_password_change', 'last_login', 'created_at', 'updated_at', 'deleted_at',
    ];

    /**
     * Update last login time.
     *
     * @return  void
     */
    public function updateLastLogin(): void
    {
        $this->setAttribute('last_login', Carbon::now());

        $this->save();
    }

    /**
     * Relation for user details.
     *
     * @return  HasOne
     */
    public function details(): HasOne
    {
        return $this->hasOne(ManagerDetails::class);
    }
}
