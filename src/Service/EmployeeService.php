<?php

namespace Payroll\Service;

use Payroll\Entity\Employee;
use Payroll\Service\Database;

class EmployeeService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $result = $this->db->getConnection()->query('SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id ORDER BY e.employee_number');
        $employees = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $employees[] = new Employee($row);
        }
        return $employees;
    }

    public function findActive(): array
    {
        $result = $this->db->getConnection()->query("SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.status = 'active' ORDER BY e.employee_number");
        $employees = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $employees[] = new Employee($row);
        }
        return $employees;
    }

    public function findById(int $id): ?Employee
    {
        $stmt = $this->db->getConnection()->prepare('SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? new Employee($row) : null;
    }

    public function findByEmployeeNumber(string $employeeNumber): ?Employee
    {
        $stmt = $this->db->getConnection()->prepare('SELECT e.*, d.name as department_name FROM employees e LEFT JOIN departments d ON e.department_id = d.id WHERE e.employee_number = :emp_num');
        $stmt->bindValue(':emp_num', $employeeNumber, SQLITE3_TEXT);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? new Employee($row) : null;
    }

    public function create(Employee $employee): Employee
    {
        $employeeNumber = $employee->getEmployeeNumber() ?: $this->generateEmployeeNumber();
        
        $stmt = $this->db->getConnection()->prepare('
            INSERT INTO employees (employee_number, first_name, last_name, email, phone, address, department_id, position, base_salary, hourly_rate, employment_type, hire_date, date_of_birth, tax_number, id_number, status, retirement_fund_member, medical_aid_member, medical_aid_main_members, medical_aid_dependents)
            VALUES (:emp_num, :first, :last, :email, :phone, :address, :dept_id, :position, :salary, :hourly, :type, :hire, :dob, :tax, :id_num, :status, :retirement, :medical, :medical_main, :medical_dep)
        ');

        $stmt->bindValue(':emp_num', $employeeNumber, SQLITE3_TEXT);
        $stmt->bindValue(':first', $employee->getFirstName(), SQLITE3_TEXT);
        $stmt->bindValue(':last', $employee->getLastName(), SQLITE3_TEXT);
        $stmt->bindValue(':email', $employee->getEmail(), SQLITE3_TEXT);
        $stmt->bindValue(':phone', $employee->getPhone(), SQLITE3_TEXT);
        $stmt->bindValue(':address', $employee->getAddress(), SQLITE3_TEXT);
        $stmt->bindValue(':dept_id', $employee->getDepartmentId(), SQLITE3_INTEGER);
        $stmt->bindValue(':position', $employee->getPosition(), SQLITE3_TEXT);
        $stmt->bindValue(':salary', $employee->getBaseSalary(), SQLITE3_FLOAT);
        $stmt->bindValue(':hourly', $employee->getHourlyRate(), SQLITE3_FLOAT);
        $stmt->bindValue(':type', $employee->getEmploymentType(), SQLITE3_TEXT);
        $stmt->bindValue(':hire', $employee->getHireDate(), SQLITE3_TEXT);
        $stmt->bindValue(':dob', $employee->getAddress() ?: null, SQLITE3_TEXT);
        $stmt->bindValue(':tax', null, SQLITE3_TEXT);
        $stmt->bindValue(':id_num', null, SQLITE3_TEXT);
        $stmt->bindValue(':status', $employee->getStatus(), SQLITE3_TEXT);
        $stmt->bindValue(':retirement', 0, SQLITE3_INTEGER);
        $stmt->bindValue(':medical', 0, SQLITE3_INTEGER);
        $stmt->bindValue(':medical_main', 1, SQLITE3_INTEGER);
        $stmt->bindValue(':medical_dep', 0, SQLITE3_INTEGER);

        $stmt->execute();
        $id = $this->db->getConnection()->lastInsertRowID();
        
        return $this->findById($id);
    }

    public function update(Employee $employee): Employee
    {
        $stmt = $this->db->getConnection()->prepare('
            UPDATE employees SET 
                first_name = :first, last_name = :last, email = :email, phone = :phone, address = :address,
                department_id = :dept_id, position = :position, base_salary = :salary, hourly_rate = :hourly,
                employment_type = :type, status = :status, updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ');

        $stmt->bindValue(':id', $employee->getId(), SQLITE3_INTEGER);
        $stmt->bindValue(':first', $employee->getFirstName(), SQLITE3_TEXT);
        $stmt->bindValue(':last', $employee->getLastName(), SQLITE3_TEXT);
        $stmt->bindValue(':email', $employee->getEmail(), SQLITE3_TEXT);
        $stmt->bindValue(':phone', $employee->getPhone(), SQLITE3_TEXT);
        $stmt->bindValue(':address', $employee->getAddress(), SQLITE3_TEXT);
        $stmt->bindValue(':dept_id', $employee->getDepartmentId(), SQLITE3_INTEGER);
        $stmt->bindValue(':position', $employee->getPosition(), SQLITE3_TEXT);
        $stmt->bindValue(':salary', $employee->getBaseSalary(), SQLITE3_FLOAT);
        $stmt->bindValue(':hourly', $employee->getHourlyRate(), SQLITE3_FLOAT);
        $stmt->bindValue(':type', $employee->getEmploymentType(), SQLITE3_TEXT);
        $stmt->bindValue(':status', $employee->getStatus(), SQLITE3_TEXT);

        $stmt->execute();
        return $this->findById($employee->getId());
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->getConnection()->prepare('DELETE FROM employees WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->getConnection()->changes() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->getConnection()->querySingle('SELECT COUNT(*) FROM employees');
    }

    public function countActive(): int
    {
        return (int) $this->db->getConnection()->querySingle("SELECT COUNT(*) FROM employees WHERE status = 'active'");
    }

    public function getTotalMonthlyPayroll(): float
    {
        return (float) $this->db->getConnection()->querySingle("SELECT COALESCE(SUM(base_salary), 0) FROM employees WHERE status = 'active' AND employment_type = 'salary'");
    }

    private function generateEmployeeNumber(): string
    {
        $lastId = $this->db->getConnection()->querySingle('SELECT MAX(id) FROM employees');
        $nextId = ($lastId ?? 0) + 1;
        return 'EMP' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}