<?php

declare(strict_types=1);

namespace App\Models;

class EstadoModel extends CatalogoModel
{
    protected $table            = 'estados';
    protected $primaryKey       = 'IDestado';
    protected string $campoNombre = 'nombreEstado';
}