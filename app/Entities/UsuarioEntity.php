<?php

declare(strict_types=1);

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class UsuarioEntity extends Entity
{
    protected $attributes = [
        'IDusuario'     => null,
        'nombres'       => null,
        'apellidos'     => null,
        'usuario'       => null,
        'contrasenia'   => null,
        'correo'        => null,
        'IDnivelAcceso' => null,
        'IDestado'      => null,
    ];

    public function getIdUsuario(): int
    {
        return (int) $this->attributes['IDusuario'];
    }

    public function getNombreCompleto(): string
    {
        return trim(($this->attributes['nombres'] ?? '') . ' ' . ($this->attributes['apellidos'] ?? ''));
    }

    public function getUsuario(): string
    {
        return (string) $this->attributes['usuario'];
    }

    public function getCorreo(): string
    {
        return (string) ($this->attributes['correo'] ?? '');
    }

    public function getNivelAcceso(): int
    {
        return (int) $this->attributes['IDnivelAcceso'];
    }

    public function passwordCoincide(string $password): bool
    {
        return password_verify($password, (string) $this->attributes['contrasenia']);
    }
}