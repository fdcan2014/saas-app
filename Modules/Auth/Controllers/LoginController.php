<?php
namespace Modules\Auth\Controllers;

use CodeIgniter\Controller;
use Modules\Auth\Services\AuthService;

/**
 * Controlador responsável pelo login via sessão (painel/admin).
 *
 * Este exemplo simplificado mostra como injetar o serviço de autenticação
 * e delegar o processo de login.  Em um projeto real você lidará com
 * requisições HTTP, validação e redirecionamentos.
 */
class LoginController extends Controller
{
    protected AuthService $auth;

    public function __construct(AuthService $auth)
    {
        $this->auth = $auth;
    }

    public function index()
    {
        // Renderiza a view de login
        return view('Auth/login');
    }

    public function login()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $tenantId = (int) $this->request->getPost('tenant_id');

        $ok = $this->auth->loginWithSession([
            'email'    => $email,
            'password' => $password,
            'tenantId' => $tenantId,
        ]);

        if ($ok) {
            // TODO: redirecionar para o painel
        }

        // TODO: lidar com erros de autenticação
    }
}