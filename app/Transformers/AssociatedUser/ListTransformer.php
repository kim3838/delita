<?php

namespace App\Transformers\AssociatedUser;

use App\Concrete\TransformerAbstractConcrete;
use App\Models\Hydrations\AssociatedUser;
use App\Models\User;
use App\Transformers\User\ListTransformer as UserListTransformer;

class ListTransformer extends TransformerAbstractConcrete
{
    public function transform(AssociatedUser $model): array
    {
        $user = User::query()->find($model->user_id);
        $associatedCompanies = $user->companies->sortBy('code')->values();

        $mappedAssociatedCompanies = new UserListTransformer()->mapAssociatedCompanies($associatedCompanies);

        $accountRoles = request()->account_id
            ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
            : $model->account_roles;

        $associatedCompaniesSummary = $this->collectionSummary($associatedCompanies, 'short_name');

        return [
            'id' => $model->user_id,
            'ulid' => $model->user_ulid,
            'username' => $model->user_username,
            'email' => $model->user_email,
            'status' => $model->user_status?->toArray(),
            'email_verified_at' => $model->user_email_verified_at,
            'timezone' => $model->user_timezone,
            'created_by' => $user->createdBy?->name ?? '',
            'associated_companies' => $mappedAssociatedCompanies,
            'account_roles_summary' => $accountRoles,
            'associated_companies_summary' => $associatedCompaniesSummary
        ];
    }
}
