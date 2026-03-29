<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Enums\Deduction;
use App\Enums\Formulable;
use App\Exports\ContributionExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Contribution\ExportContributionRequest;
use App\Http\Requests\Contribution\ListContributionRequest;
use App\Transformers\Contribution\ExportTransformer;
use App\Transformers\Contribution\ListTransformer;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;

class ContributionController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementDetailRepository $repository
    ){}

    public array $formulableTypesFilter = [
        Formulable::DEDUCTIONS->value
    ];

    public array $componentTypesFilter = [
        Deduction::STATUTORY_CONTRIBUTION->value
    ];

    public function index(ListContributionRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $filters->formulable_types = $this->formulableTypesFilter;
            $filters->component_types = $this->componentTypesFilter;

            $contributions = $this->repository->paginate($filters, ['salary_statement']);

            return ResponseJson::successfulResponse(Fractal::collection(
                $contributions,
                ListTransformer::class
            ));
        }

        abort(404);
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function export(ExportContributionRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $filters->formulable_types = $this->formulableTypesFilter;
            $filters->component_types = $this->componentTypesFilter;

            $data = $this->repository->list($filters, ['salary_statement']);
            $data = Fractal::collection($data, ExportTransformer::class)['data'];

            $export = new ContributionExport(collect($data));

            return ExcelFacade::download($export, 'contributions.csv', Excel::CSV);
        }

        abort(404);
    }
}
