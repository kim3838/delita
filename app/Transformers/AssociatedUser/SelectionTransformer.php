<?php

namespace App\Transformers\AssociatedUser;

use App\Concrete\TransformerAbstractConcrete;
use App\Models\Hydrations\AssociatedUser;
use App\Models\User;
use App\Transformers\User\ListTransformer as UserListTransformer;

class SelectionTransformer extends TransformerAbstractConcrete
{
    public function transform(AssociatedUser $model): array
    {
        $filters = json_decode(request()->get('filters'));

        $user = User::query()->find($model->user_id);

        $associatedCompanies = (!empty($filters->associated_companies) && is_array($filters->associated_companies))
            ? $user->companies->whereIn('id', $filters->associated_companies)->values()
            : $user->companies;

        $mappedAssociatedCompanies = collect(new UserListTransformer()->mapAssociatedCompanies($associatedCompanies));

        $accountRoles = request()->account_id
            ? $this->collectionSummary($user->roles->where('account_id', request()->account_id)->values(), 'name', '')
            : $model->account_roles;

        return [
            'value' => $model->user_id,
            'text' => $model->user_username,
            'id' => $model->user_id,
            'ulid' => $model->user_ulid,
            'username' => $model->user_username,
            'email' => $model->user_email,
            'status' => $model->user_status?->toArray(),
            'email_verified_at' => $model->user_email_verified_at,
            'timezone' => $model->user_timezone,
            'created_by' => $user->createdBy?->name ?? '',
            'associated_company' => $mappedAssociatedCompanies->first(),
            'account_roles' => $accountRoles,
        ];
    }
}
