<?php

namespace Payroll\Service;

use SQLite3;

class Database
{
    private static ?self $instance = null;
    private SQLite3 $db;

    private function __construct()
    {
        $dbPath = __DIR__ . '/../../database/payroll.db';
        $this->db = new SQLite3($dbPath);
        $this->db->enableExceptions(true);
        $this->db->exec('PRAGMA journal_mode = WAL');
        $this->db->exec('PRAGMA foreign_keys = ON');
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): SQLite3
    {
        return $this->db;
    }

    public function initSchema(): void
    {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS departments (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL UNIQUE,
                code TEXT NOT NULL UNIQUE,
                description TEXT,
                manager_id INTEGER,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS employees (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_number TEXT NOT NULL UNIQUE,
                first_name TEXT NOT NULL,
                last_name TEXT NOT NULL,
                email TEXT NOT NULL UNIQUE,
                phone TEXT,
                address TEXT,
                department_id INTEGER,
                position TEXT NOT NULL,
                base_salary REAL DEFAULT 0,
                hourly_rate REAL DEFAULT 0,
                employment_type TEXT DEFAULT "salary",
                hire_date DATE NOT NULL,
                date_of_birth DATE,
                tax_number TEXT,
                id_number TEXT,
                status TEXT DEFAULT "active",
                retirement_fund_member INTEGER DEFAULT 0,
                medical_aid_member INTEGER DEFAULT 0,
                medical_aid_members INTEGER DEFAULT 0,
                medical_aid_main_members INTEGER DEFAULT 1,
                medical_aid_dependents INTEGER DEFAULT 0,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (department_id) REFERENCES departments(id)
            )
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS payroll_periods (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                pay_date DATE NOT NULL,
                status TEXT DEFAULT "draft",
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS payslips (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                payroll_period_id INTEGER NOT NULL,
                basic_salary REAL DEFAULT 0,
                overtime_hours REAL DEFAULT 0,
                overtime_rate REAL DEFAULT 0,
                overtime_pay REAL DEFAULT 0,
                allowances REAL DEFAULT 0,
                bonuses REAL DEFAULT 0,
                commission REAL DEFAULT 0,
                other_income REAL DEFAULT 0,
                gross_pay REAL DEFAULT 0,
                paye REAL DEFAULT 0,
                uif_employee REAL DEFAULT 0,
                uif_employer REAL DEFAULT 0,
                sdl REAL DEFAULT 0,
                medical_aid_employee REAL DEFAULT 0,
                medical_aid_employer REAL DEFAULT 0,
                retirement_fund_employee REAL DEFAULT 0,
                retirement_fund_employer REAL DEFAULT 0,
                other_deductions REAL DEFAULT 0,
                total_deductions REAL DEFAULT 0,
                net_pay REAL DEFAULT 0,
                employer_cost REAL DEFAULT 0,
                status TEXT DEFAULT "draft",
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id),
                FOREIGN KEY (payroll_period_id) REFERENCES payroll_periods(id)
            )
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS leave_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                leave_type TEXT NOT NULL,
                start_date DATE NOT NULL,
                end_date DATE NOT NULL,
                days INTEGER NOT NULL,
                status TEXT DEFAULT "pending",
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id)
            )
        ');

        $this->db->exec('
            CREATE TABLE IF NOT EXISTS attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                employee_id INTEGER NOT NULL,
                date DATE NOT NULL,
                clock_in TIME,
                clock_out TIME,
                hours_worked REAL DEFAULT 0,
                overtime_hours REAL DEFAULT 0,
                status TEXT DEFAULT "present",
                notes TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (employee_id) REFERENCES employees(id),
                UNIQUE(employee_id, date)
            )
        ');

        $this->seedDepartments();
    }

    private function seedDepartments(): void
    {
        $check = $this->db->querySingle('SELECT COUNT(*) FROM departments');
        if ($check > 0) {
            return;
        }

        $departments = [
            ['Human Resources', 'HR', 'Human Resources Department'],
            ['Finance', 'FIN', 'Finance and Accounting Department'],
            ['Information Technology', 'IT', 'Information Technology Department'],
            ['Marketing', 'MKT', 'Marketing and Communications Department'],
            ['Sales', 'SAL', 'Sales Department'],
            ['Operations', 'OPS', 'Operations Department'],
            ['Legal', 'LGL', 'Legal Department'],
            ['Customer Service', 'CS', 'Customer Service Department'],
        ];

        $stmt = $this->db->prepare('INSERT INTO departments (name, code, description) VALUES (:name, :code, :desc)');
        foreach ($departments as $dept) {
            $stmt->bindValue(':name', $dept[0], SQLITE3_TEXT);
            $stmt->bindValue(':code', $dept[1], SQLITE3_TEXT);
            $stmt->bindValue(':desc', $dept[2], SQLITE3_TEXT);
            $stmt->execute();
            $stmt->reset();
        }
    }
}