<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Entities\UsuarioEntity;
class UsuarioModel extends Model{
    protected $table            = 'usuarios';
    protected $primaryKey       = 'IDusuario';
    protected $returnType       = UsuarioEntity::class;
    protected $useSoftDeletes   = true;
    
    protected $allowedFields    = [
        'nombres',
        'apellidos',
        'usuario',
        'contrasenia',
        'correo',
        'IDnivelAcceso',
        'IDestado'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'fechacreacion';
    protected $updatedField     = 'fechaactualizacion';
    protected $deletedField     = 'fechaeliminacion';
    protected $dateFormat       = 'datetime';

    public static function buscarPorIdentificador( string $identificador): ?UsuarioEntity{
        $instancia = new self();
        return $instancia->groupStart()
               ->where('usuario', $identificador) -> orWhere('correo',$identificador)
               ->groupEnd()
               ->where('IDestado',1)
               ->first();
    }

}