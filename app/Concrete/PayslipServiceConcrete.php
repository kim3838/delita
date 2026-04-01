<?php

namespace App\Concrete;

use App\Blueprint\PayslipServiceInterface;
use App\Enums\PayslipColumn;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\SalaryStatement;
use App\Transformers\SalaryStatement\PayslipTransformer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;
use DevRaeph\PDFPasswordProtect\Exceptions\InputFileNotFoundException;
use DevRaeph\PDFPasswordProtect\Exceptions\InputFileNotSetException;
use DevRaeph\PDFPasswordProtect\Exceptions\OutputFileNotSetException;
use DevRaeph\PDFPasswordProtect\Exceptions\PasswordNotSetException;
use DevRaeph\PDFPasswordProtect\Facade\PDFPasswordProtect;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Mpdf\MpdfException;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use Spatie\LaravelPdf\Facades\Pdf;

class PayslipServiceConcrete implements PayslipServiceInterface
{
    public string $defaultTemplate = 'payslip.default-template';
    public string $defaultFormat = 'A4';
    public string $path = '';
    public string $filename = '';
    public array $params = [];
    public int $signSeconds = 30;

    public function __construct(
        protected ?Company $company
    ){
        $this->path = $this->company->account->number. '/' . $this->company->code. '/payslips/';
    }

    public function filePath(): string
    {
        return $this->path . $this->filename;
    }

    public function view(SalaryStatement $salaryStatement): View
    {
        $this->prepareTemplateParameters($salaryStatement);

        return view($this->defaultTemplate, $this->params);
    }

    public function getSigned(SalaryStatement $salaryStatement): string
    {
        $payslipPathStored = !empty($salaryStatement->payslip_path);

        $signed = '';

        $expiration = now()->addSeconds($this->signSeconds);

        if(!$payslipPathStored){

            $this->generate($salaryStatement);

            $signed = Storage::temporaryUrl($this->filePath(), $expiration);
        }

        if($payslipPathStored && Storage::exists($salaryStatement->payslip_path)){

            $signed = Storage::temporaryUrl($salaryStatement->payslip_path, $expiration);
        }

        return $signed;
    }

    public function generatePassword(SalaryStatement $salaryStatement): string
    {
        $employee = $salaryStatement->employee;

        $password = preg_replace('/\s+/', '', strtolower($employee->family_name)) .
            preg_replace('/\s+/', '', strtolower($employee->number)) .
            $employee->birth_date?->format('Ymd');

        return $password;
    }

    /**
     * @throws InputFileNotFoundException
     * @throws OutputFileNotSetException
     * @throws PdfTypeException
     * @throws CrossReferenceException
     * @throws MpdfException
     * @throws PasswordNotSetException
     * @throws PdfParserException
     * @throws InputFileNotSetException
     */
    public function generate(SalaryStatement $salaryStatement): static
    {
        $this->prepareTemplateParameters($salaryStatement);

        Pdf::view($this->defaultTemplate, $this->params)
            ->disk('s3')
            ->format($this->defaultFormat)
            ->save($this->filePath());

        $password = $this->generatePassword($salaryStatement);

        if(!empty($password)){

            PDFPasswordProtect::setInputFile($this->filePath(), 's3')
                ->setOutputFile($this->filePath(), 's3')
                ->setPassword($password)
                ->secure();
        }

        return $this;
    }

