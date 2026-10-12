<?php

declare(strict_types=1);

namespace App\Models;

class ZonaModel extends CatalogoModel
{
    protected $table            = 'zonas';
    protected $primaryKey       = 'IDzona';
    protected string $campoNombre = 'nombreZona';
}