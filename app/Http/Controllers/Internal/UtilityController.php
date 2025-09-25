<?php

namespace App\Http\Controllers\Internal;

use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Models\Prototype;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class UtilityController extends Controller
{
    public function hit(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    public function post(Request $request)
    {
        return ResponseJson::successfulResponse();
    }

    public function debug(Request $request)
    {
        return $this->debugTimezones($request);
    }

    public function debugCalendarAssets()
    {
        $now = Carbon::now();

        _debug([
            '$now' => $now->toDateTimeString(),
            '$now day of week' => $now->dayOfWeek,
            'monday' => Carbon::THURSDAY,
            'weekday' => $now->weekday(),
        ]);

        return ResponseJson::successfulResponse();
    }

    public function debugAuthorization()
    {
        Gate::authorize('create', Prototype::class);

        return ResponseJson::successfulResponse();
    }

    public function debugTimezones(Request $request)
    {
        $globalCarbon = Carbon::parse('now');

        $dateTimeAsIs = Carbon::createFromFormat('Y-m-d H:i:s', '2025-09-02 10:00:00', 'Asia/Manila');

        $response = [
            '01 Auth Timezone' => $request->user()?->timezone ?? 'Unauthenticated',
            '02 Default Timezone' => date_default_timezone_get(),
            '03 Global Carbon Now' => $globalCarbon->toArray(),
            ...($request->user()?->timezone ? [
                '04 Global to Auth Timezone' => $globalCarbon->setTimezone($request->user()?->timezone)->toDateTimeString(),
            ] : []),
        ];

        return ResponseJson::successfulResponse($response);
    }

    public function interferePersistCompany(Request $request)
    {
        //Set `persist_company` @ bootstrap/app.php $middleware->encryptCookies
        $persistCompany = $request->cookie('persist_company');

        $persistCompany = (int)$persistCompany;
        $persistCompany++;

        //Cookie::queue(Cookie::make('persist_company', $persistCompany, 60, '/', '.server.local', 'false', 'false'));
        header('Set-Cookie: persist_company='.$persistCompany.'; Path=/; Domain=.server.local; Max-Age=3600; SameSite=Lax');

        return ResponseJson::successfulResponse([
            'persist_company' => $persistCompany
        ]);
    }
}
