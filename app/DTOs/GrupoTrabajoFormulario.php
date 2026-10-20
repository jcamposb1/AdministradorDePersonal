<?php

declare(strict_types=1);

namespace App\DTOs;

final class GrupoTrabajoFormulario
{
  
    private function __construct(
        private readonly int $IDgrupoTrabajo,
        private readonly int $IDarea,
        private readonly ?int $IDsubarea,
        private readonly ?int $IDescuelita,
        private readonly string $letra,
        private readonly array $puestos,
    ) {
    }

    public static function desdeArreglo(array $datos): self
    {
        $puestos = [];
        foreach (($datos['puestos'] ?? []) as $puesto) {
            if (!is_array($puesto)) {
                continue;
            }
            $cargo = trim((string) ($puesto['cargo'] ?? ''));
            if ($cargo === '') {
                continue; // filas en blanco se ignoran
            }
            $puestos[] = [
                'IDgrupoPuesto' => (int) ($puesto['IDgrupoPuesto'] ?? 0),
                'cargo'         => $cargo,
            ];
        }

        $subarea   = (int) ($datos['IDsubarea'] ?? 0);
        $escuelita = (int) ($datos['IDescuelita'] ?? 0);

        return new self(
            (int) ($datos['IDgrupoTrabajo'] ?? 0),
            (int) ($datos['IDarea'] ?? 0),
            $subarea > 0 ? $subarea : null,
            $escuelita > 0 ? $escuelita : null,
            mb_strtoupper(trim((string) ($datos['letra'] ?? ''))),
            $puestos
        );
    }

    public function validar(): array
    {
        $errores = [];

        if ($this->IDarea <= 0) {
            $errores[] = 'Selecciona el área.';
        }

        if ($this->letra === '') {
            $errores[] = 'Escribe la letra de la mesa.';
        } elseif (mb_strlen($this->letra) > 5) {
            $errores[] = 'La letra admite máximo 5 caracteres.';
        }

        if ($this->puestos === []) {
            $errores[] = 'Agrega al menos un puesto con su cargo.';
        }

        foreach ($this->puestos as $puesto) {
            if (mb_strlen($puesto['cargo']) > 100) {
                $errores[] = 'El cargo «' . mb_substr($puesto['cargo'], 0, 30) . '…» supera los 100 caracteres.';
                break;
            }
        }

        return $errores;
    }

    public function esNuevo(): bool
    {
        return $this->IDgrupoTrabajo <= 0;
    }

    public function getIDgrupoTrabajo(): int
    {
        return $this->IDgrupoTrabajo;
    }

    public function getIDarea(): int
    {
        return $this->IDarea;
    }

    public function getIDsubarea(): ?int
    {
        return $this->IDsubarea;
    }

    public function getIDescuelita(): ?int
    {
        return $this->IDescuelita;
    }

    public function getLetra(): string
    {
        return $this->letra;
    }


    public function getPuestos(): array
    {
        return $this->puestos;
    }
}