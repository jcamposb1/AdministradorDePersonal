<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UsuarioModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    public function login()
    {
        if (session()->get('estaLogueado')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login', [
            'titulo' => 'Ingreso al Sistema - Gestión Sostenible'
        ]);
    }

    public function autenticar(): ResponseInterface
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Petición no permitida.'
            ]);
        }

        $identificador = trim((string) $this->request->getPost('usuario'));
        $password      = (string) $this->request->getPost('password');

        if ($identificador === '' || $password === '') {
            return $this->response->setStatusCode(422)->setJSON([
                'status'  => 'error',
                'message' => 'Por favor complete todos los campos.'
            ]);
        }

        // Búsqueda orientada a objetos (acepta usuario o correo y solo activos)
        $usuario = UsuarioModel::buscarPorIdentificador($identificador);

        // Validación delegada al método de la entidad
        if ($usuario === null || !$usuario->passwordCoincide($password)) {
            return $this->response->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Usuario o contraseña incorrectos.'
            ]);
        }

        // Encapsulamiento: obtenemos los datos usando los getters de la entidad
        session()->set([
            'id_usuario'   => $usuario->getIdUsuario(),
            'nombre'       => $usuario->getNombreCompleto(),
            'usuario'      => $usuario->getUsuario(),
            'nivel_acceso' => $usuario->getNivelAcceso(),
            'estaLogueado' => true
        ]);

        return $this->response->setJSON([
            'status'   => 'success',
            'message'  => 'Acceso concedido. Redirigiendo...',
            'redirect' => base_url('dashboard')
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('/'));
    }
}