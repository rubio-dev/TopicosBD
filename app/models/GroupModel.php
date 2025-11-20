<?php
declare(strict_types=1);

class GroupModel
{
    public int $id;
    public string $package;
    public string $groupLetter;
    public ?string $hourMon;
    public ?string $hourTue;
    public ?string $hourWed;
    public ?string $hourThu;
    public ?string $hourFri;

    public function __construct(array $row)
    {
        $this->id          = (int)$row['id_grupo'];
        $this->package     = $row['paquete'];
        $this->groupLetter = $row['grupo'];
        $this->hourMon     = $row['hor_lun'];
        $this->hourTue     = $row['hor_mar'];
        $this->hourWed     = $row['hor_mie'];
        $this->hourThu     = $row['hor_jue'];
        $this->hourFri     = $row['hor_vie'];
    }
}
