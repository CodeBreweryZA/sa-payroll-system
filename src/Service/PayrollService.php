<?php

namespace Payroll\Service;

use Payroll\Entity\Employee;
use Payroll\Entity\Payslip;
use Payroll\Service\Database;
use Payroll\Service\SouthAfricanTaxCalculator;
use Payroll\Service\EmployeeService;
use Payroll\Service\PayslipService;

class PayrollService
{
    private Database $db;
    private EmployeeService $employeeService;
    private PayslipService $payslipService;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->employeeService = new EmployeeService();
        $this->payslipService = new PayslipService();
    }

    public function processPayroll(int $periodId, array $employeeData = []): array
    {
        $employees = $this->employeeService->findActive();
        $results = [
            'processed' => 0,
            'errors' => [],
            'payslips' => [],
            'summary' => [
                'total_gross' => 0,
                'total_paye' => 0,
                'total_uif_employee' => 0,
                'total_uif_employer' => 0,
                'total_sdl' => 0,
                'total_deductions' => 0,
                'total_net_pay' => 0,
                'total_employer_cost' => 0,
            ]
        ];

        foreach ($employees as $employee) {
            try {
                $payslip = $this->calculatePayslip($employee, $periodId, $employeeData[$employee->getId()] ?? []);
                $savedPayslip = $this->payslipService->create($payslip);
                $results['payslips'][] = $savedPayslip;
                $results['processed']++;
                
                $results['summary']['total_gross'] += $savedPayslip->getGrossPay();
                $results['summary']['total_paye'] += $savedPayslip->getPaye();
                $results['summary']['total_uif_employee'] += $savedPayslip->getUifEmployee();
                $results['summary']['total_uif_employer'] += $savedPayslip->getUifEmployer();
                $results['summary']['total_sdl'] += $savedPayslip->getSdl();
                $results['summary']['total_deductions'] += $savedPayslip->getTotalDeductions();
                $results['summary']['total_net_pay'] += $savedPayslip->getNetPay();
                $results['summary']['total_employer_cost'] += $savedPayslip->getEmployerCost();
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'employee_id' => $employee->getId(),
                    'employee_name' => $employee->getFullName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    public function calculatePayslip(Employee $employee, int $periodId, array $extraData = []): Payslip
    {
        $basicSalary = $employee->isSalary() ? $employee->getBaseSalary() : ($employee->getHourlyRate() * 160);
        $overtimeHours = $extraData['overtime_hours'] ?? 0;
        $overtimeRate = $employee->isHourly() ? ($employee->getHourlyRate() * 1.5) : ($employee->getBaseSalary() / 160 * 1.5);
        $overtimePay = round($overtimeHours * $overtimeRate, 2);
        $allowances = $extraData['allowances'] ?? 0;
        $bonuses = $extraData['bonuses'] ?? 0;
        $otherIncome = $extraData['other_income'] ?? 0;

        $grossPay = $basicSalary + $overtimePay + $allowances + $bonuses + $otherIncome;

        // Retirement fund calculations
        $retirementFundEmployee = $extraData['retirement_fund_employee'] ?? 0;
        $retirementFundEmployer = $extraData['retirement_fund_employer'] ?? 0;
        $retirementFund = SouthAfricanTaxCalculator::calculateRetirementFundDeduction(
            $grossPay,
            $retirementFundEmployee,
            $retirementFundEmployer
        );

        // Medical aid calculations
        $medicalAidEmployee = $extraData['medical_aid_employee'] ?? 0;
        $medicalAidEmployer = $extraData['medical_aid_employer'] ?? 0;

        // Taxable income
        $taxableIncome = SouthAfricanTaxCalculator::calculateTaxableIncome(
            $grossPay,
            $retirementFund['allowed_deduction'],
            $medicalAidEmployee
        );

        // PAYE calculation
        $age = $extraData['age'] ?? 30;
        $paye = SouthAfricanTaxCalculator::calculatePAYE($taxableIncome * 12, $age);

        // Medical aid tax credit
        $medicalAidCredit = SouthAfricanTaxCalculator::calculateMedicalAidTaxCredit(
            $extraData['medical_aid_main_members'] ?? 1,
            $extraData['medical_aid_dependents'] ?? 0
        );
        $paye = max(0, $paye - $medicalAidCredit);

        // UIF calculation
        $uif = SouthAfricanTaxCalculator::calculateUIF($grossPay);

        // SDL calculation (employer only)
        $sdl = SouthAfricanTaxCalculator::calculateSDL($grossPay);

        // Other deductions
        $otherDeductions = $extraData['other_deductions'] ?? 0;

        // Total employee deductions
        $totalDeductions = $paye + $uif['employee'] + $medicalAidEmployee + $retirementFund['allowed_deduction'] + $otherDeductions;

        // Net pay
        $netPay = $grossPay - $totalDeductions;

        // Total employer cost
        $employerCost = $grossPay + $uif['employer'] + $sdl + $retirementFundEmployer + $medicalAidEmployer;

        return new Payslip([
            'employee_id' => $employee->getId(),
            'payroll_period_id' => $periodId,
            'basic_salary' => $basicSalary,
            'overtime_pay' => $overtimePay,
            'allowances' => $allowances,
            'bonuses' => $bonuses,
            'gross_pay' => round($grossPay, 2),
            'paye' => round($paye, 2),
            'uif_employee' => round($uif['employee'], 2),
            'uif_employer' => round($uif['employer'], 2),
            'sdl' => round($sdl, 2),
            'medical_aid_employee' => round($medicalAidEmployee, 2),
            'medical_aid_employer' => round($medicalAidEmployer, 2),
            'retirement_fund_employee' => round($retirementFund['employee_contribution'], 2),
            'retirement_fund_employer' => round($retirementFund['employer_contribution'], 2),
            'other_deductions' => round($otherDeductions, 2),
            'total_deductions' => round($totalDeductions, 2),
            'net_pay' => round($netPay, 2),
            'employer_cost' => round($employerCost, 2),
            'status' => 'draft',
        ]);
    }

    public function getPayrollSummary(int $periodId): array
    {
        return $this->payslipService->getPeriodTotals($periodId);
    }

    public function approvePayroll(int $periodId): bool
    {
        $payslips = $this->payslipService->findByPeriod($periodId);
        foreach ($payslips as $payslip) {
            $this->payslipService->updateStatus($payslip->getId(), 'approved');
        }
        return true;
    }

    public function processPayment(int $periodId): bool
    {
        $payslips = $this->payslipService->findByPeriod($periodId);
        foreach ($payslips as $payslip) {
            $this->payslipService->updateStatus($payslip->getId(), 'paid');
        }
        return true;
    }
}