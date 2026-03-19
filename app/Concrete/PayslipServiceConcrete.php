<?php

namespace App\Concrete;

use App\Blueprint\PayslipServiceInterface;
use App\Enums\PayslipColumn;
use App\Facades\Fractal;
use App\Models\Company;
use App\Models\SalaryStatement;
use App\Transformers\SalaryStatement\PayslipTransformer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

class PayslipServiceConcrete implements PayslipServiceInterface
{
    public string $defaultTemplate = 'payslip.default-template';
    public string $defaultFormat = 'A4';
    public string $path = '';
    public string $filename = '';
    public array $params = [];
    public int $signSeconds = 10;

    public function __construct(
        protected ?Company $company
    ){
        $this->path = $this->company->account->number. '/payslips/';
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
        $this->generate($salaryStatement);

        return Storage::disk('s3')->temporaryUrl($this->filePath(), now()->addSeconds($this->signSeconds));
    }

    public function generate(SalaryStatement $salaryStatement): static
    {
        $this->prepareTemplateParameters($salaryStatement);

        Pdf::view($this->defaultTemplate, $this->params)
            ->disk('s3')
            ->format($this->defaultFormat)
            ->save($this->filePath());

        return $this;
    }

    public function prepareTemplateParameters($salaryStatement): static
    {
        $debugEnabled = false;

        $salaryStatement = is_array($salaryStatement)
            ? $salaryStatement
            : Fractal::item($salaryStatement, PayslipTransformer::class);

        $company = $salaryStatement['company'];
        $companyName = $company['name'];
        $companyAddressLine1 = $company['address_line_1'];
        $companyAddressLine2 = $company['address_line_2'];
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
         * Concat year, month number and month readable
         **/
        $this->path = $this->path . $payrollYear . '/' . $payrollMonth . '.' . $payrollMonthReadable . '/';

        /**
         * Is there is month sequence, concat sequence
         **/
        if(!empty($payrollFrequencySequence)){
            $this->path = $this->path . $payrollFrequencySequence . '/';
        }

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

        $params = [
            'company_name' => $companyName,
            'company_address_line_1' => $companyAddressLine1,
            'company_address_line_2' => $companyAddressLine2,
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
        ];

        $this->params = $params;

        return $this;
    }
}
