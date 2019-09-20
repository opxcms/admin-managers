<?php

namespace Modules\Admin\Managers\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ManagerDetails extends Model
{
    use SoftDeletes;

    protected $table = 'manager_details';

    protected $fillable = ['manager_id', 'phone', 'last_name', 'first_name', 'middle_name'];

    public $timestamps = false;

    /**
     * Relation to user.
     *
     * @return  BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    /**
     * Format full name.
     *
     * @return  string|null
     */
    public function fullName(): ?string
    {
        return trim(
            $this->getAttribute('last_name') . ' ' .
            $this->getAttribute('first_name') . ' ' .
            $this->getAttribute('middle_name'));
    }

    /**
     * Format full name.
     *
     * @return  string|null
     */
    public function nickname(): ?string
    {
        return $this->getAttribute('nickname');
    }
}
