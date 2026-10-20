<?php

declare(strict_types=1);

namespace App\Models;

use App\DTOs\GrupoTrabajoFormulario;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;
use Config\Database;
use DomainException;
use RuntimeException;
use Throwable;

class GrupoTrabajoModel extends Model
{
    protected $table      = 'grupostrabajo';
    protected $primaryKey = 'IDgrupoTrabajo';

    /** Texto (en minúsculas) que identifica al empleador propio; solo los demás llevan insignia. */
    private const EMPLEADOR_PROPIO = 'supralive';

    /**
     * Si tus columnas se llaman distinto, cambia SOLO estas dos en el SELECT:
     *   c.nombreCargo       (tabla cargos)
     *   em.nombreEmpleador  (tabla empleadores)
     */
    private const SQL_ESTRUCTURA = "
        SELECT
            a.IDarea,       a.nombre AS nombreArea,
            s.IDsubarea,    s.nombre AS nombreSubarea, s.descripcion AS descripcionSubarea,
            g.IDgrupoTrabajo, g.letra, g.IDescuelita,
            es.nombreEscuelita,
            gp.IDgrupoPuesto, gp.IDcargo,
            c.nombreCargo,
            e.IDempleado, e.nombresApellidos,
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

    // ------------------------------------------------------------------
    // Lectura
    // ------------------------------------------------------------------

