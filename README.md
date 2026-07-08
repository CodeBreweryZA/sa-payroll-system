# South African Payroll Management System

A comprehensive console-based payroll management system built with PHP, implementing South African tax laws and payment policies for the 2024/2025 tax year.

## Features

- **Employee Management** - Add, update, delete, and search employees
- **Department Management** - Organize employees into departments
- **Payroll Processing** - Calculate monthly payroll with all statutory deductions
- **PAYE Tax Calculations** - SARS 2024/2025 tax brackets and rebates
- **UIF Contributions** - Employee and employer unemployment insurance
- **SDL (Skills Development Levy)** - Employer skills development contributions
- **Medical Aid Tax Credits** - Tax credits for medical aid contributions
- **Retirement Fund Deductions** - Pension/provident fund calculations
- **Payslip Generation** - Detailed payslips with all deductions
- **Reports** - Employee summaries, department reports, payroll summaries

## South African Tax Rules Implemented

### PAYE (Pay As You Earn) Tax Brackets 2024/2025
| Income Range (Annual) | Rate | Base Tax |
|----------------------|------|----------|
| R0 - R237,100 | 18% | R0 |
| R237,101 - R370,500 | 26% | R42,678 |
| R370,501 - R512,800 | 31% | R77,362 |
| R512,801 - R673,000 | 36% | R121,475 |
| R673,001 - R857,900 | 39% | R179,147 |
| R857,901 - R1,817,000 | 41% | R251,258 |
| R1,817,001+ | 45% | R644,489 |

### Tax Rebates
- **Primary Rebate**: R17,235 per year (all taxpayers)
- **Secondary Rebate**: R9,417 per year (65 and older)
- **Tertiary Rebate**: R3,141 per year (75 and older)

### UIF (Unemployment Insurance Fund)
- Employee Contribution: 1% of gross remuneration
- Employer Contribution: 1% of gross remuneration
- Maximum Monthly Ceiling: R17,712
- Maximum Monthly UIF: R177.12

### SDL (Skills Development Levy)
- Rate: 1% of total payroll
- Paid by: Employer only

### Medical Aid Tax Credits
- Main Member: R364 per month
- First Dependent: R364 per month
- Additional Dependents: R246 per month each

### Retirement Fund Contribution Limits
- Annual Limit: R350,000
- Monthly Limit: R29,166.67
- Maximum Deduction Rate: 27.5% of remuneration

## Requirements

- PHP 8.0 or higher
- SQLite3 extension enabled

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/sa-payroll-system.git
cd sa-payroll-system
```

2. Ensure SQLite3 extension is enabled in php.ini:
```ini
extension=sqlite3
```

3. Run the application:
```bash
php payroll.php
```

## Project Structure

```
sa-payroll-system/
├── payroll.php                    # Entry point
├── database/
│   └── payroll.db                 # SQLite database (auto-created)
├── src/
│   ├── autoload.php               # PSR-4 autoloader
│   ├── Console/
│   │   └── Application.php        # CLI menu interface
│   ├── Entity/
│   │   ├── Department.php         # Department entity
│   │   ├── Employee.php           # Employee entity
│   │   ├── Payslip.php            # Payslip entity
│   │   └── PayrollPeriod.php      # Payroll period entity
│   └── Service/
│       ├── Database.php           # Database connection & schema
│       ├── DepartmentService.php  # Department CRUD operations
│       ├── EmployeeService.php    # Employee CRUD operations
│       ├── PayslipService.php     # Payslip operations
│       ├── PayrollPeriodService.php # Payroll period operations
│       ├── PayrollService.php     # Main payroll processing
│       └── SouthAfricanTaxCalculator.php # SA tax calculations
└── README.md
```

## Usage

### Main Menu Options:
1. **Employee Management** - Manage employee records
2. **Department Management** - Manage company departments
3. **Payroll Processing** - Process monthly payroll
4. **Reports** - View various reports
5. **Tax Information** - View SARS tax rates and rules

### Processing Payroll:
1. Create a payroll period (e.g., 2024-01 for January 2024)
2. Enter employee-specific data (overtime, allowances, etc.)
3. System calculates all statutory deductions automatically
4. Review and approve payroll
5. Process payments

## License

This project is open source and available under the MIT License.

## Author

Created as a demonstration of PHP OOP skills and South African payroll knowledge.