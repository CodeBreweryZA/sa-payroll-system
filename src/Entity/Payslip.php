<?php

namespace Payroll\Entity;

class Payslip
{
    private int $id;
    private int $employeeId;
    private int $payrollPeriodId;
    private float $basicSalary;
    private float $overtimePay;
    private float $allowances;
    private float $bonuses;
    private float $grossPay;
    private float $paye;
    private float $uifEmployee;
    private float $uifEmployer;
    private float $sdl;
    private float $medicalAidEmployee;
    private float $medicalAidEmployer;
    private float $retirementFundEmployee;
    private float $retirementFundEmployer;
    private float $otherDeductions;
    private float $totalDeductions;
    private float $netPay;
    private float $employerCost;
    private string $status; // 'draft', 'approved', 'paid'
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->employeeId = $data['employee_id'] ?? 0;
        $this->payrollPeriodId = $data['payroll_period_id'] ?? 0;
        $this->basicSalary = $data['basic_salary'] ?? 0.0;
        $this->overtimePay = $data['overtime_pay'] ?? 0.0;
        $this->allowances = $data['allowances'] ?? 0.0;
        $this->bonuses = $data['bonuses'] ?? 0.0;
        $this->grossPay = $data['gross_pay'] ?? 0.0;
        $this->paye = $data['paye'] ?? 0.0;
        $this->uifEmployee = $data['uif_employee'] ?? 0.0;
        $this->uifEmployer = $data['uif_employer'] ?? 0.0;
        $this->sdl = $data['sdl'] ?? 0.0;
        $this->medicalAidEmployee = $data['medical_aid_employee'] ?? 0.0;
        $this->medicalAidEmployer = $data['medical_aid_employer'] ?? 0.0;
        $this->retirementFundEmployee = $data['retirement_fund_employee'] ?? 0.0;
        $this->retirementFundEmployer = $data['retirement_fund_employer'] ?? 0.0;
        $this->otherDeductions = $data['other_deductions'] ?? 0.0;
        $this->totalDeductions = $data['total_deductions'] ?? 0.0;
        $this->netPay = $data['net_pay'] ?? 0.0;
        $this->employerCost = $data['employer_cost'] ?? 0.0;
        $this->status = $data['status'] ?? 'draft';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmployeeId(): int { return $this->employeeId; }
    public function getPayrollPeriodId(): int { return $this->payrollPeriodId; }
    public function getBasicSalary(): float { return $this->basicSalary; }
    public function getOvertimePay(): float { return $this->overtimePay; }
    public function getAllowances(): float { return $this->allowances; }
    public function getBonuses(): float { return $this->bonuses; }
    public function getGrossPay(): float { return $this->grossPay; }
    public function getPaye(): float { return $this->paye; }
    public function getUifEmployee(): float { return $this->uifEmployee; }
    public function getUifEmployer(): float { return $this->uifEmployer; }
    public function getSdl(): float { return $this->sdl; }
    public function getMedicalAidEmployee(): float { return $this->medicalAidEmployee; }
    public function getMedicalAidEmployer(): float { return $this->medicalAidEmployer; }
    public function getRetirementFundEmployee(): float { return $this->retirementFundEmployee; }
    public function getRetirementFundEmployer(): float { return $this->retirementFundEmployer; }
    public function getOtherDeductions(): float { return $this->otherDeductions; }
    public function getTotalDeductions(): float { return $this->totalDeductions; }
    public function getNetPay(): float { return $this->netPay; }
    public function getEmployerCost(): float { return $this->employerCost; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Setters
    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setEmployeeId(int $employeeId): self { $this->employeeId = $employeeId; return $this; }
    public function setPayrollPeriodId(int $payrollPeriodId): self { $this->payrollPeriodId = $payrollPeriodId; return $this; }
    public function setBasicSalary(float $basicSalary): self { $this->basicSalary = $basicSalary; return $this; }
    public function setOvertimePay(float $overtimePay): self { $this->overtimePay = $overtimePay; return $this; }
    public function setAllowances(float $allowances): self { $this->allowances = $allowances; return $this; }
    public function setBonuses(float $bonuses): self { $this->bonuses = $bonuses; return $this; }
    public function setGrossPay(float $grossPay): self { $this->grossPay = $grossPay; return $this; }
    public function setPaye(float $paye): self { $this->paye = $paye; return $this; }
    public function setUifEmployee(float $uifEmployee): self { $this->uifEmployee = $uifEmployee; return $this; }
    public function setUifEmployer(float $uifEmployer): self { $this->uifEmployer = $uifEmployer; return $this; }
    public function setSdl(float $sdl): self { $this->sdl = $sdl; return $this; }
    public function setMedicalAidEmployee(float $medicalAidEmployee): self { $this->medicalAidEmployee = $medicalAidEmployee; return $this; }
    public function setMedicalAidEmployer(float $medicalAidEmployer): self { $this->medicalAidEmployer = $medicalAidEmployer; return $this; }
    public function setRetirementFundEmployee(float $retirementFundEmployee): self { $this->retirementFundEmployee = $retirementFundEmployee; return $this; }
    public function setRetirementFundEmployer(float $retirementFundEmployer): self { $this->retirementFundEmployer = $retirementFundEmployer; return $this; }
    public function setOtherDeductions(float $otherDeductions): self { $this->otherDeductions = $otherDeductions; return $this; }
    public function setTotalDeductions(float $totalDeductions): self { $this->totalDeductions = $totalDeductions; return $this; }
    public function setNetPay(float $netPay): self { $this->netPay = $netPay; return $this; }
    public function setEmployerCost(float $employerCost): self { $this->employerCost = $employerCost; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setUpdatedAt(string $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'payroll_period_id' => $this->payrollPeriodId,
            'basic_salary' => $this->basicSalary,
            'overtime_pay' => $this->overtimePay,
            'allowances' => $this->allowances,
            'bonuses' => $this->bonuses,
            'gross_pay' => $this->grossPay,
            'paye' => $this->paye,
            'uif_employee' => $this->uifEmployee,
            'uif_employer' => $this->uifEmployer,
            'sdl' => $this->sdl,
            'medical_aid_employee' => $this->medicalAidEmployee,
            'medical_aid_employer' => $this->medicalAidEmployer,
            'retirement_fund_employee' => $this->retirementFundEmployee,
            'retirement_fund_employer' => $this->retirementFundEmployer,
            'other_deductions' => $this->otherDeductions,
            'total_deductions' => $this->totalDeductions,
            'net_pay' => $this->netPay,
            'employer_cost' => $this->employerCost,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}