<?php

declare(strict_types=1);

namespace App\Models;

class EmpleadorModel extends CatalogoModel
{
    protected $table            = 'empleadores';
    protected $primaryKey       = 'IDempleador';
    protected string $campoNombre = 'nombreEmpleador';
}