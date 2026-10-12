<?php

declare(strict_types=1);

namespace App\Models;

class CargoModel extends CatalogoModel
{
    protected $table            = 'cargos';
    protected $primaryKey       = 'IDcargo';
    protected string $campoNombre = 'nombreCargo';
}