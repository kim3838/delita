<?php

namespace App\Observers;

use App\Events\Repositories\AccountCreated;
use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AccountObserver
{
    private string $customNumberAttribute = 'number';

    public function creating(Account $account): bool
    {
        if (empty($account->ulid)) {
            $account->ulid = (string) Str::ulid();
        }

        $this->addCustomNumberAttribute($account);

        return true;
    }

    public function created(Account $account): void
    {
        event(new AccountCreated($account));
    }

    public function addCustomNumberAttribute(Account $account): Account
    {
        $series = 1;

        $dateCreating = Carbon::parse($account->date_registered);

        $seriesUpToDate = Account::query()
            ->whereBetween('date_registered', [
                Carbon::parse($dateCreating)->startOfYear()->toDateTimeString(),
                Carbon::parse($dateCreating)->endOfYear()->toDateTimeString()
            ])->count();

        $series = $series + $seriesUpToDate;

        $yearCreating = $dateCreating->year;
        $series = str_pad($series,3, '0',STR_PAD_LEFT);
        $prefix = _str_random(8) . _now_timestamp();

        $number = "{$prefix}{$yearCreating}-{$series}";

        $account->{$this->customNumberAttribute} = $number;

        return $account;
    }
}