    public function prepareTemplateParameters($salaryStatement): static
    {
        $debugEnabled = false;

        $employee = $salaryStatement->employee;

        $salaryStatement = is_array($salaryStatement)
            ? $salaryStatement
            : Fractal::item($salaryStatement, PayslipTransformer::class);

        $company = $salaryStatement['company'];
        $companyName = $company['name'];
        $companyAddressLine1 = $company['address_line_1'];
        $companyAddressLine2 = $company['address_line_2'];
        $companyCity = $company['city'];
        $companyState = $company['state'];
        $companyPostalCode = $company['postal_code'];
        $companyCityStatePostalCode = collect([
            $companyCity,
            $companyState,
            $companyPostalCode,
        ])->filter()->implode(' ');
        $companyCountryName = $company['country']['name'];
        $companyCountrySubregionName = $company['country']['subregion'];

        $employeeNumber = $salaryStatement['employee_number'];
        $employeeFullName = $salaryStatement['employee_full_name'];
        $employeeDesignation = $salaryStatement['employee_designation']['name'] ?? '';
        $employeeDepartment = $salaryStatement['employee_department']['name'] ?? '';

        $payroll = $salaryStatement['payroll'];
        $payrollYear = $payroll['year'];
        $payrollNumber = $payroll['number'];
        $salaryStatementType = $salaryStatement['type']['text'];
        $payrollMonth = $payroll['month'];
        $payrollMonthReadable = $payroll['month_readable'];
        $payrollYearMonth = $payroll['year'] . ' ' . $payroll['month_readable'];

        $payrollFrequency = $payroll['pay_frequency']['text'];
        $payrollFrequencySequence = $payroll['frequency_sequence']['text'] ?? '';

        $payrollPeriod = $payroll['date_range_readable'];

        $totalDays = $salaryStatement['total_days'];
        $totalDayOffs = $salaryStatement['total_day_offs'];
        $totalWorkDays = $salaryStatement['total_working_days'];
        $totalWorkingRestDays = $salaryStatement['total_working_rest_days'];

        $totalPresent = $salaryStatement['total_present'];
        $totalLeaveWithPay = $salaryStatement['total_leave_with_pay'];
        $totalLeaveWithoutPay = $salaryStatement['total_leave_without_pay'];
        $totalAbsent = $salaryStatement['total_absent'];

        /**
         * Concat employee ulid
         **/
        $this->path = $this->path . $employee->ulid . '/';

        $this->filename = $employeeNumber . '-' . $payrollNumber  . '.pdf';

        $earnings = [];
        $deductions = [];
        $summary = ['gross' => '0.00', 'deduction' => '0.00', 'net' => '0.00'];

        foreach($salaryStatement['statement_details'] as $salaryStatementDetail){
            if($salaryStatementDetail['payslip_payload']['viewable']){
                switch($salaryStatementDetail['payslip_payload']['column']){
                    case PayslipColumn::EARNINGS:
                        $earnings[] = $salaryStatementDetail['payslip_payload'];
                        break;
                    case PayslipColumn::DEDUCTIONS:
                        $deductions[] = $salaryStatementDetail['payslip_payload'];
                        break;
                    case PayslipColumn::SUMMARY:
                        $summary['gross'] = $salaryStatementDetail['payslip_payload']['summary']['gross'];
                        $summary['deduction'] = $salaryStatementDetail['payslip_payload']['summary']['deduction'];
                        $summary['net'] = $salaryStatementDetail['payslip_payload']['summary']['net'];
                        break;
                }
            }

            $split = $salaryStatementDetail['payslip_payload']['split'];

            if(!empty($split)){
                switch($split['column']){
                    case PayslipColumn::EARNINGS:
                        $earnings[] = $split;
                        break;
                    case PayslipColumn::DEDUCTIONS:
                        $deductions[] = $split;
                        break;
                }
            }
        }

        if($debugEnabled){

            _debug([
                'Salary Statement' => $salaryStatement,
                'Payslip Items' => [
                    'Earnings' => $earnings,
                    'Deductions' => $deductions,
                    'Summary' => $summary,
                ],
            ]);
        }

        $renderer = new ImageRenderer(
            new RendererStyle(100, 0),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        $ulid = $salaryStatement['ulid'];

        $expires = Carbon::now()->addHours(24)->timestamp;
        $payload = "{$ulid}|{$expires}";
        $signature = substr(hash_hmac('sha256', $payload, config('app.key')), 0, 8);

        $token = base64_encode("{$payload}|{$signature}");

        $publicSalaryStatementBreakdownUrl = config('app.frontend_url') . "/temp/salary-statement/$token";

        if($debugEnabled){

            _debug([
                'Salary Statement Breakdown URL' => $publicSalaryStatementBreakdownUrl,
            ]);
        }

        $svgString = $writer->writeString($publicSalaryStatementBreakdownUrl);
        $statementBreakdownLinkBase64Svg  = base64_encode($svgString);

        $params = [
            'company_name' => $companyName,
            'company_address_line_1' => $companyAddressLine1,
            'company_address_line_2' => $companyAddressLine2,
            'company_city_state_postal_code' => $companyCityStatePostalCode,
            'company_country' => $companyCountryName,
            'company_country_subregion_name' => $companyCountryName . ', ' . $companyCountrySubregionName,

            'employee_number' => $employeeNumber,
            'employee_full_name' => $employeeFullName,
            'employee_designation' => $employeeDesignation,
            'employee_department' => $employeeDepartment,

            'payroll_number' => $payrollNumber,
            'payroll_year' => $payrollYear,
            'payroll_month' => $payrollMonth,
            'payroll_month_readable' => $payrollMonthReadable,
            'salary_statement_type' => $salaryStatementType,
            'payroll_year_month' => $payrollYearMonth,
            'payroll_frequency' => trim($payrollFrequency . ' ' . $payrollFrequencySequence),
            'payroll_period' => $payrollPeriod,

            'total_days' => $totalDays,
            'total_day_offs' => $totalDayOffs,
            'total_work_days' => $totalWorkDays,
            'total_working_rest_days' => $totalWorkingRestDays,

            'total_present' => $totalPresent,
            'total_leave_with_pay' => $totalLeaveWithPay,
            'total_leave_without_pay' => $totalLeaveWithoutPay,
            'total_absent' => $totalAbsent,

            'earnings' => $earnings,
            'deductions' => $deductions,
            'summary' => $summary,

            'state_breakdown_link_qr_code_base64' => $statementBreakdownLinkBase64Svg,
        ];

        $this->params = $params;

        return $this;
    }
}
