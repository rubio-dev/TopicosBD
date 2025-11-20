<?php
declare(strict_types=1);

class Config
{
    public const DB_HOST = 'localhost';
    public const DB_NAME = 'proyecto_horarios'; // mismo nombre de la BD
    public const DB_USER = 'root';
    public const DB_PASS = '';
    public const DB_CHARSET = 'utf8mb4';

    // Para enlaces en el dashboard (ajusta según tu entorno)
    public const BASE_URL = 'http://localhost/TopicosBD/public';
}