    /**
     * Devuelve la estructura completa: Área -> Sub área -> Grupo -> Puestos.
     * Los grupos sin sub área quedan en la clave 0.
     */
    public static function obtenerEstructura(): array
    {
        $filas = self::conexion()->query(self::SQL_ESTRUCTURA)->getResultArray();

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
                    'IDarea'         => $idArea,
                    'IDsubarea'      => $idSub,
                    'IDescuelita'    => $f['IDescuelita'] === null ? 0 : (int) $f['IDescuelita'],
                    'areaNombre'     => $f['nombreArea'],
                    'subareaNombre'  => $f['nombreSubarea'],
                    'letra'          => $f['letra'],
                    'escuelita'      => $f['nombreEscuelita'],
                    'puestos'        => [],
                ];
            }

            if ($f['IDgrupoPuesto'] !== null) {
                $idPuesto = (int) $f['IDgrupoPuesto'];
                $grupo    = &$areas[$idArea]['subareas'][$idSub]['grupos'][$idGrupo];

                if (!isset($grupo['puestos'][$idPuesto])) {
                    $nombre    = trim((string) $f['nombresApellidos']);
                    $vacante   = ($nombre === '' || strtoupper($nombre) === 'X');
                    $empleador = trim((string) $f['nombreEmpleador']);

                    $grupo['puestos'][$idPuesto] = [
                        'IDgrupoPuesto' => $idPuesto,
                        'IDempleado'    => $vacante || $f['IDempleado'] === null ? 0 : (int) $f['IDempleado'],
                        'cargo'         => $f['nombreCargo'],
                        'nombre'        => $vacante ? null : $nombre,
                        'vacante'       => $vacante,
                        'empleador'     => $empleador,
                        'tercero'       => !$vacante
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

    /** Listas para los formularios (áreas, sub áreas, escuelitas y cargos existentes). */
    public static function obtenerCatalogos(): array
    {
        $db = self::conexion();

        return [
            'areas'      => $db->query('SELECT IDarea, nombre FROM areas WHERE fechaeliminacion IS NULL ORDER BY orden, IDarea')->getResultArray(),
            'subareas'   => $db->query('SELECT IDsubarea, IDarea, nombre FROM subareas WHERE fechaeliminacion IS NULL ORDER BY orden, IDsubarea')->getResultArray(),
            'escuelitas' => $db->query('SELECT IDescuelita, nombreEscuelita FROM escuelitas WHERE fechaeliminacion IS NULL ORDER BY IDescuelita')->getResultArray(),
            'cargos'     => $db->query('SELECT nombreCargo FROM cargos ORDER BY nombreCargo')->getResultArray(),
        ];
    }

    /** Búsqueda rápida de trabajadores por nombre o apellido, con varias palabras en cualquier orden. */
    public static function buscarPersonal(string $texto): array
    {
        $texto = trim($texto);
        if (mb_strlen($texto) < 2) {
            return [];
        }

        $palabras = array_slice(preg_split('/\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 5);

        $consulta = self::conexion()->table('empleados e')
            ->select('e.IDempleado, e.nombresApellidos, c.nombreCargo, gp.IDgrupoPuesto')
            ->join('cargos c', 'c.IDcargo = e.IDcargo', 'left')
            ->join('grupospuestos gp', 'gp.IDcargo = e.IDcargo AND gp.fechaeliminacion IS NULL', 'left')
            ->where('e.fechaeliminacion IS NULL');

        foreach ($palabras as $palabra) {
            $consulta->like('e.nombresApellidos', $palabra);
        }

        $filas = $consulta->orderBy('e.nombresApellidos')->limit(15)->get()->getResultArray();

        return array_map(static fn(array $f): array => [
            'IDempleado'       => (int) $f['IDempleado'],
            'nombresApellidos' => $f['nombresApellidos'],
            'nombreCargo'      => $f['nombreCargo'],
            'enMesa'           => $f['IDgrupoPuesto'] !== null,
        ], $filas);
    }

    // ------------------------------------------------------------------
    // Escritura: personal
    // ------------------------------------------------------------------

    /**
     * Mueve a un trabajador a un puesto. Mover = cambiar su IDcargo por el del puesto destino
     * (el mismo criterio del BUSCARV: el cargo define en qué mesa aparece).
     * Si el destino está ocupado y el trabajador viene de otra mesa, se intercambian.
     */
    public static function moverPersona(int $idEmpleado, int $idPuestoDestino): array
    {
        $db = self::conexion();

        $empleado = $db->table('empleados')
            ->select('IDempleado, IDcargo, nombresApellidos')
            ->where('IDempleado', $idEmpleado)
            ->where('fechaeliminacion IS NULL')
            ->get()->getRowArray();

        if ($empleado === null) {
            return self::respuesta(false, 'No se encontró al trabajador.');
        }

        $destino = $db->query(
            'SELECT gp.IDgrupoPuesto, gp.IDcargo
               FROM grupospuestos gp
              INNER JOIN grupostrabajo g ON g.IDgrupoTrabajo = gp.IDgrupoTrabajo
              WHERE gp.IDgrupoPuesto = ? AND gp.fechaeliminacion IS NULL AND g.fechaeliminacion IS NULL',
            [$idPuestoDestino]
        )->getRowArray();

        if ($destino === null) {
            return self::respuesta(false, 'El puesto de destino no existe.');
        }

        $idCargoOrigen  = (int) $empleado['IDcargo'];
        $idCargoDestino = (int) $destino['IDcargo'];

        if ($idCargoOrigen === $idCargoDestino) {
            return self::respuesta(true, 'La persona ya está en ese puesto.');
        }

        $ocupantes = $db->table('empleados')
            ->select('IDempleado, nombresApellidos')
            ->where('IDcargo', $idCargoDestino)
            ->where('fechaeliminacion IS NULL')
            ->get()->getResultArray();

        if (count($ocupantes) > 1) {
            return self::respuesta(false, 'Ese cargo tiene más de una persona asignada. Corrígelo en Base de Personal.');
        }

        $ocupante = $ocupantes[0] ?? null;

        if ($ocupante !== null) {
            $origenEsPuestoDeMesa = $db->table('grupospuestos')
                ->where('IDcargo', $idCargoOrigen)
                ->where('fechaeliminacion IS NULL')
                ->countAllResults() > 0;

            if (!$origenEsPuestoDeMesa) {
                return self::respuesta(false, 'Ese puesto está ocupado por ' . $ocupante['nombresApellidos']
                    . '. Elige un puesto vacante o arrastra desde otra mesa para intercambiar.');
            }
        }

        $ahora = date('Y-m-d H:i:s');

        try {
            $db->transBegin();

            $db->table('empleados')->where('IDempleado', $idEmpleado)
                ->update(['IDcargo' => $idCargoDestino, 'fechaactualizacion' => $ahora]);

            if ($ocupante !== null) {
                $db->table('empleados')->where('IDempleado', (int) $ocupante['IDempleado'])
                    ->update(['IDcargo' => $idCargoOrigen, 'fechaactualizacion' => $ahora]);
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Falló la actualización de empleados.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'moverPersona: ' . $e->getMessage());

            return self::respuesta(false, 'No se pudo mover a la persona. Intenta nuevamente.');
        }

        return self::respuesta(
            true,
            $ocupante !== null ? 'Intercambio guardado.' : 'Persona movida.',
            ['intercambio' => $ocupante !== null]
        );
    }

    // ------------------------------------------------------------------
    // Escritura: grupos
    // ------------------------------------------------------------------

    /** Crea o edita una mesa con sus puestos, todo en una transacción. */
    public static function guardarGrupo(GrupoTrabajoFormulario $formulario): array
    {
        $db    = self::conexion();
        $ahora = date('Y-m-d H:i:s');

        try {
            $db->transBegin();

            self::validarAreaYSubarea($db, $formulario);

            $datosGrupo = [
                'IDarea'             => $formulario->getIDarea(),
                'IDsubarea'          => $formulario->getIDsubarea(),
                'IDescuelita'        => $formulario->getIDescuelita(),
                'letra'              => $formulario->getLetra(),
                'fechaactualizacion' => $ahora,
            ];

            $existentes = [];

            if ($formulario->esNuevo()) {
                $orden = (int) $db->query(
                    'SELECT COALESCE(MAX(orden), 0) + 1 AS siguiente FROM grupostrabajo WHERE IDarea = ? AND fechaeliminacion IS NULL',
                    [$formulario->getIDarea()]
                )->getRow()->siguiente;

                $db->table('grupostrabajo')->insert($datosGrupo + ['orden' => $orden, 'fechacreacion' => $ahora]);
                $idGrupo = (int) $db->insertID();
            } else {
                $idGrupo = $formulario->getIDgrupoTrabajo();

                $existe = $db->table('grupostrabajo')
                    ->where('IDgrupoTrabajo', $idGrupo)
                    ->where('fechaeliminacion IS NULL')
                    ->countAllResults();

                if ($existe === 0) {
                    throw new DomainException('La mesa ya no existe. Recarga la página.');
                }

                $db->table('grupostrabajo')->where('IDgrupoTrabajo', $idGrupo)->update($datosGrupo);

                $filas = $db->table('grupospuestos')
                    ->select('IDgrupoPuesto, IDcargo')
                    ->where('IDgrupoTrabajo', $idGrupo)
                    ->where('fechaeliminacion IS NULL')
                    ->get()->getResultArray();

                foreach ($filas as $fila) {
                    $existentes[(int) $fila['IDgrupoPuesto']] = (int) $fila['IDcargo'];
                }
            }

            $conservados  = [];
            $cargosEnMesa = [];

            foreach ($formulario->getPuestos() as $posicion => $puesto) {
                $idCargo = self::obtenerOCrearCargo($db, $puesto['cargo'], $ahora);

                if (isset($cargosEnMesa[$idCargo])) {
                    throw new DomainException('El cargo «' . $puesto['cargo'] . '» está repetido en la mesa.');
                }
                $cargosEnMesa[$idCargo] = true;

                $idPuesto = $puesto['IDgrupoPuesto'];

                $enOtroPuesto = $db->table('grupospuestos')
                    ->where('IDcargo', $idCargo)
                    ->where('fechaeliminacion IS NULL')
                    ->where('IDgrupoPuesto !=', $idPuesto)
                    ->countAllResults();

                if ($enOtroPuesto > 0) {
                    throw new DomainException('El cargo «' . $puesto['cargo'] . '» ya pertenece a otra mesa.');
                }

                if ($idPuesto > 0 && isset($existentes[$idPuesto])) {
                    $cargoActual = $existentes[$idPuesto];

                    if ($cargoActual !== $idCargo && self::contarOcupantes($db, $cargoActual) > 0) {
                        throw new DomainException('No se puede cambiar el cargo de un puesto ocupado. Mueve primero a la persona.');
                    }

                    $db->table('grupospuestos')->where('IDgrupoPuesto', $idPuesto)->update([
                        'IDcargo'            => $idCargo,
                        'orden'              => $posicion + 1,
                        'fechaactualizacion' => $ahora,
                    ]);
                    $conservados[] = $idPuesto;
                } else {
                    $db->table('grupospuestos')->insert([
                        'IDgrupoTrabajo' => $idGrupo,
                        'IDcargo'        => $idCargo,
                        'orden'          => $posicion + 1,
                        'fechacreacion'  => $ahora,
                    ]);
                }
            }

            foreach ($existentes as $idPuesto => $idCargo) {
                if (in_array($idPuesto, $conservados, true)) {
                    continue;
                }

                if (self::contarOcupantes($db, $idCargo) > 0) {
                    throw new DomainException('No se puede quitar un puesto ocupado. Mueve primero a la persona.');
                }

                $db->table('grupospuestos')->where('IDgrupoPuesto', $idPuesto)
                    ->update(['fechaeliminacion' => $ahora, 'fechaactualizacion' => $ahora]);
            }

            if ($db->transStatus() === false) {
                throw new RuntimeException('Falló alguna instrucción de la transacción.');
            }

            $db->transCommit();
        } catch (DomainException $e) {
            $db->transRollback();

            return self::respuesta(false, $e->getMessage());
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'guardarGrupo: ' . $e->getMessage());

            return self::respuesta(false, 'No se pudo guardar la mesa. Intenta nuevamente.');
        }

        return self::respuesta(true, $formulario->esNuevo() ? 'Mesa creada.' : 'Mesa actualizada.', ['IDgrupoTrabajo' => $idGrupo]);
    }

    /**
     * Elimina una mesa (borrado lógico). Las personas conservan su cargo,
     * solo dejan de aparecer en el listado hasta que se les asigne otra mesa.
     */
    public static function eliminarGrupo(int $idGrupo): array
    {
        $db    = self::conexion();
        $ahora = date('Y-m-d H:i:s');

        $existe = $db->table('grupostrabajo')
            ->where('IDgrupoTrabajo', $idGrupo)
            ->where('fechaeliminacion IS NULL')
            ->countAllResults();

        if ($existe === 0) {
            return self::respuesta(false, 'La mesa no existe o ya fue eliminada.');
        }

        try {
            $db->transBegin();

            $db->table('grupospuestos')
                ->where('IDgrupoTrabajo', $idGrupo)
                ->where('fechaeliminacion IS NULL')
                ->update(['fechaeliminacion' => $ahora, 'fechaactualizacion' => $ahora]);

            $db->table('grupostrabajo')
                ->where('IDgrupoTrabajo', $idGrupo)
                ->update(['fechaeliminacion' => $ahora, 'fechaactualizacion' => $ahora]);

            if ($db->transStatus() === false) {
                throw new RuntimeException('Falló el borrado lógico.');
            }

            $db->transCommit();
        } catch (Throwable $e) {
            $db->transRollback();
            log_message('error', 'eliminarGrupo: ' . $e->getMessage());

            return self::respuesta(false, 'No se pudo eliminar la mesa. Intenta nuevamente.');
        }

        return self::respuesta(true, 'Mesa eliminada.');
    }

    // ------------------------------------------------------------------
    // Apoyo interno
    // ------------------------------------------------------------------

    private static function conexion(): BaseConnection
    {
        return Database::connect();
    }

    private static function respuesta(bool $ok, string $mensaje, array $extra = []): array
    {
        return ['ok' => $ok, 'mensaje' => $mensaje] + $extra;
    }

    private static function validarAreaYSubarea(BaseConnection $db, GrupoTrabajoFormulario $formulario): void
    {
        $area = $db->table('areas')
            ->where('IDarea', $formulario->getIDarea())
            ->where('fechaeliminacion IS NULL')
            ->countAllResults();

        if ($area === 0) {
            throw new DomainException('El área seleccionada no existe.');
        }

        if ($formulario->getIDsubarea() !== null) {
            $subarea = $db->table('subareas')
                ->where('IDsubarea', $formulario->getIDsubarea())
                ->where('IDarea', $formulario->getIDarea())
                ->where('fechaeliminacion IS NULL')
                ->countAllResults();

            if ($subarea === 0) {
                throw new DomainException('La sub área no pertenece al área elegida.');
            }
        }

        if ($formulario->getIDescuelita() !== null) {
            $escuelita = $db->table('escuelitas')
                ->where('IDescuelita', $formulario->getIDescuelita())
                ->where('fechaeliminacion IS NULL')
                ->countAllResults();

            if ($escuelita === 0) {
                throw new DomainException('La escuelita seleccionada no existe.');
            }
        }
    }

    private static function contarOcupantes(BaseConnection $db, int $idCargo): int
    {
        return $db->table('empleados')
            ->where('IDcargo', $idCargo)
            ->where('fechaeliminacion IS NULL')
            ->countAllResults();
    }

    /**
     * Busca el cargo por nombre; si no existe lo crea.
     * Supuesto: la tabla cargos tiene nombreCargo, IDestado y fechacreacion (como el resto de tablas).
     */
    private static function obtenerOCrearCargo(BaseConnection $db, string $nombre, string $ahora): int
    {
        $fila = $db->query('SELECT IDcargo FROM cargos WHERE TRIM(nombreCargo) = ? LIMIT 1', [$nombre])->getRowArray();

        if ($fila !== null) {
            return (int) $fila['IDcargo'];
        }

        $db->table('cargos')->insert([
            'nombreCargo'   => $nombre,
            'IDestado'      => 1,
            'fechacreacion' => $ahora,
        ]);

        return (int) $db->insertID();
    }
}