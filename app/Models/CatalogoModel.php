<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

abstract class CatalogoModel extends Model
{
    protected $returnType = 'array';

    // Cada hijo indica cuál columna tiene el nombre (nombreZona, nombreCargo...)
    protected string $campoNombre = '';

    public static function listar(): array
    {
        $instancia = new static();
        return $instancia
            ->select("{$instancia->primaryKey} AS id, {$instancia->campoNombre} AS nombre")
            ->orderBy($instancia->campoNombre, 'ASC')
            ->findAll();
    }

    public static function nombrePorId(int $id): string
    {
        $instancia = new static();
        $fila      = $instancia->select("{$instancia->campoNombre} AS nombre")->find($id);

        return (string) ($fila['nombre'] ?? '');
    }
}