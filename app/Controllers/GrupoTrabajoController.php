<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\GrupoTrabajoFormulario;
use App\Models\GrupoTrabajoModel;
use CodeIgniter\HTTP\ResponseInterface;

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
            'titulo'     => 'Listado Grupo Trabajo - Supra Live',
            'usuario'    => $this->session->get('nombre') ?? 'Administrador',
            'areas'      => $areas,
            'resumen'    => GrupoTrabajoModel::calcularResumen($areas),
            'catalogos'  => GrupoTrabajoModel::obtenerCatalogos(),
        ]);
    }

    public function GuardarGrupoTrabajo(): ResponseInterface
    {
        $sinSesion = $this->exigirSesionJson();
        if ($sinSesion !== null) {
            return $sinSesion;
        }

        $formulario = GrupoTrabajoFormulario::desdeArreglo($this->leerEntrada());
        $errores    = $formulario->validar();

        if ($errores !== []) {
            return $this->responderJson(['ok' => false, 'mensaje' => implode(' ', $errores)], 422);
        }

        $resultado = GrupoTrabajoModel::guardarGrupo($formulario);

        return $this->responderJson($resultado, $resultado['ok'] ? 200 : 422);
    }

    public function EliminarGrupoTrabajo(int $idGrupoTrabajo): ResponseInterface
    {
        $sinSesion = $this->exigirSesionJson();
        if ($sinSesion !== null) {
            return $sinSesion;
        }

        $resultado = GrupoTrabajoModel::eliminarGrupo($idGrupoTrabajo);

        return $this->responderJson($resultado, $resultado['ok'] ? 200 : 422);
    }

    public function MoverPersona(): ResponseInterface
    {
        $sinSesion = $this->exigirSesionJson();
        if ($sinSesion !== null) {
            return $sinSesion;
        }

        $entrada    = $this->leerEntrada();
        $idEmpleado = (int) ($entrada['IDempleado'] ?? 0);
        $idPuesto   = (int) ($entrada['IDgrupoPuestoDestino'] ?? 0);

        if ($idEmpleado <= 0 || $idPuesto <= 0) {
            return $this->responderJson(['ok' => false, 'mensaje' => 'Datos incompletos.'], 422);
        }

        $resultado = GrupoTrabajoModel::moverPersona($idEmpleado, $idPuesto);

        return $this->responderJson($resultado, $resultado['ok'] ? 200 : 422);
    }

    public function BuscarPersonal(): ResponseInterface
    {
        $sinSesion = $this->exigirSesionJson();
        if ($sinSesion !== null) {
            return $sinSesion;
        }

        $texto = (string) $this->request->getGet('q');

        return $this->responderJson([
            'ok'         => true,
            'resultados' => GrupoTrabajoModel::buscarPersonal($texto),
        ]);
    }

    private function exigirSesionJson(): ?ResponseInterface
    {
        if ($this->verificarSesion() === null) {
            return null;
        }

        return $this->responderJson([
            'ok'      => false,
            'mensaje' => 'Tu sesión venció. Inicia sesión nuevamente.',
        ], 401);
    }

    private function leerEntrada(): array
    {
        $json = $this->request->getJSON(true);

        return is_array($json) ? $json : [];
    }

    private function responderJson(array $datos, int $codigo = 200): ResponseInterface
    {
        // El token CSRF se regenera en cada petición: se devuelve el nuevo para la siguiente.
        $datos['csrf'] = csrf_hash();

        return $this->response->setStatusCode($codigo)->setJSON($datos);
    }
}