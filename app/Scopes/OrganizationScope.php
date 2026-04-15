<?php

namespace App\Scopes;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class OrganizationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($org = Organization::current()) {
            $builder->where($model->getTable() . '.organization_id', $org->id);
        }
    }
}
