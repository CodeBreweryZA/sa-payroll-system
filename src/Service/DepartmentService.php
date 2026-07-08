<?php

namespace Payroll\Service;

use Payroll\Entity\Department;
use Payroll\Service\Database;

class DepartmentService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $result = $this->db->getConnection()->query('SELECT * FROM departments ORDER BY name');
        $departments = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $departments[] = new Department($row);
        }
        return $departments;
    }

    public function findById(int $id): ?Department
    {
        $stmt = $this->db->getConnection()->prepare('SELECT * FROM departments WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? new Department($row) : null;
    }

    public function create(Department $department): Department
    {
        $stmt = $this->db->getConnection()->prepare('INSERT INTO departments (name, code, description) VALUES (:name, :code, :desc)');
        $stmt->bindValue(':name', $department->getName(), SQLITE3_TEXT);
        $stmt->bindValue(':code', $department->getCode(), SQLITE3_TEXT);
        $stmt->bindValue(':desc', $department->getDescription(), SQLITE3_TEXT);
        $stmt->execute();

        $id = $this->db->getConnection()->lastInsertRowID();
        return $this->findById($id);
    }

    public function update(Department $department): Department
    {
        $stmt = $this->db->getConnection()->prepare('UPDATE departments SET name = :name, code = :code, description = :desc, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->bindValue(':id', $department->getId(), SQLITE3_INTEGER);
        $stmt->bindValue(':name', $department->getName(), SQLITE3_TEXT);
        $stmt->bindValue(':code', $department->getCode(), SQLITE3_TEXT);
        $stmt->bindValue(':desc', $department->getDescription(), SQLITE3_TEXT);
        $stmt->execute();

        return $this->findById($department->getId());
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->getConnection()->prepare('DELETE FROM departments WHERE id = :id');
        $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
        $stmt->execute();
        return $this->db->getConnection()->changes() > 0;
    }

    public function count(): int
    {
        return (int) $this->db->getConnection()->querySingle('SELECT COUNT(*) FROM departments');
    }

    public function getEmployeeCount(int $departmentId): int
    {
        $stmt = $this->db->getConnection()->prepare("SELECT COUNT(*) FROM employees WHERE department_id = :id AND status = 'active'");
        $stmt->bindValue(':id', $departmentId, SQLITE3_INTEGER);
        return (int) $stmt->execute()->fetchArray()[0];
    }
}