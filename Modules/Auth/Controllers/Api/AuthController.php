<?php
namespace Modules\Auth\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use Modules\Auth\Services\AuthService;

/**
 * Controlador REST para autenticação via JWT.
 */
class AuthController extends ResourceController
{
    protected AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    /**
     * POST /api/auth/login
     *
     * Recebe credenciais e retorna um token JWT se válidas.
     */
    public function login()
    {
        $data = $this->request->getJSON(true);
        $token = $this->auth->loginWithJwt($data);
        if ($token === null) {
            return $this->failUnauthorized('Credenciais inválidas');
        }
        return $this->respond(['token' => $token]);
    }
}