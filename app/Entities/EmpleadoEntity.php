<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EmpleadoEntity extends Entity
{
    public const ESTADO_ACTIVO   = 1;
    public const ESTADO_INACTIVO = 2;
    protected $attributes = [
        'IDempleado'       => null,
        'cedula'           => null,
        'nombresApellidos' => null,
        'fechaNacimiento'  => null,
        'fechaIngreso'     => null,
        'IDempleador'      => null,
        'IDcargo'          => null,
        'IDzona'           => null,
        'IDestado'         => null,
        // Atributos hidratados por los alias del JOIN
        'nombre_empleador' => null,
        'nombre_cargo'     => null,
        'nombre_zona'      => null,
        'nombre_estado'    => null,
    ];

    public function getId(): int
    {
        return (int) ($this->attributes['IDempleado'] ?? 0);
    }

    public function getCedula(): string
    {
        return (string) ($this->attributes['cedula'] ?? '');
    }

    public function getNombreCompleto(): string
    {
        return (string) ($this->attributes['nombresApellidos'] ?? '');
    }

    public function getEmpleador(): string
    {
        return (string) ($this->attributes['nombre_empleador'] ?? 'N/A');
    }

    public function getCargo(): string
    {
        return (string) ($this->attributes['nombre_cargo'] ?? 'N/A');
    }

    public function getZona(): string
    {
        return (string) ($this->attributes['nombre_zona'] ?? 'Sin Zona');
    }

    public function getZonaClase(): string
    {
        // Un color por zona: zona-0 ... zona-7 (se definen en el CSS)
        return 'zona-' . (((int) ($this->attributes['IDzona'] ?? 0)) % 8);
    }

    public function getEstado(): string
    {
        return (string) ($this->attributes['nombre_estado'] ?? 'Activo');
    }

    public function getFechaIngresoFormateada(): string
    {
        if (empty($this->attributes['fechaIngreso'])) {
            return 'N/A';
        }
        return date('d/m/Y', strtotime((string)$this->attributes['fechaIngreso']));
    }

    public function getFechaNacimientoFormateada(): string
    {
        $fecha = $this->attributes['fechaNacimiento'] ?? null;

        // Evita mostrar fechas vacías o inválidas (como 00/01/1900 o 0000-00-00)
        if (empty($fecha) || str_starts_with((string) $fecha, '0000') || str_starts_with((string) $fecha, '1900')) {
            return 'N/A';
        }
        return date('d/m/Y', strtotime((string) $fecha));
    }

    public function getAniosTrabajados(): float
    {
        $fecha = $this->attributes['fechaIngreso'] ?? null;

        if (empty($fecha) || str_starts_with((string) $fecha, '0000')) {
            return 0.0;
        }
        $ingreso = new \DateTime((string) $fecha);
        $hoy     = new \DateTime('today');

        // Si la fecha de ingreso es futura, no hay años trabajados
        if ($ingreso > $hoy) {
            return 0.0;
        }
        // Mismo cálculo del Excel: días transcurridos / 360
        $dias = (int) $ingreso->diff($hoy)->days;
        return round($dias / 360, 2);
    }
        public function getIdEstado(): int
    {
        return (int) ($this->attributes['IDestado'] ?? 0);
    }

    public function estaActivo(): bool
    {
        return $this->getIdEstado() === self::ESTADO_ACTIVO;
    }

    public function getFoto(): ?string
    {
        $foto = $this->attributes['foto'] ?? null;
        return ($foto === null || $foto === '') ? null : (string) $foto;
    }

    public function getFotoUrl(): ?string
    {
        $foto = $this->getFoto();
        return $foto === null ? null : base_url('uploads/empleados/' . $foto);
    }

    public function getIniciales(): string
    {
        $partes   = preg_split('/\s+/', trim($this->getNombreCompleto())) ?: [];
        $iniciales = '';
        foreach (array_slice($partes, 0, 2) as $parte) {
            $iniciales .= mb_substr($parte, 0, 1);
        }
        return mb_strtoupper($iniciales);
    }
}