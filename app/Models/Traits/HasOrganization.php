<?php

namespace App\Models\Traits;

use App\Models\Organization;
use App\Scopes\OrganizationScope;
use Illuminate\Database\Eloquent\Model;

trait HasOrganization
{
    protected static function bootHasOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());

        static::creating(function (Model $model) {
            if (empty($model->organization_id) && Organization::current()) {
                $model->organization_id = Organization::current()->id;
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
