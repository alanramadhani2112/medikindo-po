<?php

namespace App\Traits;

use App\Models\Scopes\OrganizationScope;

trait BelongsToOrganization
{
    /**
     * The "boot" method of the model.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope());
    }
}
