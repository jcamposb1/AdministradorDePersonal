<?php

declare(strict_types=1);

namespace App\Models;

use CodeIgniter\Model;

class GrupoTrabajoModel extends Model
{
    protected $table      = 'grupostrabajo';
    protected $primaryKey = 'IDgrupoTrabajo';

    /** Texto (en minúsculas) que identifica al empleador propio; solo los demás llevan insignia. */
    private const EMPLEADOR_PROPIO = 'supralive';

    /**
     * Si tus columnas se llaman distinto, cambia SOLO estas dos en el SELECT:
     *   c.nombreCargo  (tabla cargos)
     *   em.nombreEmpleador  (tabla empleadores)
     */
    private const SQL_ESTRUCTURA = "
        SELECT
            a.IDarea,       a.nombre AS nombreArea,
            s.IDsubarea,    s.nombre AS nombreSubarea, s.descripcion AS descripcionSubarea,
            g.IDgrupoTrabajo, g.letra,
            es.nombreEscuelita,
            gp.IDgrupoPuesto, gp.IDcargo,
            c.nombreCargo,
            e.nombresApellidos,
            em.nombreEmpleador
        FROM grupostrabajo g
        INNER JOIN areas a              ON a.IDarea = g.IDarea
        LEFT JOIN subareas s            ON s.IDsubarea = g.IDsubarea
        LEFT JOIN escuelitas es         ON es.IDescuelita = g.IDescuelita
        LEFT JOIN grupospuestos gp      ON gp.IDgrupoTrabajo = g.IDgrupoTrabajo
                                       AND gp.fechaeliminacion IS NULL
        LEFT JOIN cargos c              ON c.IDcargo = gp.IDcargo
        LEFT JOIN empleados e           ON e.IDcargo = gp.IDcargo
                                       AND e.fechaeliminacion IS NULL
        LEFT JOIN empleadores em        ON em.IDempleador = e.IDempleador
        WHERE g.fechaeliminacion IS NULL
        ORDER BY a.orden, a.IDarea, s.orden, g.orden, gp.orden
    ";

    /**
     * Devuelve la estructura completa: Área -> Sub área -> Grupo -> Puestos.
     * Los grupos sin sub área quedan en la clave 0.
     */
    public static function obtenerEstructura(): array
    {
        $filas = \Config\Database::connect()->query(self::SQL_ESTRUCTURA)->getResultArray();

        $areas = [];

        foreach ($filas as $f) {
            $idArea  = (int) $f['IDarea'];
            $idSub   = $f['IDsubarea'] === null ? 0 : (int) $f['IDsubarea'];
            $idGrupo = (int) $f['IDgrupoTrabajo'];

            if (!isset($areas[$idArea])) {
                $areas[$idArea] = [
                    'IDarea'   => $idArea,
                    'nombre'   => $f['nombreArea'],
                    'subareas' => [],
                ];
            }

            if (!isset($areas[$idArea]['subareas'][$idSub])) {
                $areas[$idArea]['subareas'][$idSub] = [
                    'IDsubarea'   => $idSub,
                    'nombre'      => $f['nombreSubarea'],
                    'descripcion' => $f['descripcionSubarea'],
                    'grupos'      => [],
                ];
            }

            if (!isset($areas[$idArea]['subareas'][$idSub]['grupos'][$idGrupo])) {
                $areas[$idArea]['subareas'][$idSub]['grupos'][$idGrupo] = [
                    'IDgrupoTrabajo' => $idGrupo,
                    'letra'          => $f['letra'],
                    'escuelita'      => $f['nombreEscuelita'],
                    'puestos'        => [],
                ];
            }

            if ($f['IDgrupoPuesto'] !== null) {
                $idPuesto = (int) $f['IDgrupoPuesto'];
                $grupo    = &$areas[$idArea]['subareas'][$idSub]['grupos'][$idGrupo];

                if (!isset($grupo['puestos'][$idPuesto])) {
                    $nombre  = trim((string) $f['nombresApellidos']);
                    $vacante = ($nombre === '' || strtoupper($nombre) === 'X');
                    $empleador = trim((string) $f['nombreEmpleador']);

                    $grupo['puestos'][$idPuesto] = [
                        'cargo'     => $f['nombreCargo'],
                        'nombre'    => $vacante ? null : $nombre,
                        'vacante'   => $vacante,
                        'empleador' => $empleador,
                        'tercero'   => !$vacante
                                       && $empleador !== ''
                                       && stripos($empleador, self::EMPLEADOR_PROPIO) === false,
                    ];
                }
                unset($grupo);
            }
        }

        return $areas;
    }

    /** Totales para las cifras de arriba de la vista. */
    public static function calcularResumen(array $areas): array
    {
        $resumen = ['grupos' => 0, 'puestos' => 0, 'vacantes' => 0];

        foreach ($areas as $area) {
            foreach ($area['subareas'] as $sub) {
                foreach ($sub['grupos'] as $grupo) {
                    $resumen['grupos']++;
                    foreach ($grupo['puestos'] as $puesto) {
                        $resumen['puestos']++;
                        if ($puesto['vacante']) {
                            $resumen['vacantes']++;
                        }
                    }
                }
            }
        }

        return $resumen;
    }
}