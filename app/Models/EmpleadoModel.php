<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\EmpleadoEntity;

class EmpleadoModel extends Model
{
    protected $table            = 'empleados';
    protected $primaryKey       = 'IDempleado';
    protected $returnType       = EmpleadoEntity::class;
    protected $useAutoIncrement = true;
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'cedula',
        'nombresApellidos',
        'fechaNacimiento',
        'fechaIngreso',
        'IDempleador',
        'IDcargo',
        'IDzona',
        'IDestado',
        'fechacreacion'
    ];

    /**
     * Fachada estática que realiza los JOINs y mapea a EmpleadoEntity
     * @return EmpleadoEntity[]
     */
    public static function obtenerListadoCompleto(): array
    {
        $instancia = new static();

        return $instancia->select('
                empleados.*, 
                empleadores.nombreEmpleador AS nombre_empleador,
                cargos.nombreCargo AS nombre_cargo,
                zonas.nombreZona AS nombre_zona,
                estados.nombreEstado AS nombre_estado
            ')
            ->join('empleadores', 'empleadores.IDempleador = empleados.IDempleador', 'left')
            ->join('cargos', 'cargos.IDcargo = empleados.IDcargo', 'left')
            ->join('zonas', 'zonas.IDzona = empleados.IDzona', 'left')
            ->join('estados', 'estados.IDestado = empleados.IDestado', 'left')
            ->orderBy('empleados.IDempleado', 'ASC')
            ->findAll();
    }
}