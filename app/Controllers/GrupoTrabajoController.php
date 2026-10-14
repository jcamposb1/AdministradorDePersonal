<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\GrupoTrabajoModel;

class GrupoTrabajoController extends BaseController
{
    public function MostrarGrupoTrabajo()
    {
        $redireccion = $this->verificarSesion();
        if ($redireccion !== null) {
            return $redireccion;
        }

        $areas = GrupoTrabajoModel::obtenerEstructura();

        return view('grupos_trabajo/mostrar', [
            'titulo'  => 'Listado Grupo Trabajo - Supra Live',
            'usuario' => $this->session->get('nombre') ?? 'Administrador',
            'areas'   => $areas,
            'resumen' => GrupoTrabajoModel::calcularResumen($areas),
        ]);
    }
}