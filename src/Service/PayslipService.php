<?php

namespace Payroll\Service;

use Payroll\Entity\Payslip;
use Payroll\Service\Database;

class PayslipService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $result = $this->db->getConnection()->query('
            SELECT p.*, e.employee_number, e.first_name, e.last_name, pp.name as period_name 
            FROM payslips p 
            JOIN employees e ON p.employee_id = e.id 
            JOIN payroll_periods pp ON p.payroll_period_id = pp.id 
            ORDER BY pp.start_date DESC, e.employee_number
        ');
        $payslips = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $payslips[] = new Payslip($row);
        }
        return $payslips;
    }

    public function findById(int $id): ?Payslip
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT p.*, e.employee_number, e.first_name, e.last_name, pp.name as period_name 
            FROM payslips p 
            JOIN employees e ON p.employee_id = e.id 
            JOIN payroll_periods pp ON p.payroll_period_id = pp.id 
            WHERE p.id = :id
        ');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? new Payslip($row) : null;
    }

    public function findByPeriod(int $periodId): array
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT p.*, e.employee_number, e.first_name, e.last_name, pp.name as period_name 
            FROM payslips p 
            JOIN employees e ON p.employee_id = e.id 
            JOIN payroll_periods pp ON p.payroll_period_id = pp.id 
            WHERE p.payroll_period_id = :period_id 
            ORDER BY e.employee_number
        ');
        $stmt->bindValue(':period_id', $periodId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $payslips = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $payslips[] = new Payslip($row);
        }
        return $payslips;
    }

    public function findByEmployee(int $employeeId): array
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT p.*, e.employee_number, e.first_name, e.last_name, pp.name as period_name 
            FROM payslips p 
            JOIN employees e ON p.employee_id = e.id 
            JOIN payroll_periods pp ON p.payroll_period_id = pp.id 
            WHERE p.employee_id = :employee_id 
            ORDER BY pp.start_date DESC
        ');
        $stmt->bindValue(':employee_id', $employeeId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $payslips = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $payslips[] = new Payslip($row);
        }
        return $payslips;
    }

    public function create(Payslip $payslip): Payslip
    {
        $stmt = $this->db->getConnection()->prepare('
            INSERT INTO payslips (
                employee_id, payroll_period_id, basic_salary, overtime_hours, overtime_rate, overtime_pay,
                allowances, bonuses, commission, other_income, gross_pay, paye, uif_employee, uif_employer,
                sdl, medical_aid_employee, medical_aid_employer, retirement_fund_employee, retirement_fund_employer,
                other_deductions, total_deductions, net_pay, employer_cost, status, notes
            ) VALUES (
                :emp_id, :period_id, :basic, :ot_hours, :ot_rate, :ot_pay,
                :allow, :bonus, :comm, :other_inc, :gross, :paye, :uif_emp, :uif_er,
                :sdl, :med_emp, :med_er, :ret_emp, :ret_er,
                :other_ded, :total_ded, :net, :employer_cost, :status, :notes
            )
        ');

        $stmt->bindValue(':emp_id', $payslip->getEmployeeId(), SQLITE3_INTEGER);
        $stmt->bindValue(':period_id', $payslip->getPayrollPeriodId(), SQLITE3_INTEGER);
        $stmt->bindValue(':basic', $payslip->getBasicSalary(), SQLITE3_FLOAT);
        $stmt->bindValue(':ot_hours', 0, SQLITE3_FLOAT);
        $stmt->bindValue(':ot_rate', 0, SQLITE3_FLOAT);
        $stmt->bindValue(':ot_pay', $payslip->getOvertimePay(), SQLITE3_FLOAT);
        $stmt->bindValue(':allow', $payslip->getAllowances(), SQLITE3_FLOAT);
        $stmt->bindValue(':bonus', $payslip->getBonuses(), SQLITE3_FLOAT);
        $stmt->bindValue(':comm', 0, SQLITE3_FLOAT);
        $stmt->bindValue(':other_inc', 0, SQLITE3_FLOAT);
        $stmt->bindValue(':gross', $payslip->getGrossPay(), SQLITE3_FLOAT);
        $stmt->bindValue(':paye', $payslip->getPaye(), SQLITE3_FLOAT);
        $stmt->bindValue(':uif_emp', $payslip->getUifEmployee(), SQLITE3_FLOAT);
        $stmt->bindValue(':uif_er', $payslip->getUifEmployer(), SQLITE3_FLOAT);
        $stmt->bindValue(':sdl', $payslip->getSdl(), SQLITE3_FLOAT);
        $stmt->bindValue(':med_emp', $payslip->getMedicalAidEmployee(), SQLITE3_FLOAT);
        $stmt->bindValue(':med_er', $payslip->getMedicalAidEmployer(), SQLITE3_FLOAT);
        $stmt->bindValue(':ret_emp', $payslip->getRetirementFundEmployee(), SQLITE3_FLOAT);
        $stmt->bindValue(':ret_er', $payslip->getRetirementFundEmployer(), SQLITE3_FLOAT);
        $stmt->bindValue(':other_ded', $payslip->getOtherDeductions(), SQLITE3_FLOAT);
        $stmt->bindValue(':total_ded', $payslip->getTotalDeductions(), SQLITE3_FLOAT);
        $stmt->bindValue(':net', $payslip->getNetPay(), SQLITE3_FLOAT);
        $stmt->bindValue(':employer_cost', $payslip->getEmployerCost(), SQLITE3_FLOAT);
        $stmt->bindValue(':status', $payslip->getStatus(), SQLITE3_TEXT);
        $stmt->bindValue(':notes', null, SQLITE3_TEXT);

        $stmt->execute();
        $id = $this->db->getConnection()->lastInsertRowID();
        return $this->findById($id);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $stmt = $this->db->getConnection()->prepare('UPDATE payslips SET status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->execute();
        return $this->db->getConnection()->changes() > 0;
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->getConnection()->prepare('DELETE FROM payslips WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->getConnection()->changes() > 0;
    }

    public function getPeriodTotals(int $periodId): array
    {
        $stmt = $this->db->getConnection()->prepare('
            SELECT 
                COUNT(*) as total_employees,
                SUM(gross_pay) as total_gross,
                SUM(paye) as total_paye,
                SUM(uif_employee) as total_uif_employee,
                SUM(uif_employer) as total_uif_employer,
                SUM(sdl) as total_sdl,
                SUM(total_deductions) as total_deductions,
                SUM(net_pay) as total_net_pay,
                SUM(employer_cost) as total_employer_cost
            FROM payslips 
            WHERE payroll_period_id = :period_id
        ');
        $stmt->bindValue(':period_id', $periodId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $result->fetchArray(SQLITE3_ASSOC);
    }
}