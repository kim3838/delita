<?php

namespace App\Http\Controllers\Internal;

use App\Blueprint\PayslipServiceInterface;
use App\Blueprint\PrototypeInterface;
use App\Blueprint\Repositories\SalaryStatementRepository;
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

    public function viewPayslip(Request $request, $salaryStatementUlid)
    {
        $salaryStatement = app(SalaryStatementRepository::class)->show($salaryStatementUlid);

        return app(PayslipServiceInterface::class, [$salaryStatement->payroll->company])->view($salaryStatement);
    }

    public function debug(Request $request)
    {
        return $this->debugTimezones($request);
    }

    public function debugSingletonBindings()
    {
        $instance = app(PrototypeInterface::class);
        $instance->setKey(4);
        $instance->showKey();

        $anotherInstance = app(PrototypeInterface::class);
        $anotherInstance->showKey();

        $loops = [1,2];

        foreach ($loops as $value) {
            $loopInstance = app(PrototypeInterface::class);
            $loopInstance->setKey($value);
            $loopInstance->showKey();
        }

        $anotherInstance = app(PrototypeInterface::class);
        $anotherInstance->showKey();

        return ResponseJson::successfulResponse();
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

        $globalCarbonToManila = Carbon::createFromFormat('Y-m-d H:i:s', $globalCarbon->toDateTimeString(), 'Asia/Manila');

        $result = \DB::select("SELECT CONVERT_TZ(UTC_TIMESTAMP(), 'UTC', 'Asia/Manila') AS manila_time");

        $manilaTime = $result[0]->manila_time;

        $response = [
            '00 MYSQL Manila now' => $manilaTime,
            '00 Carbon Manila now' => $globalCarbonToManila->toDateTimeString(),
            '01 Auth Timezone' => $request->user()?->timezone ?? 'Unauthenticated',
            '02 Default Timezone' => date_default_timezone_get(),
            '03 Global Carbon Now' => $globalCarbon->toArray(),
            ...($request->user()?->timezone ? [
                '04 Global to Auth Timezone' => $globalCarbon->setTimezone($request->user()?->timezone)->toDateTimeString(),
            ] : []),
        ];

        return ResponseJson::successfulResponse($response);
    }

    public function debugCteMaxRecursionDepth(Request $request)
    {
        $mysqlCteMaxRecursionDepth = \DB::select("SELECT @@cte_max_recursion_depth AS cte_max_recursion_depth");

        $cteMaxRecursionDepth = $mysqlCteMaxRecursionDepth[0]->cte_max_recursion_depth;

        $response = [
            'CTE Max recursion depth' => $cteMaxRecursionDepth,
        ];

        return ResponseJson::successfulResponse($response);
    }

    public function interferePersistCompany(Request $request)
    {
        //Set `pc`,`pas` @ bootstrap/app.php $middleware->encryptCookies
        $persistCompany = $request->cookie('pc');
        $persistAccountSubscription = $request->cookie('pas');

        $persistCompany = (int)$persistCompany;
        $persistCompany++;

        //Cookie::queue(Cookie::make('persist_company', $persistCompany, 60, '/', '.server.local', 'false', 'false'));
        header('Set-Cookie: pc='.$persistCompany.'; Path=/; Domain=.server.local; Max-Age=3600; SameSite=Lax');

        return ResponseJson::successfulResponse([
            'persist_company' => $persistCompany,
            'persist_account_subscription' => $persistAccountSubscription,
        ]);
    }
}
