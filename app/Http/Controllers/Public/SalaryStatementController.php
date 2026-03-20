<?php

namespace App\Http\Controllers\Public;

use App\Blueprint\Repositories\SalaryStatementRepository;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Controllers\Controller;
use App\Transformers\SalaryStatement\ItemTransformer;
use Illuminate\Http\Request;

class SalaryStatementController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementRepository $repository
    ){}

    public function show(Request $request, $token)
    {
        if($request->expectsJson()){

            if (!$token) return ResponseJson::notFoundResponse('Token not found.');

            $decoded = base64_decode($token);

            if ($decoded === false) {
                return ResponseJson::notAcceptableResponse('Invalid token.');
            }

            $values = explode('|', $decoded);

            if(!isset($values[0], $values[1], $values[2])){

                return ResponseJson::notAcceptableResponse('Invalid token.');
            }

            $ulid = $values[0];
            $expires = $values[1];
            $sig = $values[2];

            $expectedSig = substr(hash_hmac('sha256', "$ulid|$expires", config('app.key')), 0, 8);

            if (!hash_equals($expectedSig, $sig)) {
                return ResponseJson::notAcceptableResponse('Invalid signature.');
            }

            if (now()->timestamp > (int)$expires) {
                return ResponseJson::serviceUnavailableResponse('Link expired.');
            }

            $salaryStatement = $this->repository->show($ulid);

            $salaryStatement = $salaryStatement
                ? Fractal::item($salaryStatement, ItemTransformer::class)
                : $salaryStatement;

            return ResponseJson::successfulResponse([
                'salary_statement' => $salaryStatement,
            ]);

        }

        abort(404);
    }
}
