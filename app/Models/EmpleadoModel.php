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
        'foto',
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
    public static function normalizarCedula ( $cedula) : string 
    {
        $cedula = preg_replace('/\D/', '', $cedula) ?? '';
        if(strlen($cedula)===9){
            return'0'.$cedula;
        }
        return $cedula;
    }

    public static function obtenerCatalogos(): array
    {
        return [
            'empleadores' => EmpleadorModel::listar(),
            'cargos'      => CargoModel::listar(),
            'zonas'       => ZonaModel::listar(),
            'estados'     => EstadoModel::listar(),
        ];
    }

    public static function buscarPorId(int $id): ?EmpleadoEntity
    {
        return (new static())->find($id);
    }

    public static function registrar(array $datos): int|string|false
    {
        $datos['fechacreacion'] = date('Y-m-d H:i:s');

        return (new static())->insert($datos);
    }

    /**
     * Cambia Activo <-> Inactivo. Devuelve el nuevo estado o null si no existe.
     * @return array{id:int, nombre:string}|null
     */
    public static function alternarEstado(int $id): ?array
    {
        $instancia = new static();
        $empleado  = $instancia->find($id);
        if ($empleado === null) {
            return null;
        }
        $nuevo = $empleado->estaActivo()
            ? EmpleadoEntity::ESTADO_INACTIVO
            : EmpleadoEntity::ESTADO_ACTIVO;
        $instancia->update($id, ['IDestado' => $nuevo]);
        return ['id' => $nuevo, 'nombre' => EstadoModel::nombrePorId($nuevo)];
    }

    public static function eliminarPorId(int $id): bool
    {
        try {
            return (new static())->delete($id) !== false;
        } catch (\Throwable $e) {
            log_message('error', 'No se pudo eliminar el empleado {id}: {msg}', [
                'id'  => $id,
                'msg' => $e->getMessage(),
            ]);
            return false;
        }
    }
}