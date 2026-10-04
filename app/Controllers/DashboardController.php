<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\ModuloMenu;

class DashboardController extends BaseController
{
    public function index()
    {
        $redireccion = $this->verificarSesion();
        if ($redireccion !== null) {
            return $redireccion;
        }

        $modulos = [
            new ModuloMenu(
                'Base de Personal',
                'Ficha maestra, legajos y datos contractuales de trabajadores',
                'bi-people-fill',
                'personal',
                'verde',
                'Principal'
            ),
            new ModuloMenu(
                'Listado Grupo Trabajo',
                'Estructuración de cuadrillas operativas y frentes de labor',
                'bi-diagram-3-fill',
                'grupos-trabajo',
                'verde'
            ),
            new ModuloMenu(
                'Listado de Turnos',
                'Planificación horaria: turnos diurnos, vespertinos y rotativos',
                'bi-clock-history',
                'turnos',
                'naranja'
            ),
            new ModuloMenu(
                'Resultados',
                'Métricas operativas de rendimiento e indicadores de gestión',
                'bi-bar-chart-line-fill',
                'resultados',
                'naranja',
                'Reportes'
            ),
            new ModuloMenu(
                'Día de Cumpleañeros',
                'Monitoreo mensual de natalicios y bienestar laboral',
                'bi-cake2-fill',
                'cumpleaneros',
                'amarillo'
            ),
            new ModuloMenu(
                'Distribución de Personal',
                'Asignación dinámica de puestos en planta y áreas de campo',
                'bi-grid-3x3-gap-fill',
                'distribucion',
                'verde'
            ),
            new ModuloMenu(
                'Días Laborables',
                'Calendario anual de operaciones, descansos y días festivos',
                'bi-calendar-check-fill',
                'dias-laborables',
                'naranja'
            ),
        ];

        return view('dashboard/index', [
            'titulo'  => 'Panel de Control - Supra Live',
            'modulos' => $modulos,
            'usuario' => $this->session->get('nombre') ?? 'Administrador'
        ]);
    }
}