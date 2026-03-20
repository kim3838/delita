<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PS-{{$employee_number}}-{{$payroll_number}}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Funnel+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    <style>
        html, body {
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .font {
            font-family: "Inter", sans-serif;
            font-optical-sizing: auto;
            font-style: normal;
        }

        .font-numeric {
            font-family: "Funnel Sans", monospace;
            font-optical-sizing: auto;
            font-style: normal;
        }

        .payslip-body {
            padding: 2rem;
            background: #fff;
        }

        .text-color {
            color: #1c1c1c;
        }

        .sub-text-color {
            color: #5c5c5c;
        }

        .payslip-container {
            max-width: 1024px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .flex-row { flex-direction: row; }
        .flex-row-reverse { flex-direction: row-reverse; }
        .flex-1 { flex: 1 1 0%; }
        .flex-auto { flex: 1 1 auto; }
        .flex-none { flex: 0 0 auto; }
        .items-center { align-items: center; }
        .items-end { align-items: flex-end; }
        .justify-between { justify-content: space-between; }
        .justify-around { justify-content: space-around; }
        .justify-center { justify-content: center; }
        .gap-1 { gap: 0.25rem; }
        .gap-2 { gap: 0.5rem; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .space-y-2 > * + * { margin-top: 0.5rem; }

        .w-400 { width: 400px; }
        .h-140 { height: 140px; }
        .min-w-120 { min-width: 120px; }
        .min-w-110 { min-width: 110px; }
        .min-w-100 { min-width: 100px; }
        .min-w-180 { min-width: 180px; }
        .min-w-200 { min-width: 200px; }
        .min-h-80 { min-height: 80px; }
        .min-h-200 { min-height: 200px; }

        .p-1 { padding: 0.25rem; }
        .pl-2 { padding-left: 0.5rem; }
        .pr-2 { padding-right: 0.5rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .pt-2 { padding-top: 0.5rem; }

        .text-xs { font-size: 0.75rem; line-height: 1rem; }
        .text-sm { font-size: 0.875rem; line-height: 1.25rem; }
        .text-base { font-size: 1rem; line-height: 1.5rem; }
        .text-lg { font-size: 1.125rem; line-height: 1.75rem; }
        .text-xl { font-size: 1.25rem; line-height: 1.75rem; }

        .font-medium { font-weight: 500; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .bg-white { background: #ffffff; }
        .bg-slate-100 { background: oklch(96.8% 0.007 247.896); }
        .bg-slate-200 { background: oklch(92.9% 0.013 255.508) }
        .bg-gray-100 { background: oklch(96.7% 0.003 264.542); }
        .bg-gray-200 { background: oklch(92.8% 0.006 264.531); }
        .bg-gray-300 { background: oklch(87.2% 0.01 258.338); }
        .text-slate-800 { color: #1e293b; }
        .border-gray-200 { border-color: oklch(92.8% 0.006 264.531); }
        .border-gray-300 { border-color: oklch(87.2% 0.01 258.338) }

        .border-1 { border-width: 1px; }
        .border-dashed { border-style: dashed; }

        .border-l-r-b {
            border-left-width: 1px;
            border-right-width: 1px;
            border-top-width: 0;
            border-bottom-width: 1px;
        }

        .divide-x > * + * {
            border-left: 1px solid oklch(92.8% 0.006 264.531);
        }

        .divide-x.divide-dashed > * + * {
            border-left-style: dashed;
        }

        .hidden { display: none; }
        .w-auto { width: auto; }
        .max-h-full { max-height: 100%; }
        .object-contain { object-fit: contain; }

        .basis-7\/12 { flex-basis: 58.333333%; }
        .basis-5\/12 { flex-basis: 41.666667%; }

        .logo-box {
            width: 360px;
            height: 120px;
            display: flex;
            align-items: center;
            border: 1px dashed oklch(92.8% 0.006 264.531);
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 500;
            min-width: 120px;
            text-align: center;
        }

        .header-bg {
            background: oklch(0.98 0 0);
        }

        .item-label {
            font-size: 0.875rem;
            min-width: 120px;
            text-align: center;
            padding: 0 0.5rem;
        }

        .text-condensed {
            letter-spacing: -0.5px;
        }

        .payroll-item-value-row {
            min-height: 2.25rem;
        }
    </style>
</head>
<body class="payslip-body text-color">

<div class="payslip-container font">
    <!-- Company details -->
    <div class="flex justify-between items-center">
        <div class="logo-box">
            <img src="" alt="Company Logo" class="hidden max-h-full w-auto object-contain">
        </div>
        <div class="text-right pr-2">
            <div class="text-xl font-medium">{{$company_name}}</div>
            <div class="text-base">
                <div>{{$company_address_line_1}}</div>
                <div>{{$company_address_line_2}}</div>
                <div>{{$company_country}}</div>
            </div>
        </div>
    </div>

    <!-- Employee & Payroll details -->
    <div class="flex justify-around text-xs">
        <table class="border-separate">
            <tbody>
            <tr><td class="sub-text-color">Employee #:</td><td class="pl-2">{{$employee_number}}</td></tr>
            <tr><td class="sub-text-color">Name:</td><td class="pl-2">{{$employee_full_name}}</td></tr>
            <tr><td class="sub-text-color">Designation:</td><td class="pl-2">{{$employee_designation}}</td></tr>
            <tr><td class="sub-text-color">Department:</td><td class="pl-2">{{$employee_department}}</td></tr>
            </tbody>
        </table>

        <table class="border-separate">
            <tbody>
            <tr><td class="sub-text-color">Payroll #:</td><td class="pl-2">{{$payroll_number}}</td></tr>
            <tr><td class="sub-text-color">Statement type:</td><td class="pl-2">{{$salary_statement_type}}</td></tr>
            <tr><td class="sub-text-color">Month:</td><td class="pl-2">{{$payroll_year_month}}</td></tr>
            <tr><td class="sub-text-color">Sequence:</td><td class="pl-2">{{$payroll_frequency}}</td></tr>
            <tr><td class="sub-text-color">Period:</td><td class="pl-2">{{$payroll_period}}</td></tr>
            </tbody>
        </table>

        <table class="border-separate">
            <tbody>
            <tr><td class="sub-text-color">Days:</td><td class="pl-2 font-numeric">{{$total_days}}</td></tr>
            <tr><td class="sub-text-color">Day offs:</td><td class="pl-2 font-numeric">{{$total_day_offs}}</td></tr>
            <tr><td class="sub-text-color">Work days:</td><td class="pl-2 font-numeric">{{$total_work_days}}</td></tr>
            <tr><td class="sub-text-color">Working rest days:</td><td class="pl-2 font-numeric">{{$total_working_rest_days}}</td></tr>
            </tbody>
        </table>

        <table class="border-separate">
            <tbody>
            <tr><td class="sub-text-color">Present:</td><td class="pl-2 font-numeric">{{$total_present}}</td></tr>
            <tr><td class="sub-text-color">Lwp:</td><td class="pl-2 font-numeric">{{$total_leave_with_pay}}</td></tr>
            <tr><td class="sub-text-color">Lwop:</td><td class="pl-2 font-numeric">{{$total_leave_without_pay}}</td></tr>
            <tr><td class="sub-text-color">Awol:</td><td class="pl-2 font-numeric">{{$total_absent}}</td></tr>
            </tbody>
        </table>
    </div>

    <!-- Payroll items -->
    <div class="flex flex-row pt-2 gap-2 text-base">
        <div class="flex-1 space-y-2">
            <div class="flex flex-row-reverse text-base">
                <div class="section-title">Earnings</div>
            </div>

            @foreach($earnings as $earning)
            <div class="flex flex-col">
                <div class="flex flex-row-reverse text-base">
                    <div class="item-label header-bg">{{$earning['payroll_item_name']}}</div>
                </div>
                <div class="flex flex-row-reverse gap-2 payroll-item-value-row text-right border-1 border-dashed border-gray-200">
                    <div class="flex-none flex items-center text-center justify-center min-w-120">
                        <div class="text-base font-medium font-numeric">{{$earning['payroll_item_value']}}</div>
                    </div>
                    @foreach($earning['payroll_item_sub_values'] as $earningSubValue)
                    <div class="flex-auto">
                        <div class="text-condensed text-xs sub-text-color">{{$earningSubValue['label']}}</div>
                        <div class="text-xs font-numeric">{{$earningSubValue['value']}}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="flex-1 space-y-2">
            <div class="flex flex-row text-base">
                <div class="section-title">Deductions</div>
            </div>

            @foreach($deductions as $deduction)
                <div class="flex flex-col">
                    <div class="flex flex-row text-base">
                        <div class="item-label header-bg">{{$deduction['payroll_item_name']}}</div>
                    </div>

                    <div class="flex flex-row gap-2 payroll-item-value-row text-left border-1 border-dashed border-gray-200">
                        <div class="flex-none flex items-center text-center justify-center min-w-120">
                            <div class="text-base font-medium font-numeric">{{$deduction['payroll_item_value']}}</div>
                        </div>
                        @foreach($deduction['payroll_item_sub_values'] as $deductionSubValue)
                        <div class="flex-auto">
                            <div class="text-condensed text-xs sub-text-color">{{$deductionSubValue['label']}}</div>
                            <div class="text-xs font-numeric">{{$deductionSubValue['value']}}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Payroll summary -->
    <div class="flex flex-col">
        <div class="flex-1 text-sm text-center header-bg">
            Summary
        </div>
        <div class="flex justify-center border-l-r-b border-dashed border-gray-200 divide-x divide-dashed">
            <div class="flex-1 p-1">
                <div class="flex justify-center text-base">
                    <div class="section-title">Gross</div>
                </div>
                <div class="flex justify-center gap-4 text-right">
                    <div class="flex-none flex items-center text-center justify-center">
                        <div class="text-base font-medium font-numeric">{{$summary['gross']}}</div>
                    </div>
                </div>
            </div>

            <div class="flex-1 p-1">
                <div class="flex justify-center text-base">
                    <div class="section-title">Deduction</div>
                </div>
                <div class="flex justify-center gap-4 text-left">
                    <div class="flex-none flex items-center text-center justify-center">
                        <div class="text-base font-medium font-numeric">{{$summary['deduction']}}</div>
                    </div>
                </div>
            </div>

            <div class="flex-1 p-1">
                <div class="flex justify-center text-base">
                    <div class="section-title">Net salary</div>
                </div>
                <div class="flex justify-center gap-4 text-left">
                    <div class="flex-none flex items-center text-center justify-center">
                        <div class="text-base font-medium font-numeric">{{$summary['net']}}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signatories -->
    <div class="flex gap-6 justify-around">
        <div class="flex-auto basis-7/12 flex items-end">
            <div class="min-w-180 min-h-400 flex flex-row" style="padding: 0.25rem">
                <img src="data:image/svg+xml;base64, {{ $state_breakdown_link_qr_code_base64 }}" width="100" height="100" alt="Statement breakdown">
                <div class="flex flex-col pl-2 gap-1">
                    <div class="text-sm font-medium">View full statement breakdown</div>
                    <div class="text-xs font-medium sub-text-color">Scan this QR code with your phone.</div>
                    <div class="text-xs sub-text-color">Link expires in 24 hours</div>
                </div>
            </div>
        </div>

        <div class="flex-auto basis-5/12 flex gap-6 justify-around items-center">
            <div class="flex-auto min-w-200 min-h-200 border-1 border-dashed border-gray-200"></div>
            <div class="flex-auto min-w-200 min-h-200 border-1 border-dashed border-gray-200"></div>
        </div>
    </div>
</div>
</body>
</html>
