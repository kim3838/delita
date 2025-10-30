<?php

namespace App\Concrete;

use App\Blueprint\ImportInterface;
use App\Exceptions\RepositoryException;
use Illuminate\Container\Container as Application;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;

abstract class BaseImportConcrete implements ImportInterface
{
    protected Application $app;
    protected Model $model;
    protected string $modelAlias;
    protected string $inputFileKey = 'file';
    protected int $sheetIndex = 0;
    protected int $headingRowIndex = 0;
    protected string $exportTemplate;

    abstract public function model(): string;
    abstract public function exportTemplate(): string;
    abstract public function validateData($data, $companyId): array;
    abstract public function resolvedData($data, $companyId): array;

    /**
     * @throws BindingResolutionException
     * @throws RepositoryException
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->exportTemplate = $this->exportTemplate();
        $this->makeModel();
        $this->makeModelAlias();
    }

    /**
     * @throws RepositoryException
     * @throws BindingResolutionException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if(!$model instanceof Model){
            throw new RepositoryException("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    public function makeModelAlias(): false|int|string
    {
        $morphMap = Relation::morphMap();

        return $this->modelAlias = array_search($this->model(), $morphMap, true);
    }

    protected function isActionAuthorized($action, $model): bool
    {
        return Gate::allows($action, $model);
    }

    protected function readValidationRules(): array
    {
        return array_merge($this->afterReadValidationRules(), [
            $this->inputFileKey => [
                'required',
                'file',
                'max:20480',
                'mimes:csv,xlsx,xls',
                'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]
        ]);
    }
    protected function afterReadValidationRules(): array
    {
        return [
            'company_id' => 'required|exists:companies,id',
        ];
    }

    protected function validationMessages(): array
    {
        return [
            'company_id.required' => 'Company is required.',
            'company_id.exists' => 'Company not found.',
            `{$this->inputFileKey}.required` => 'Please select a file to import.',
            `{$this->inputFileKey}.file` => 'The uploaded file is not valid.',
            `{$this->inputFileKey}.max` => 'The file size must not exceed 20MB.',
            `{$this->inputFileKey}.mimes` => 'Only CSV and Excel files (xlsx, xls) are allowed.',
            `{$this->inputFileKey}.mimetypes` => 'Invalid file format. Only CSV and Excel files are supported.'
        ];
    }

    /**
     * @throws ValidationException
     */
    public function read(Request $request): array
    {
        $request->validate($this->readValidationRules(), $this->validationMessages());

        $fileToImport = $request->file($this->inputFileKey);

        $importHeadings = new HeadingRowImport()->toArray($fileToImport);
        $importHeadingsValid = $this->exportTemplateHeadingsFormatted() === $importHeadings[$this->sheetIndex][$this->headingRowIndex];

        if(!$importHeadingsValid){
            throw ValidationException::withMessages(['Invalid headings.']);
        }

        $data = Excel::toArray((object)null, $fileToImport)[$this->sheetIndex];

        array_shift($data);

        $transformedData = $this->transformData($data, $this->exportTemplateHeadingsFormatted());

        return [
            'validated' => $this->validateData($transformedData, $request->company_id)
        ];
    }

    public function reValidate(Request $request): array
    {
        $request->validate($this->afterReadValidationRules(), $this->validationMessages());

        $data = $request->get('re_validate');
        $transformedData = $this->transformData($data, $this->exportTemplateHeadingsFormatted());

        return [
            'validated' => $this->validateData($transformedData, $request->company_id)
        ];
    }

    protected function resolveValidatedRow(&$row, $validationErrors, &$dataToImport): void
    {
        $row['validation_errors'] = $validationErrors;

        $dataToImport[] = $row;
    }

    public function save(Request $request): array
    {
        $request->validate($this->afterReadValidationRules(), $this->validationMessages());

        $data = $request->get('save');
        $transformedData = $this->transformData($data, $this->exportTemplateHeadingsFormatted());

        $validated = $this->validateData($transformedData, $request->company_id);
        $saved = [];

        $noValidationErrors = empty(array_filter($validated, function ($item) {
            return !empty($item['validation_errors']);
        }));

        if($noValidationErrors){

            $saved = $this->resolvedData($validated, $request->company_id);

            $validated = [];
        }

        return [
            'validated' => $validated,
            'saved' => $saved,
        ];
    }

    protected function exportTemplateHeadingsFormatted(): array
    {
        $templateHeadings = app($this->exportTemplate)->headings();

        return HeadingRowFormatter::format($templateHeadings);
    }

    protected function transformData($data, $headings): array
    {
        $transformedData = [];

        foreach ($data as $index => $row) {

            if(isset($row['id'])){

                $rowTemp = $row;

                $rowTemp['validation_errors'] = [];

                $transformedData[] = $rowTemp;

            } else {

                $rowNumber = $index + 2;

                $rowTemp = [
                    'id' => $rowNumber,
                    'row' => $rowNumber,
                ];

                foreach ($headings as $headingIndex => $heading) {
                    $rowTemp[$heading] = isset($row[$headingIndex]) ? trim($row[$headingIndex]) : '';
                }

                $rowTemp['validation_errors'] = [];

                $transformedData[] = $rowTemp;
            }
        }

        return $transformedData;
    }


}
