<?php

namespace Payroll\Console;

use Payroll\Service\Database;
use Payroll\Service\EmployeeService;
use Payroll\Service\DepartmentService;
use Payroll\Service\PayrollPeriodService;
use Payroll\Service\PayslipService;
use Payroll\Service\PayrollService;
use Payroll\Service\SouthAfricanTaxCalculator;
use Payroll\Entity\Employee;
use Payroll\Entity\Department;
use Payroll\Entity\PayrollPeriod;

class Application
{
    private EmployeeService $employeeService;
    private DepartmentService $departmentService;
    private PayrollPeriodService $periodService;
    private PayslipService $payslipService;
    private PayrollService $payrollService;

    public function __construct()
    {
        $this->employeeService = new EmployeeService();
        $this->departmentService = new DepartmentService();
        $this->periodService = new PayrollPeriodService();
        $this->payslipService = new PayslipService();
        $this->payrollService = new PayrollService();
        
        Database::getInstance()->initSchema();
    }

    public function run(): void
    {
        $this->clearScreen();
        $this->printBanner();
        
        while (true) {
            $this->printMainMenu();
            $choice = $this->getInput('Enter your choice');
            
            match($choice) {
                '1' => $this->employeeMenu(),
                '2' => $this->departmentMenu(),
                '3' => $this->payrollMenu(),
                '4' => $this->reportsMenu(),
                '5' => $this->taxInfoMenu(),
                '0' => $this->exit(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function printBanner(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           SOUTH AFRICAN PAYROLL MANAGEMENT SYSTEM          ║\n";
        echo "║                     Console Application                    ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Features:                                                 ║\n";
        echo "║  • Employee Management                                     ║\n";
        echo "║  • Department Management                                   ║\n";
        echo "║  • PAYE Tax Calculations (SARS 2024/2025)                 ║\n";
        echo "║  • UIF Contributions                                       ║\n";
        echo "║  • SDL (Skills Development Levy)                           ║\n";
        echo "║  • Medical Aid Tax Credits                                 ║\n";
        echo "║  • Retirement Fund Deductions                              ║\n";
        echo "║  • Payslip Generation                                      ║\n";
        echo "║  • Payroll Reports                                         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function printMainMenu(): void
    {
        echo "\n";
        echo "┌─────────────────────────────────────────────────────────────┐\n";
        echo "│                      MAIN MENU                              │\n";
        echo "├─────────────────────────────────────────────────────────────┤\n";
        echo "│  1. Employee Management                                     │\n";
        echo "│  2. Department Management                                   │\n";
        echo "│  3. Payroll Processing                                      │\n";
        echo "│  4. Reports                                                 │\n";
        echo "│  5. Tax Information (SARS Rates)                            │\n";
        echo "│  0. Exit                                                    │\n";
        echo "└─────────────────────────────────────────────────────────────┘\n";
    }

    private function employeeMenu(): void
    {
        while (true) {
            echo "\n";
            echo "┌─────────────────────────────────────────────────────────────┐\n";
            echo "│                   EMPLOYEE MANAGEMENT                       │\n";
            echo "├─────────────────────────────────────────────────────────────┤\n";
            echo "│  1. List All Employees                                      │\n";
            echo "│  2. View Employee Details                                   │\n";
            echo "│  3. Add New Employee                                        │\n";
            echo "│  4. Update Employee                                         │\n";
            echo "│  5. Delete Employee                                         │\n";
            echo "│  6. Search Employee                                         │\n";
            echo "│  0. Back to Main Menu                                       │\n";
            echo "└─────────────────────────────────────────────────────────────┘\n";

            $choice = $this->getInput('Enter your choice');

            match($choice) {
                '1' => $this->listEmployees(),
                '2' => $this->viewEmployee(),
                '3' => $this->addEmployee(),
                '4' => $this->updateEmployee(),
                '5' => $this->deleteEmployee(),
                '6' => $this->searchEmployee(),
                '0' => $this->clearScreen(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function listEmployees(): void
    {
        $employees = $this->employeeService->findAll();
        
        if (empty($employees)) {
            $this->warning('No employees found.');
            return;
        }

        echo "\n";
        echo "┌──────┬────────────┬────────────────────────┬────────────────────────┬──────────────────┬────────┐\n";
        echo "│ ID   │ Emp Number │ Name                   │ Position               │ Department       │ Status │\n";
        echo "├──────┼────────────┼────────────────────────┼────────────────────────┼──────────────────┼────────┤\n";

        foreach ($employees as $emp) {
            printf("│ %-4d │ %-10s │ %-22s │ %-22s │ %-16s │ %-6s │\n",
                $emp->getId(),
                $emp->getEmployeeNumber(),
                substr($emp->getFullName(), 0, 22),
                substr($emp->getPosition(), 0, 22),
                substr($emp->getDepartmentId() ? 'Dept #' . $emp->getDepartmentId() : 'N/A', 0, 16),
                $emp->getStatus()
            );
        }

        echo "└──────┴────────────┴────────────────────────┴────────────────────────┴──────────────────┴────────┘\n";
        echo "Total: " . count($employees) . " employee(s)\n";
    }

    private function viewEmployee(): void
    {
        $id = (int) $this->getInput('Enter Employee ID');
        $employee = $this->employeeService->findById($id);

        if (!$employee) {
            $this->error('Employee not found.');
            return;
        }

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    EMPLOYEE DETAILS                         ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Employee Number: %-42s ║\n", $employee->getEmployeeNumber());
        printf("║  Full Name:       %-42s ║\n", $employee->getFullName());
        printf("║  Email:           %-42s ║\n", $employee->getEmail());
        printf("║  Phone:           %-42s ║\n", $employee->getPhone() ?: 'N/A');
        printf("║  Position:        %-42s ║\n", $employee->getPosition());
        printf("║  Department ID:   %-42s ║\n", $employee->getDepartmentId() ?: 'N/A');
        printf("║  Employment Type: %-42s ║\n", $employee->getEmploymentType());
        printf("║  Base Salary:     R%-41s ║\n", number_format($employee->getBaseSalary(), 2));
        printf("║  Hourly Rate:     R%-41s ║\n", number_format($employee->getHourlyRate(), 2));
        printf("║  Hire Date:       %-42s ║\n", $employee->getHireDate());
        printf("║  Status:          %-42s ║\n", $employee->getStatus());
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function addEmployee(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                     ADD NEW EMPLOYEE                        ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";

        $firstName = $this->getInput('First Name');
        $lastName = $this->getInput('Last Name');
        $email = $this->getInput('Email');
        $phone = $this->getInput('Phone (optional)');
        $position = $this->getInput('Position');
        
        $this->listDepartmentsBrief();
        $departmentId = (int) $this->getInput('Department ID');
        
        $employmentType = $this->getInput('Employment Type (salary/hourly)');
        $baseSalary = 0;
        $hourlyRate = 0;
        
        if ($employmentType === 'salary') {
            $baseSalary = (float) $this->getInput('Monthly Base Salary (ZAR)');
        } else {
            $hourlyRate = (float) $this->getInput('Hourly Rate (ZAR)');
        }

        $hireDate = $this->getInput('Hire Date (YYYY-MM-DD)');

        $employee = new Employee([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'position' => $position,
            'department_id' => $departmentId,
            'employment_type' => $employmentType,
            'base_salary' => $baseSalary,
            'hourly_rate' => $hourlyRate,
            'hire_date' => $hireDate,
            'status' => 'active',
        ]);

        try {
            $saved = $this->employeeService->create($employee);
            $this->success('Employee created successfully! Employee Number: ' . $saved->getEmployeeNumber());
        } catch (\Exception $e) {
            $this->error('Error creating employee: ' . $e->getMessage());
        }
    }

    private function updateEmployee(): void
    {
        $id = (int) $this->getInput('Enter Employee ID to update');
        $employee = $this->employeeService->findById($id);

        if (!$employee) {
            $this->error('Employee not found.');
            return;
        }

        echo "\nCurrent employee details (press Enter to keep current value):\n";
        
        $firstName = $this->getInput("First Name [{$employee->getFirstName()}]") ?: $employee->getFirstName();
        $lastName = $this->getInput("Last Name [{$employee->getLastName()}]") ?: $employee->getLastName();
        $email = $this->getInput("Email [{$employee->getEmail()}]") ?: $employee->getEmail();
        $phone = $this->getInput("Phone [{$employee->getPhone()}]") ?: $employee->getPhone();
        $position = $this->getInput("Position [{$employee->getPosition()}]") ?: $employee->getPosition();
        $status = $this->getInput("Status [{$employee->getStatus()}]") ?: $employee->getStatus();

        $employee->setFirstName($firstName);
        $employee->setLastName($lastName);
        $employee->setEmail($email);
        $employee->setPhone($phone);
        $employee->setPosition($position);
        $employee->setStatus($status);

        try {
            $this->employeeService->update($employee);
            $this->success('Employee updated successfully!');
        } catch (\Exception $e) {
            $this->error('Error updating employee: ' . $e->getMessage());
        }
    }

    private function deleteEmployee(): void
    {
        $id = (int) $this->getInput('Enter Employee ID to delete');
        $employee = $this->employeeService->findById($id);

        if (!$employee) {
            $this->error('Employee not found.');
            return;
        }

        echo "\nEmployee to delete: {$employee->getFullName()} ({$employee->getEmployeeNumber()})\n";
        $confirm = $this->getInput('Are you sure? (yes/no)');

        if (strtolower($confirm) === 'yes') {
            if ($this->employeeService->delete($id)) {
                $this->success('Employee deleted successfully!');
            } else {
                $this->error('Error deleting employee.');
            }
        } else {
            $this->info('Deletion cancelled.');
        }
    }

    private function searchEmployee(): void
    {
        $query = $this->getInput('Search by name or employee number');
        $employees = $this->employeeService->findAll();
        
        $results = array_filter($employees, function($emp) use ($query) {
            $searchStr = strtolower($query);
            return str_contains(strtolower($emp->getFullName()), $searchStr) ||
                   str_contains(strtolower($emp->getEmployeeNumber()), $searchStr);
        });

        if (empty($results)) {
            $this->warning('No employees found matching your search.');
            return;
        }

        echo "\nSearch Results:\n";
        foreach ($results as $emp) {
            printf("  %s - %s - %s\n", $emp->getEmployeeNumber(), $emp->getFullName(), $emp->getPosition());
        }
    }

    private function departmentMenu(): void
    {
        while (true) {
            echo "\n";
            echo "┌─────────────────────────────────────────────────────────────┐\n";
            echo "│                  DEPARTMENT MANAGEMENT                      │\n";
            echo "├─────────────────────────────────────────────────────────────┤\n";
            echo "│  1. List All Departments                                    │\n";
            echo "│  2. Add New Department                                      │\n";
            echo "│  3. Update Department                                       │\n";
            echo "│  4. Delete Department                                       │\n";
            echo "│  0. Back to Main Menu                                       │\n";
            echo "└─────────────────────────────────────────────────────────────┘\n";

            $choice = $this->getInput('Enter your choice');

            match($choice) {
                '1' => $this->listDepartments(),
                '2' => $this->addDepartment(),
                '3' => $this->updateDepartment(),
                '4' => $this->deleteDepartment(),
                '0' => $this->clearScreen(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function listDepartments(): void
    {
        $departments = $this->departmentService->findAll();
        
        if (empty($departments)) {
            $this->warning('No departments found.');
            return;
        }

        echo "\n";
        echo "┌──────┬──────┬──────────────────────────────┬────────────────────────────────────────┐\n";
        echo "│ ID   │ Code │ Name                         │ Description                            │\n";
        echo "├──────┼──────┼──────────────────────────────┼────────────────────────────────────────┤\n";

        foreach ($departments as $dept) {
            printf("│ %-4d │ %-4s │ %-28s │ %-38s │\n",
                $dept->getId(),
                $dept->getCode(),
                substr($dept->getName(), 0, 28),
                substr($dept->getDescription(), 0, 38)
            );
        }

        echo "└──────┴──────┴──────────────────────────────┴────────────────────────────────────────┘\n";
    }

    private function listDepartmentsBrief(): void
    {
        $departments = $this->departmentService->findAll();
        echo "\nAvailable Departments:\n";
        foreach ($departments as $dept) {
            echo "  {$dept->getId()} - {$dept->getName()} ({$dept->getCode()})\n";
        }
    }

    private function addDepartment(): void
    {
        $name = $this->getInput('Department Name');
        $code = $this->getInput('Department Code');
        $description = $this->getInput('Description');

        $department = new Department([
            'name' => $name,
            'code' => $code,
            'description' => $description,
        ]);

        try {
            $this->departmentService->create($department);
            $this->success('Department created successfully!');
        } catch (\Exception $e) {
            $this->error('Error creating department: ' . $e->getMessage());
        }
    }

    private function updateDepartment(): void
    {
        $id = (int) $this->getInput('Enter Department ID to update');
        $department = $this->departmentService->findById($id);

        if (!$department) {
            $this->error('Department not found.');
            return;
        }

        $name = $this->getInput("Name [{$department->getName()}]") ?: $department->getName();
        $code = $this->getInput("Code [{$department->getCode()}]") ?: $department->getCode();
        $description = $this->getInput("Description [{$department->getDescription()}]") ?: $department->getDescription();

        $department->setName($name);
        $department->setCode($code);
        $department->setDescription($description);

        try {
            $this->departmentService->update($department);
            $this->success('Department updated successfully!');
        } catch (\Exception $e) {
            $this->error('Error updating department: ' . $e->getMessage());
        }
    }

    private function deleteDepartment(): void
    {
        $id = (int) $this->getInput('Enter Department ID to delete');
        $department = $this->departmentService->findById($id);

        if (!$department) {
            $this->error('Department not found.');
            return;
        }

        $empCount = $this->departmentService->getEmployeeCount($id);
        if ($empCount > 0) {
            $this->warning("Cannot delete department with {$empCount} active employee(s). Reassign employees first.");
            return;
        }

        $confirm = $this->getInput("Delete department: {$department->getName()}? (yes/no)");
        if (strtolower($confirm) === 'yes') {
            $this->departmentService->delete($id);
            $this->success('Department deleted successfully!');
        }
    }

    private function payrollMenu(): void
    {
        while (true) {
            echo "\n";
            echo "┌─────────────────────────────────────────────────────────────┐\n";
            echo "│                   PAYROLL PROCESSING                        │\n";
            echo "├─────────────────────────────────────────────────────────────┤\n";
            echo "│  1. Create Payroll Period                                   │\n";
            echo "│  2. List Payroll Periods                                    │\n";
            echo "│  3. Process Payroll for Period                              │\n";
            echo "│  4. View Payslips for Period                                │\n";
            echo "│  5. Approve Payroll                                         │\n";
            echo "│  6. Process Payment                                         │\n";
            echo "│  7. View Employee Payslips                                  │\n";
            echo "│  0. Back to Main Menu                                       │\n";
            echo "└─────────────────────────────────────────────────────────────┘\n";

            $choice = $this->getInput('Enter your choice');

            match($choice) {
                '1' => $this->createPayrollPeriod(),
                '2' => $this->listPayrollPeriods(),
                '3' => $this->processPayroll(),
                '4' => $this->viewPayslips(),
                '5' => $this->approvePayroll(),
                '6' => $this->processPayment(),
                '7' => $this->viewEmployeePayslips(),
                '0' => $this->clearScreen(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function createPayrollPeriod(): void
    {
        echo "\nCreate Payroll Period:\n";
        $monthYear = $this->getInput('Enter month (YYYY-MM, e.g., 2024-01)');
        
        try {
            $period = $this->periodService->generateMonthlyPeriod($monthYear);
            $this->success("Payroll period created: {$period->getName()}");
            echo "  Start Date: {$period->getStartDate()}\n";
            echo "  End Date:   {$period->getEndDate()}\n";
            echo "  Pay Date:   {$period->getPayDate()}\n";
        } catch (\Exception $e) {
            $this->error('Error creating payroll period: ' . $e->getMessage());
        }
    }

    private function listPayrollPeriods(): void
    {
        $periods = $this->periodService->findAll();
        
        if (empty($periods)) {
            $this->warning('No payroll periods found.');
            return;
        }

        echo "\n";
        echo "┌──────┬──────────────────────────────┬────────────┬────────────┬────────────┬────────────┐\n";
        echo "│ ID   │ Name                         │ Start Date │ End Date   │ Pay Date   │ Status     │\n";
        echo "├──────┼──────────────────────────────┼────────────┼────────────┼────────────┼────────────┤\n";

        foreach ($periods as $period) {
            printf("│ %-4d │ %-28s │ %-10s │ %-10s │ %-10s │ %-10s │\n",
                $period->getId(),
                substr($period->getName(), 0, 28),
                $period->getStartDate(),
                $period->getEndDate(),
                $period->getPayDate(),
                $period->getStatus()
            );
        }

        echo "└──────┴──────────────────────────────┴────────────┴────────────┴────────────┴────────────┘\n";
    }

    private function processPayroll(): void
    {
        $periodId = (int) $this->getInput('Enter Payroll Period ID');
        $period = $this->periodService->findById($periodId);

        if (!$period) {
            $this->error('Payroll period not found.');
            return;
        }

        $employees = $this->employeeService->findActive();
        if (empty($employees)) {
            $this->warning('No active employees to process.');
            return;
        }

        $extraData = [];
        echo "\nProcessing payroll for: {$period->getName()}\n";
        echo "Active employees: " . count($employees) . "\n\n";

        foreach ($employees as $emp) {
            echo "Employee: {$emp->getFullName()} ({$emp->getEmployeeNumber()})\n";
            
            $empData = [];
            
            if ($emp->isHourly()) {
                $empData['overtime_hours'] = (float) $this->getInput("  Overtime hours for {$emp->getFirstName()}", '0');
            } else {
                $empData['overtime_hours'] = (float) $this->getInput("  Overtime hours for {$emp->getFirstName()}", '0');
            }
            
            $empData['allowances'] = (float) $this->getInput("  Allowances for {$emp->getFirstName()}", '0');
            $empData['bonuses'] = (float) $this->getInput("  Bonuses for {$emp->getFirstName()}", '0');
            $empData['retirement_fund_employee'] = (float) $this->getInput("  Retirement fund contribution (employee) for {$emp->getFirstName()}", '0');
            $empData['medical_aid_employee'] = (float) $this->getInput("  Medical aid contribution (employee) for {$emp->getFirstName()}", '0');
            
            $extraData[$emp->getId()] = $empData;
        }

        echo "\nProcessing...\n";
        $results = $this->payrollService->processPayroll($periodId, $extraData);

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                   PAYROLL PROCESSING SUMMARY                ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Employees Processed: %-38d ║\n", $results['processed']);
        printf("║  Total Gross Pay:     R%-37s ║\n", number_format($results['summary']['total_gross'], 2));
        printf("║  Total PAYE:          R%-37s ║\n", number_format($results['summary']['total_paye'], 2));
        printf("║  Total UIF (Employee):R%-37s ║\n", number_format($results['summary']['total_uif_employee'], 2));
        printf("║  Total UIF (Employer):R%-37s ║\n", number_format($results['summary']['total_uif_employer'], 2));
        printf("║  Total SDL:           R%-37s ║\n", number_format($results['summary']['total_sdl'], 2));
        printf("║  Total Deductions:    R%-37s ║\n", number_format($results['summary']['total_deductions'], 2));
        printf("║  Total Net Pay:       R%-37s ║\n", number_format($results['summary']['total_net_pay'], 2));
        printf("║  Total Employer Cost: R%-37s ║\n", number_format($results['summary']['total_employer_cost'], 2));
        echo "╚══════════════════════════════════════════════════════════════╝\n";

        if (!empty($results['errors'])) {
            $this->warning("\nErrors encountered:");
            foreach ($results['errors'] as $error) {
                echo "  - {$error['employee_name']}: {$error['error']}\n";
            }
        }
    }

    private function viewPayslips(): void
    {
        $periodId = (int) $this->getInput('Enter Payroll Period ID');
        $payslips = $this->payslipService->findByPeriod($periodId);

        if (empty($payslips)) {
            $this->warning('No payslips found for this period.');
            return;
        }

        echo "\n";
        echo "┌──────┬────────────┬────────────────────────┬────────────┬────────────┬────────────┬──────────┐\n";
        echo "│ ID   │ Emp Number │ Name                   │ Gross Pay  │ Deductions │ Net Pay    │ Status   │\n";
        echo "├──────┼────────────┼────────────────────────┼────────────┼────────────┼────────────┼──────────┤\n";

        foreach ($payslips as $payslip) {
            printf("│ %-4d │ %-10s │ %-22s │ R%9s │ R%9s │ R%9s │ %-8s │\n",
                $payslip->getId(),
                $payslip->getEmployeeNumber(),
                substr($payslip->getFullName(), 0, 22),
                number_format($payslip->getGrossPay(), 2),
                number_format($payslip->getTotalDeductions(), 2),
                number_format($payslip->getNetPay(), 2),
                $payslip->getStatus()
            );
        }

        echo "└──────┴────────────┴────────────────────────┴────────────┴────────────┴────────────┴──────────┘\n";
    }

    private function approvePayroll(): void
    {
        $periodId = (int) $this->getInput('Enter Payroll Period ID to approve');
        $period = $this->periodService->findById($periodId);

        if (!$period) {
            $this->error('Payroll period not found.');
            return;
        }

        $confirm = $this->getInput("Approve payroll for {$period->getName()}? (yes/no)");
        if (strtolower($confirm) === 'yes') {
            $this->payrollService->approvePayroll($periodId);
            $this->success('Payroll approved successfully!');
        }
    }

    private function processPayment(): void
    {
        $periodId = (int) $this->getInput('Enter Payroll Period ID to process payment');
        $period = $this->periodService->findById($periodId);

        if (!$period) {
            $this->error('Payroll period not found.');
            return;
        }

        $confirm = $this->getInput("Process payment for {$period->getName()}? (yes/no)");
        if (strtolower($confirm) === 'yes') {
            $this->payrollService->processPayment($periodId);
            $this->success('Payment processed successfully!');
        }
    }

    private function viewEmployeePayslips(): void
    {
        $empId = (int) $this->getInput('Enter Employee ID');
        $employee = $this->employeeService->findById($empId);

        if (!$employee) {
            $this->error('Employee not found.');
            return;
        }

        $payslips = $this->payslipService->findByEmployee($empId);

        if (empty($payslips)) {
            $this->warning('No payslips found for this employee.');
            return;
        }

        echo "\nPayslips for: {$employee->getFullName()}\n";
        echo str_repeat('-', 100) . "\n";

        foreach ($payslips as $payslip) {
            echo "\n  Period: {$payslip->getPayrollPeriodId()}\n";
            echo "  Gross Pay:     R" . number_format($payslip->getGrossPay(), 2) . "\n";
            echo "  PAYE:          R" . number_format($payslip->getPaye(), 2) . "\n";
            echo "  UIF:           R" . number_format($payslip->getUifEmployee(), 2) . "\n";
            echo "  Net Pay:       R" . number_format($payslip->getNetPay(), 2) . "\n";
            echo str_repeat('-', 100) . "\n";
        }
    }

    private function reportsMenu(): void
    {
        while (true) {
            echo "\n";
            echo "┌─────────────────────────────────────────────────────────────┐\n";
            echo "│                       REPORTS                               │\n";
            echo "├─────────────────────────────────────────────────────────────┤\n";
            echo "│  1. Employee Summary                                        │\n";
            echo "│  2. Department Summary                                      │\n";
            echo "│  3. Payroll Summary by Period                               │\n";
            echo "│  4. Tax Calculation Example                                 │\n";
            echo "│  5. UIF Calculation Example                                 │\n";
            echo "│  0. Back to Main Menu                                       │\n";
            echo "└─────────────────────────────────────────────────────────────┘\n";

            $choice = $this->getInput('Enter your choice');

            match($choice) {
                '1' => $this->employeeSummaryReport(),
                '2' => $this->departmentSummaryReport(),
                '3' => $this->payrollSummaryReport(),
                '4' => $this->taxCalculationExample(),
                '5' => $this->uifCalculationExample(),
                '0' => $this->clearScreen(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function employeeSummaryReport(): void
    {
        $total = $this->employeeService->count();
        $active = $this->employeeService->countActive();
        $payroll = $this->employeeService->getTotalMonthlyPayroll();

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                   EMPLOYEE SUMMARY REPORT                   ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Total Employees:        %-35d ║\n", $total);
        printf("║  Active Employees:       %-35d ║\n", $active);
        printf("║  Total Monthly Payroll:  R%-34s ║\n", number_format($payroll, 2));
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function departmentSummaryReport(): void
    {
        $departments = $this->departmentService->findAll();
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                  DEPARTMENT SUMMARY REPORT                  ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";

        foreach ($departments as $dept) {
            $empCount = $this->departmentService->getEmployeeCount($dept->getId());
            printf("║  %-20s │ Employees: %-24d ║\n", $dept->getName(), $empCount);
        }

        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function payrollSummaryReport(): void
    {
        $periodId = (int) $this->getInput('Enter Payroll Period ID');
        $summary = $this->payrollService->getPayrollSummary($periodId);

        if (!$summary || $summary['total_employees'] == 0) {
            $this->warning('No payroll data found for this period.');
            return;
        }

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                  PAYROLL SUMMARY REPORT                     ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Total Employees Processed: %-31d ║\n", $summary['total_employees']);
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Total Gross Pay:      R%-36s ║\n", number_format($summary['total_gross'], 2));
        printf("║  Total PAYE:           R%-36s ║\n", number_format($summary['total_paye'], 2));
        printf("║  Total UIF (Employee): R%-36s ║\n", number_format($summary['total_uif_employee'], 2));
        printf("║  Total UIF (Employer): R%-36s ║\n", number_format($summary['total_uif_employer'], 2));
        printf("║  Total SDL:            R%-36s ║\n", number_format($summary['total_sdl'], 2));
        printf("║  Total Deductions:     R%-36s ║\n", number_format($summary['total_deductions'], 2));
        printf("║  Total Net Pay:        R%-36s ║\n", number_format($summary['total_net_pay'], 2));
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Total Employer Cost:  R%-36s ║\n", number_format($summary['total_employer_cost'], 2));
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function taxCalculationExample(): void
    {
        $salary = (float) $this->getInput('Enter annual salary (ZAR)');
        $age = (int) $this->getInput('Enter age');

        $monthlyTaxable = $salary / 12;
        $annualTax = SouthAfricanTaxCalculator::calculateAnnualPAYE($salary, $age);
        $monthlyTax = SouthAfricanTaxCalculator::calculatePAYE($salary, $age);

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║              SOUTH AFRICAN TAX CALCULATION                  ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Annual Salary:         R%-36s ║\n", number_format($salary, 2));
        printf("║  Monthly Taxable:       R%-36s ║\n", number_format($monthlyTaxable, 2));
        printf("║  Age:                   %-40d ║\n", $age);
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Annual PAYE Tax:       R%-36s ║\n", number_format($annualTax, 2));
        printf("║  Monthly PAYE Tax:      R%-36s ║\n", number_format($monthlyTax, 2));
        printf("║  Effective Tax Rate:    %-37s ║\n", ($salary > 0 ? number_format(($annualTax / $salary) * 100, 2) . '%' : '0%'));
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function uifCalculationExample(): void
    {
        $salary = (float) $this->getInput('Enter monthly gross salary (ZAR)');
        $uif = SouthAfricanTaxCalculator::calculateUIF($salary);

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║              UIF CONTRIBUTION CALCULATION                   ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Monthly Gross Salary:  R%-36s ║\n", number_format($salary, 2));
        printf("║  UIF Ceiling:           R%-36s ║\n", number_format(SouthAfricanTaxCalculator::getUIFCeiling(), 2));
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Employee Contribution: R%-36s ║\n", number_format($uif['employee'], 2));
        printf("║  Employer Contribution: R%-36s ║\n", number_format($uif['employer'], 2));
        printf("║  Total UIF:             R%-36s ║\n", number_format($uif['employee'] + $uif['employer'], 2));
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function taxInfoMenu(): void
    {
        while (true) {
            echo "\n";
            echo "┌─────────────────────────────────────────────────────────────┐\n";
            echo "│                  TAX INFORMATION (SARS 2024/2025)          │\n";
            echo "├─────────────────────────────────────────────────────────────┤\n";
            echo "│  1. PAYE Tax Brackets                                      │\n";
            echo "│  2. Tax Rebates                                            │\n";
            echo "│  3. UIF Contribution Rates                                 │\n";
            echo "│  4. SDL Information                                        │\n";
            echo "│  5. Medical Aid Tax Credits                                │\n";
            echo "│  6. Retirement Fund Limits                                 │\n";
            echo "│  7. Tax Thresholds                                         │\n";
            echo "│  0. Back to Main Menu                                       │\n";
            echo "└─────────────────────────────────────────────────────────────┘\n";

            $choice = $this->getInput('Enter your choice');

            match($choice) {
                '1' => $this->showPAYEBackets(),
                '2' => $this->showTaxRebates(),
                '3' => $this->showUIFRates(),
                '4' => $this->showSDLInfo(),
                '5' => $this->showMedicalAidCredits(),
                '6' => $this->showRetirementFundLimits(),
                '7' => $this->showTaxThresholds(),
                '0' => $this->clearScreen(),
                default => $this->error('Invalid choice. Please try again.'),
            };
        }
    }

    private function showPAYEBackets(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║              PAYE TAX BRACKETS (2024/2025)                  ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Income Range (Annual)         │ Rate │ Base Tax            ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  R0 - R237,100                 │ 18%  │ R0                  ║\n";
        echo "║  R237,101 - R370,500           │ 26%  │ R42,678             ║\n";
        echo "║  R370,501 - R512,800           │ 31%  │ R77,362             ║\n";
        echo "║  R512,801 - R673,000           │ 36%  │ R121,475            ║\n";
        echo "║  R673,001 - R857,900           │ 39%  │ R179,147            ║\n";
        echo "║  R857,901 - R1,817,000         │ 41%  │ R251,258            ║\n";
        echo "║  R1,817,001+                   │ 45%  │ R644,489            ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showTaxRebates(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                    TAX REBATES (2024/2025)                  ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Primary Rebate (all taxpayers):         R17,235 per year   ║\n";
        echo "║  Secondary Rebate (65 and older):        R9,417 per year    ║\n";
        echo "║  Tertiary Rebate (75 and older):         R3,141 per year    ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showUIFRates(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║            UIF CONTRIBUTION RATES (2024/2025)               ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Employee Contribution:     1% of gross remuneration       ║\n";
        echo "║  Employer Contribution:     1% of gross remuneration       ║\n";
        echo "║  Maximum Monthly Ceiling:   R17,712                        ║\n";
        echo "║  Maximum Annual Ceiling:    R212,544                       ║\n";
        echo "║  Maximum Monthly UIF:       R177.12                        ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showSDLInfo(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║         SKILLS DEVELOPMENT LEVY (SDL) - 2024/2025          ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  SDL Rate:                  1% of total payroll             ║\n";
        echo "║  Paid by:                   Employer only                   ║\n";
        echo "║  Purpose:                   Skills development funding      ║\n";
        echo "║  Note:                      No ceiling applies              ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showMedicalAidCredits(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║           MEDICAL AID TAX CREDITS (2024/2025)              ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        echo "║  Main Member:              R364 per month                   ║\n";
        echo "║  First Dependent:          R364 per month                   ║\n";
        echo "║  Additional Dependents:    R246 per month each              ║\n";
        echo "║  Note:  Credits reduce PAYE tax liability                   ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showRetirementFundLimits(): void
    {
        $limits = SouthAfricanTaxCalculator::getRetirementFundLimits();
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║          RETIREMENT FUND CONTRIBUTION LIMITS                ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Annual Limit:             R%-32s ║\n", number_format($limits['annual_limit'], 2));
        printf("║  Monthly Limit:            R%-32s ║\n", number_format($limits['monthly_limit'], 2));
        printf("║  Max Deduction Rate:       %-33s ║\n", ($limits['max_deduction_rate'] * 100) . '%');
        echo "║  Note:  Contributions above limits are not tax deductible  ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function showTaxThresholds(): void
    {
        $thresholds = SouthAfricanTaxCalculator::getTaxThresholds();
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║                  TAX THRESHOLDS (2024/2025)                 ║\n";
        echo "╠══════════════════════════════════════════════════════════════╣\n";
        printf("║  Under 65:                 R%-32s ║\n", number_format($thresholds['under_65'], 2));
        printf("║  65 to 74:                 R%-32s ║\n", number_format($thresholds['65_to_74'], 2));
        printf("║  75 and older:             R%-32s ║\n", number_format($thresholds['75_plus'], 2));
        echo "║  Note:  No PAYE if annual income below threshold            ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
    }

    private function exit(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║  Thank you for using SA Payroll Management System!         ║\n";
        echo "║  Goodbye!                                                  ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";
        exit(0);
    }

    private function getInput(string $prompt, string $default = ''): string
    {
        echo $prompt . ($default !== '' ? " [{$default}]" : '') . ': ';
        $handle = fopen('php://stdin', 'r');
        $line = fgets($handle);
        if ($line === false) {
            $this->exit();
        }
        $input = trim($line);
        return $input !== '' ? $input : $default;
    }

    private function clearScreen(): void
    {
        echo "\033[2J\033[1;1H";
    }

    private function success(string $message): void
    {
        echo "\n✓ {$message}\n";
    }

    private function error(string $message): void
    {
        echo "\n✗ Error: {$message}\n";
    }

    private function warning(string $message): void
    {
        echo "\n⚠ {$message}\n";
    }

    private function info(string $message): void
    {
        echo "\nℹ {$message}\n";
    }
}