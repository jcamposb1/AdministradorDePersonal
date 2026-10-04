<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class EmpleadoEntity extends Entity
{
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
        return (string) ($this->attributes['nombre_zona'] ?? 'N/A');
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
        return date('d/m/Y', strtotime((string) $this->attributes['fechaIngreso']));
    }
}