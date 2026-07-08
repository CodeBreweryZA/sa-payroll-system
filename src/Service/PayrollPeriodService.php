<?php

namespace Payroll\Service;

use Payroll\Entity\PayrollPeriod;
use Payroll\Service\Database;

class PayrollPeriodService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $result = $this->db->getConnection()->query('SELECT * FROM payroll_periods ORDER BY start_date DESC');
        $periods = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $periods[] = new PayrollPeriod($row);
        }
        return $periods;
    }

    public function findById(int $id): ?PayrollPeriod
    {
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM payroll_periods WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? new PayrollPeriod($row) : null;
    }

    public function findCurrentPeriod(): ?PayrollPeriod
    {
        $result = $this->db->getConnection()->querySingle("SELECT * FROM payroll_periods WHERE status IN ('draft','processing') ORDER BY start_date DESC LIMIT 1", true);
        return $result ? new PayrollPeriod($result) : null;
    }

    public function findLatest(): ?PayrollPeriod
    {
        $result = $this->db->getConnection()->querySingle('SELECT * FROM payroll_periods ORDER BY start_date DESC LIMIT 1', true);
        return $result ? new PayrollPeriod($result) : null;
    }

    public function create(PayrollPeriod $period): PayrollPeriod
    {
        $stmt = $this->db->getConnection()->prepare('INSERT INTO payroll_periods (name, start_date, end_date, pay_date, status) VALUES (:name, :start, :end, :pay, :status)');
        $stmt->bindValue(':name', $period->getName(), SQLITE3_TEXT);
        $stmt->bindValue(':start', $period->getStartDate(), SQLITE3_TEXT);
        $stmt->bindValue(':end', $period->getEndDate(), SQLITE3_TEXT);
        $stmt->bindValue(':pay', $period->getPayDate(), SQLITE3_TEXT);
        $stmt->bindValue(':status', $period->getStatus(), SQLITE3_TEXT);
        $stmt->execute();

        $id = $this->db->getConnection()->lastInsertRowID();
        return $this->findById($id);
    }

    public function update(PayrollPeriod $period): PayrollPeriod
    {
        $stmt = $this->db->getConnection()->prepare('UPDATE payroll_periods SET name = :name, start_date = :start, end_date = :end, pay_date = :pay, status = :status, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $period->getId(), SQLITE3_INTEGER);
        $stmt->bindValue(':name', $period->getName(), SQLITE3_TEXT);
        $stmt->bindValue(':start', $period->getStartDate(), SQLITE3_TEXT);
        $stmt->bindValue(':end', $period->getEndDate(), SQLITE3_TEXT);
        $stmt->bindValue(':pay', $period->getPayDate(), SQLITE3_TEXT);
        $stmt->bindValue(':status', $period->getStatus(), SQLITE3_TEXT);
        $stmt->execute();

        return $this->findById($period->getId());
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->getConnection()->prepare('DELETE FROM payroll_periods WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->getConnection()->changes() > 0;
    }

    public function generateMonthlyPeriod(string $monthYear): PayrollPeriod
    {
        $date = new DateTime($monthYear . '-01');
        $startDate = $date->format('Y-m-d');
        $endDate = $date->format('Y-m-t');
        $payDate = $date->format('Y-m-25');
        
        $name = $date->format('F Y') . ' Payroll';
        
        $period = new PayrollPeriod([
            'name' => $name,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'pay_date' => $payDate,
            'status' => 'draft',
        ]);

        return $this->create($period);
    }
}