<?php
declare(strict_types=1);

class Student
{
    public string $controlNumber;
    public string $name;
    public int $currentSemester;

    public function __construct(array $row)
    {
        $this->controlNumber   = $row['num_control'];
        $this->name            = $row['nombre'];
        $this->currentSemester = (int)$row['semestre_actual'];
    }
}
