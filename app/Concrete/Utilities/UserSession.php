<?php

namespace App\Concrete\Utilities;

use Illuminate\Support\Facades\DB;

class UserSession
{
    private(set) ?string $userId {
        get {
            return $this->userId;
        }
    }

    public function __construct($userId = null)
    {
        $this->userId = $userId;
    }

    public static function resolve()
    {
        return app(UserSession::class);
    }

    public function setUserId($userId = null)
    {
        $instance = app(UserSession::class);

        $instance->userId = $userId;

        return $instance;
    }

    public function getLastActivityPayload()
    {
        if(empty($this->userId)){
            return 'User id not found.';
        }

        $sessions = collect(
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $this->userId)
                ->orderBy('last_activity', 'desc')
                ->get()
        );

        return $sessions->isEmpty()
            ? 'Session not found.'
            : collect(unserialize(base64_decode($sessions->first()->payload)))->except(['_previous', '_flash'])->all();
    }
}
