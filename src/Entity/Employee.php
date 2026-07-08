<?php

namespace Payroll\Entity;

class Employee
{
    private int $id;
    private string $employeeNumber;
    private string $firstName;
    private string $lastName;
    private string $email;
    private string $phone;
    private string $address;
    private int $departmentId;
    private string $position;
    private float $baseSalary;
    private float $hourlyRate;
    private string $employmentType; // 'salary' or 'hourly'
    private string $hireDate;
    private string $status; // 'active', 'inactive', 'terminated'
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->employeeNumber = $data['employee_number'] ?? '';
        $this->firstName = $data['first_name'] ?? '';
        $this->lastName = $data['last_name'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->departmentId = $data['department_id'] ?? 0;
        $this->position = $data['position'] ?? '';
        $this->baseSalary = $data['base_salary'] ?? 0.0;
        $this->hourlyRate = $data['hourly_rate'] ?? 0.0;
        $this->employmentType = $data['employment_type'] ?? 'salary';
        $this->hireDate = $data['hire_date'] ?? date('Y-m-d');
        $this->status = $data['status'] ?? 'active';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getEmployeeNumber(): string { return $this->employeeNumber; }
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getFullName(): string { return $this->firstName . ' ' . $this->lastName; }
    public function getEmail(): string { return $this->email; }
    public function getPhone(): string { return $this->phone; }
    public function getAddress(): string { return $this->address; }
    public function getDepartmentId(): int { return $this->departmentId; }
    public function getPosition(): string { return $this->position; }
    public function getBaseSalary(): float { return $this->baseSalary; }
    public function getHourlyRate(): float { return $this->hourlyRate; }
    public function getEmploymentType(): string { return $this->employmentType; }
    public function getHireDate(): string { return $this->hireDate; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Setters
    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setEmployeeNumber(string $employeeNumber): self { $this->employeeNumber = $employeeNumber; return $this; }
    public function setFirstName(string $firstName): self { $this->firstName = $firstName; return $this; }
    public function setLastName(string $lastName): self { $this->lastName = $lastName; return $this; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPhone(string $phone): self { $this->phone = $phone; return $this; }
    public function setAddress(string $address): self { $this->address = $address; return $this; }
    public function setDepartmentId(int $departmentId): self { $this->departmentId = $departmentId; return $this; }
    public function setPosition(string $position): self { $this->position = $position; return $this; }
    public function setBaseSalary(float $baseSalary): self { $this->baseSalary = $baseSalary; return $this; }
    public function setHourlyRate(float $hourlyRate): self { $this->hourlyRate = $hourlyRate; return $this; }
    public function setEmploymentType(string $employmentType): self { $this->employmentType = $employmentType; return $this; }
    public function setHireDate(string $hireDate): self { $this->hireDate = $hireDate; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setUpdatedAt(string $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_number' => $this->employeeNumber,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'department_id' => $this->departmentId,
            'position' => $this->position,
            'base_salary' => $this->baseSalary,
            'hourly_rate' => $this->hourlyRate,
            'employment_type' => $this->employmentType,
            'hire_date' => $this->hireDate,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isHourly(): bool
    {
        return $this->employmentType === 'hourly';
    }

    public function isSalary(): bool
    {
        return $this->employmentType === 'salary';
    }
}