<?php

namespace Payroll\Entity;

class Department
{
    private int $id;
    private string $name;
    private string $code;
    private string $description;
    private int $managerId;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data = [])
    {
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->code = $data['code'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->managerId = $data['manager_id'] ?? 0;
        $this->createdAt = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updatedAt = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getCode(): string { return $this->code; }
    public function getDescription(): string { return $this->description; }
    public function getManagerId(): int { return $this->managerId; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Setters
    public function setId(int $id): self { $this->id = $id; return $this; }
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setCode(string $code): self { $this->code = $code; return $this; }
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function setManagerId(int $managerId): self { $this->managerId = $managerId; return $this; }
    public function setUpdatedAt(string $updatedAt): self { $this->updatedAt = $updatedAt; return $this; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'manager_id' => $this->managerId,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}