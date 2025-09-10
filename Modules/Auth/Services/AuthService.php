<?php
namespace Modules\Auth\Services;

use Modules\Auth\Repositories\UserRepository;
use Modules\Auth\Entities\UserEntity;

/**
 * AuthService centraliza a lógica de autenticação.
 *
 * Este serviço fornece métodos para login baseado em sessão (para uso no
 * painel administrativo) e login baseado em JWT (para a API/PDV).  Ele
 * encapsula o mecanismo de autenticação (ex.: Shield) e expõe uma API
 * uniforme para os demais módulos consumirem.
 */
class AuthService
{
    /**
     * Repositório de usuários/perfis.
     *
     * @var UserRepository
     */
    protected UserRepository $users;

    /**
     * Chave secreta utilizada para assinar e verificar os tokens JWT.
     * Em um projeto real, esta chave deve ser armazenada em um local
     * seguro (env, vault, etc.).
     */
    protected string $jwtSecret;

    public function __construct(UserRepository $users)
    {
        $this->users     = $users;
        $this->jwtSecret = getenv('JWT_SECRET') ?: 'change-this-secret';
    }

    /**
     * Realiza login de usuário utilizando sessão.  Este método deve
     * delegar para a biblioteca de autenticação da framework (ex.: Shield)
     * para validar as credenciais e criar a sessão apropriada.
     */
    public function loginWithSession(array $credentials): bool
    {
        // Usa o serviço de autenticação do Shield para efetuar login baseado
        // em sessão.  O array $credentials deve conter "email" (ou outra
        // identidade configurada) e "password".
        $auth = service('authentication');
        return $auth->attempt($credentials);
    }

    /**
     * Gera um token JWT para uso em APIs.  Valida as credenciais do
     * usuário, gera um token assinado e retorna o token como string.
     */
    public function loginWithJwt(array $credentials): ?string
    {
        // Extração de credenciais básicas
        $email    = $credentials['email']   ?? null;
        $password = $credentials['password']?? null;
        $tenantId = $credentials['tenantId']?? null;
        if (! $email || ! $password || ! $tenantId) {
            return null;
        }
        // Recupera o usuário via provedor do Shield
        $auth = service('authentication');
        $provider = $auth->getProvider();
        if (! method_exists($provider, 'getIdByEmail')) {
            // Provedor padrão do Shield possui este método; se não existir, aborta
            return null;
        }
        $userId = $provider->getIdByEmail($email);
        if (! $userId) {
            return null;
        }
        $user = $provider->findById($userId);
        if (! $user) {
            return null;
        }
        // Validação da senha
        if (! password_verify($password, $user->password_hash)) {
            return null;
        }
        // Recupera o perfil para o tenant
        $profile = $this->users->findProfile($user->id, (int) $tenantId);
        if (! $profile) {
            return null;
        }
        // Monta as claims do JWT
        $now      = time();
        // Obtém o serviço de autorização do container.  No CodeIgniter 4,
        // pode ser registrado em app/Config/Services.php.  Aqui usamos
        // service('authorization') como exemplo.
        $authorization = service('authorization');
        $roles    = method_exists($authorization, 'roles') ? $authorization->roles($user->id, (int) $tenantId) : [];
        $payload  = [
            'iss'  => base_url() ?? 'http://localhost',
            'sub'  => $user->id,
            'tid'  => (int) $tenantId,
            'roles'=> $roles,
            'iat'  => $now,
            'exp'  => $now + 3600, // 1 hora de validade
        ];
        // Gera o token usando a biblioteca firebase/php-jwt
        try {
            // Verifica se a classe JWT está disponível
            if (! class_exists('\Firebase\JWT\JWT')) {
                // Biblioteca não instalada
                return null;
            }
            return \Firebase\JWT\JWT::encode($payload, $this->jwtSecret, 'HS256');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Valida o token JWT recebido e retorna o contexto do usuário se
     * válido.  O contexto deve incluir o usuário autenticado e o
     * identificador do tenant.
     */
    public function validateJwt(string $token): ?UserEntity
    {
        // Verifica se a biblioteca JWT está disponível
        if (! class_exists('\Firebase\JWT\JWT')) {
            return null;
        }
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($this->jwtSecret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
        // Verifica expiração (a biblioteca já lança exceção se expirado)
        $userId  = $decoded->sub ?? null;
        $tenantId= $decoded->tid ?? null;
        if (! $userId || ! $tenantId) {
            return null;
        }
        return $this->users->findProfile((int) $userId, (int) $tenantId);
    }

    /**
     * Retorna o contexto de usuário já autenticado para um determinado
     * tenant.  Isto permite que outros módulos consultem as permissões e
     * dados do usuário autenticado.
     */
    public function getUserContext(int $tenantId): ?UserEntity
    {
        // Obtém o usuário autenticado via Shield
        $auth = service('authentication');
        $user = $auth->user();
        if (! $user) {
            return null;
        }
        // Recupera o perfil correspondente ao tenant
        $profile = $this->users->findProfile($user->id, $tenantId);
        return $profile;
    }
}