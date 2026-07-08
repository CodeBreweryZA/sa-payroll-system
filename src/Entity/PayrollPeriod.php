<?php

namespace Payroll\Entity;

class PayrollPeriod
{
    private int $id;
    private string $name;
    private string $startDate;
    private string $endDate;
    private string $payDate;
    private string $status; // 'draft', 'processing', 'completed', 'paid'
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->startDate = $data['start_date'] ?? '';
        $this->endDate = $data['end_date'] ?? '';
        $this->payDate = $data['pay_date'] ?? '';
        $this->status = $data['status'] ?? 'draft';
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getStartDate(): string { return $this->startDate; }
    public function getEndDate(): string { return $this->endDate; }
    public function getPayDate(): string { return $this->payDate; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setStartDate(string $startDate): self { $this->startDate = $startDate; return $this; }
    public function setEndDate(string $endDate): self { $this->endDate = $endDate; return $this; }
    public function setPayDate(string $payDate): self { $this->payDate = $payDate; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setUpdatedAt(string $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'pay_date' => $this->payDate,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}