<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Entities\EmpleadoEntity;
use App\Models\EmpleadoModel;
use CodeIgniter\HTTP\ResponseInterface;

class PersonalController extends BaseController
{
    private const CARPETA_FOTOS = 'uploads/empleados';

    public function index()
    {
        $auth = $this->verificarSesion();
        if ($auth !== null) {
            return $auth;
        }

        return view('personal/index', [
            'titulo'    => 'Base de Personal - Supra Live',
            'empleados' => EmpleadoModel::obtenerListadoCompleto(),
            'catalogos' => EmpleadoModel::obtenerCatalogos(),
        ]);
    }

    public function guardar(): ResponseInterface
    {
        if (!$this->peticionValida()) {
            return $this->responder(403, 'error', 'Petición no permitida.');
        }

        $datos = [
            'cedula'           => EmpleadoModel::normalizarCedula((string) $this->request->getPost('cedula')),
            'nombresApellidos' => mb_strtoupper(trim((string) $this->request->getPost('nombresApellidos'))),
            'fechaNacimiento'  => trim((string) $this->request->getPost('fechaNacimiento')),
            'fechaIngreso'     => trim((string) $this->request->getPost('fechaIngreso')),
            'IDempleador'      => (int) $this->request->getPost('IDempleador'),
            'IDcargo'          => (int) $this->request->getPost('IDcargo'),
            'IDzona'           => (int) $this->request->getPost('IDzona'),
        ];

        $reglas = [
            'cedula'           => 'required|regex_match[/^\d{10}$/]|is_unique[empleados.cedula]',
            'nombresApellidos' => 'required|min_length[5]|max_length[150]',
            'fechaNacimiento'  => 'permit_empty|valid_date[Y-m-d]',
            'fechaIngreso'     => 'required|valid_date[Y-m-d]',
            'IDempleador'      => 'is_natural_no_zero',
            'IDcargo'          => 'is_natural_no_zero',
            'IDzona'           => 'is_natural_no_zero',
        ];

        if (!$this->validateData($datos, $reglas, $this->mensajesValidacion())) {
            return $this->responder(422, 'error', implode(' ', $this->validator->getErrors()));
        }

        // Foto opcional
        $nombreFoto = null;
        $foto       = $this->request->getFile('foto');

        if ($foto !== null && $foto->getError() !== UPLOAD_ERR_NO_FILE) {
            $tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp'];

            if (!$foto->isValid()
                || !in_array($foto->getMimeType(), $tiposPermitidos, true)
                || $foto->getSize() > 2 * 1024 * 1024) {
                return $this->responder(422, 'error', 'La foto debe ser JPG, PNG o WEBP de máximo 2 MB.');
            }

            $nombreFoto = $foto->getRandomName();
            $foto->move(FCPATH . self::CARPETA_FOTOS, $nombreFoto);
        }

        $datos['fechaNacimiento'] = $datos['fechaNacimiento'] !== '' ? $datos['fechaNacimiento'] : null;
        $datos['IDestado']        = EmpleadoEntity::ESTADO_ACTIVO;
        $datos['foto']            = $nombreFoto;

        if (EmpleadoModel::registrar($datos) === false) {
            $this->borrarFoto($nombreFoto);
            return $this->responder(500, 'error', 'No se pudo guardar el empleado. Intenta de nuevo.');
        }

        return $this->responder(201, 'success', 'Empleado registrado correctamente.');
    }

    public function cambiarEstado(int $id): ResponseInterface
    {
        if (!$this->peticionValida()) {
            return $this->responder(403, 'error', 'Petición no permitida.');
        }

        $nuevo = EmpleadoModel::alternarEstado($id);

        if ($nuevo === null) {
            return $this->responder(404, 'error', 'El empleado no existe.');
        }

        $activo = $nuevo['id'] === EmpleadoEntity::ESTADO_ACTIVO;

        return $this->responder(200, 'success', $activo ? 'Empleado activado.' : 'Empleado inactivado.', [
            'activo' => $activo,
            'estado' => $nuevo['nombre'],
        ]);
    }

    public function eliminar(int $id): ResponseInterface
    {
        if (!$this->peticionValida()) {
            return $this->responder(403, 'error', 'Petición no permitida.');
        }

        $empleado = EmpleadoModel::buscarPorId($id);

        if ($empleado === null) {
            return $this->responder(404, 'error', 'El empleado no existe.');
        }

        if (!EmpleadoModel::eliminarPorId($id)) {
            return $this->responder(409, 'error', 'No se pudo eliminar: tiene registros relacionados. Mejor inactívalo.');
        }

        $this->borrarFoto($empleado->getFoto());

        return $this->responder(200, 'success', 'Empleado eliminado.');
    }

    // ---------- Auxiliares ----------

    private function peticionValida(): bool
    {
        return $this->request->isAJAX() && session()->get('estaLogueado') === true;
    }

    private function responder(int $codigo, string $status, string $mensaje, array $extra = []): ResponseInterface
    {
        return $this->response
            ->setStatusCode($codigo)
            ->setJSON(array_merge(['status' => $status, 'message' => $mensaje], $extra));
    }

    private function borrarFoto(?string $nombre): void
    {
        if ($nombre === null || $nombre === '') {
            return;
        }

        $ruta = FCPATH . self::CARPETA_FOTOS . '/' . basename($nombre);
        if (is_file($ruta)) {
            unlink($ruta);
        }
    }

    private function mensajesValidacion(): array
    {
        return [
            'cedula' => [
                'required'    => 'La cédula es obligatoria.',
                'regex_match' => 'La cédula debe tener 10 dígitos numéricos.',
                'is_unique'   => 'Ya existe un empleado con esa cédula.',
            ],
            'nombresApellidos' => [
                'required'   => 'Los nombres y apellidos son obligatorios.',
                'min_length' => 'Escribe los nombres y apellidos completos.',
            ],
            'fechaNacimiento' => ['valid_date' => 'La fecha de nacimiento no es válida.'],
            'fechaIngreso'    => [
                'required'   => 'La fecha de ingreso es obligatoria.',
                'valid_date' => 'La fecha de ingreso no es válida.',
            ],
            'IDempleador' => ['is_natural_no_zero' => 'Selecciona un empleador.'],
            'IDcargo'     => ['is_natural_no_zero' => 'Selecciona un cargo.'],
            'IDzona'      => ['is_natural_no_zero' => 'Selecciona una zona.'],
        ];
    }
}