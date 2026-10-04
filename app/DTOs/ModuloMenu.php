<?php

declare(strict_types=1);

namespace App\DTOs;

class ModuloMenu
{
    public function __construct(
        private string $titulo,
        private string $descripcion,
        private string $icono,
        private string $ruta,
        private string $colorAcento = 'verde',
        private ?string $etiqueta = null
    ) {}

    public function getTitulo(): string
    {
        return $this->titulo;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getIcono(): string
    {
        return $this->icono;
    }

    public function getUrl(): string
    {
        return base_url($this->ruta);
    }

    public function getColorAcento(): string
    {
        return $this->colorAcento;
    }

    public function getEtiqueta(): ?string
    {
        return $this->etiqueta;
    }
}