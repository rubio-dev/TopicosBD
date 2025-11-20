<?php
declare(strict_types=1);

class Subject
{
    public string $code;
    public string $name;
    public int $semester;
    public int $priority;

    public function __construct(array $row)
    {
        $this->code     = $row['clave_materia'];
        $this->name     = $row['nombre'];
        $this->semester = (int)$row['semestre_sugerido'];
        $this->priority = (int)$row['prioridad'];
    }
}
