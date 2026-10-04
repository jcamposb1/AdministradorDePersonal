<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmpleadoModel;

class PersonalController extends BaseController
{
    public function index()
    {
        $auth = $this->verificarSesion();
        if ($auth !== null) {
            return $auth;
        }

        $empleados = EmpleadoModel::obtenerListadoCompleto();

        return view('personal/index', [
            'titulo'    => 'Base de Personal - Supra Live',
            'empleados' => $empleados
        ]);
    }
}