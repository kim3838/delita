<?php

namespace App\Http\Controllers;

use App\Blueprint\Repositories\SalaryStatementDetailRepository;
use App\Enums\FormulableComponentSubType;
use App\Exports\TaxExport;
use App\Facades\Fractal;
use App\Facades\ResponseJson;
use App\Http\Requests\Tax\ExportTaxRequest;
use App\Http\Requests\Tax\ListTaxRequest;
use App\Transformers\Tax\ExportTransformer;
use App\Transformers\Tax\ListTransformer;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use PhpOffice\PhpSpreadsheet\Exception;

class TaxController extends Controller
{
    public function __construct(
        protected readonly SalaryStatementDetailRepository $repository
    ){}

    public array $componentSubTypesFilter = [
        FormulableComponentSubType::PH_WITHHOLDING_TAX_COMPENSATION->value,
        FormulableComponentSubType::TAX_DEFICIT->value,
        FormulableComponentSubType::TAX_REFUND->value,
    ];

    public function index(ListTaxRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $filters->component_sub_types = $this->componentSubTypesFilter;

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
    public function export(ExportTaxRequest $request)
    {
        if($request->expectsJson()){

            $filters = json_decode($request->get('filters'));
            $filters->component_sub_types = $this->componentSubTypesFilter;

            $data = $this->repository->list($filters, ['salary_statement']);
            $data = Fractal::collection($data, ExportTransformer::class)['data'];

            $export = new TaxExport(collect($data));

            return ExcelFacade::download($export, 'taxes.csv', Excel::CSV);
        }

        abort(404);
    }
}
