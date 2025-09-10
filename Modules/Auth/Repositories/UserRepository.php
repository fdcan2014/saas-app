<?php
namespace Modules\Auth\Repositories;

use Modules\Auth\Entities\UserEntity;

/**
 * Repositório responsável por recuperar e persistir usuários no banco de dados.
 *
 * Em um ambiente real você implementaria métodos usando o Query Builder ou
 * Eloquent/Model do CodeIgniter.  Aqui fornecemos apenas assinaturas.
 */
class UserRepository
{
    /**
     * Instância de conexão com o banco de dados.
     *
     * @var \CodeIgniter\Database\ConnectionInterface
     */
    protected $db;

    public function __construct()
    {
        // Obtém a conexão padrão através do helper global do CI4.  Em um
        // projeto real é preferível injetar a conexão via construtor.
        $this->db = \Config\Database::connect();
    }

    /**
     * Busca um usuário por e‑mail e tenant.
     *
     * Este método retorna uma instância de UserEntity preenchida com os
     * dados básicos do usuário (id, email, nome, tenant_id) se existir
     * um perfil associado ao tenant informado.  Caso contrário retorna
     * null.
     */
    public function findByEmail(string $email, int $tenantId): ?UserEntity
    {
        // Primeiro localiza o usuário na tabela `users` (do Shield)
        $builder = $this->db->table('users');
        $builder->select('users.id as user_id, users.email, users.password_hash, profiles.id as profile_id, profiles.display_name, profiles.tenant_id');
        $builder->join('profiles', 'profiles.user_id = users.id', 'left');
        $builder->where('users.email', $email);
        $builder->where('profiles.tenant_id', $tenantId);
        $row = $builder->get()->getRow();
        if (! $row) {
            return null;
        }
        return new UserEntity([
            'id'          => (int) $row->user_id,
            'profile_id'  => (int) ($row->profile_id ?? 0),
            'tenant_id'   => (int) $row->tenant_id,
            'email'       => $row->email,
            'password_hash' => $row->password_hash,
            'name'        => $row->display_name,
        ]);
    }

    /**
     * Persistir um novo usuário ou atualizar um existente.
     *
     * Este método cria ou atualiza um usuário na tabela `users` e
     * opcionalmente cria/atualiza o perfil correspondente na tabela
     * `profiles`.  Para novos usuários, é necessário informar
     * `password_hash` previamente calculado.
     */
    public function save(UserEntity $user): bool
    {
        // Persistir na tabela users
        $builder = $this->db->table('users');
        // Verifica se o usuário já existe (id > 0)
        if (isset($user->id) && $user->id > 0) {
            $builder->where('id', $user->id)->update([
                'email'         => $user->email,
                'password_hash' => $user->password_hash,
            ]);
            $userId = $user->id;
        } else {
            $builder->insert([
                'email'         => $user->email,
                'password_hash' => $user->password_hash,
                'username'      => $user->email,
                'status'        => 'active',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
            $userId = $this->db->insertID();
            $user->id = $userId;
        }
        // Verifica se existe perfil para o tenant
        $profileBuilder = $this->db->table('profiles');
        $profile = $profileBuilder
            ->where('user_id', $userId)
            ->where('tenant_id', $user->tenant_id)
            ->get()->getRow();
        if ($profile) {
            // Atualiza display_name
            $profileBuilder
                ->where('id', $profile->id)
                ->update(['display_name' => $user->name]);
        } else {
            // Cria novo perfil
            $profileBuilder->insert([
                'user_id'      => $userId,
                'tenant_id'    => $user->tenant_id,
                'display_name' => $user->name,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
        return true;
    }

    /**
     * Recupera o perfil de um usuário para um determinado tenant.
     *
     * Retorna uma instância de UserEntity com dados do perfil (nome,
     * tenant_id, email, password_hash) se existir.  Caso o perfil
     * inexistente, retorna null.
     */
    public function findProfile(int $userId, int $tenantId): ?UserEntity
    {
        // Consulta combinando `users` e `profiles` para obter os dados
        $builder = $this->db->table('users');
        $builder->select('users.id as user_id, users.email, users.password_hash, profiles.id as profile_id, profiles.display_name, profiles.tenant_id');
        $builder->join('profiles', 'profiles.user_id = users.id');
        $builder->where('users.id', $userId);
        $builder->where('profiles.tenant_id', $tenantId);
        $row = $builder->get()->getRow();
        if (! $row) {
            return null;
        }
        return new UserEntity([
            'id'          => (int) $row->user_id,
            'profile_id'  => (int) ($row->profile_id ?? 0),
            'tenant_id'   => (int) $row->tenant_id,
            'email'       => $row->email,
            'password_hash' => $row->password_hash,
            'name'        => $row->display_name,
        ]);
    }

    /**
     * Lista todos os perfis (usuários) de um tenant com seus dados básicos.
     *
     * Retorna um array de arrays contendo profile_id, user_id, email e name.
     */
    public function listProfiles(int $tenantId): array
    {
        $builder = $this->db->table('profiles');
        $builder->select('profiles.id as profile_id, users.id as user_id, users.email, profiles.display_name');
        $builder->join('users', 'users.id = profiles.user_id');
        $builder->where('profiles.tenant_id', $tenantId);
        $rows = $builder->get()->getResult();
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'profile_id' => (int) $row->profile_id,
                'user_id'    => (int) $row->user_id,
                'email'      => $row->email,
                'name'       => $row->display_name,
            ];
        }
        return $result;
    }

    /**
     * Recupera um perfil pelo ID.  Retorna os dados do perfil e do usuário.
     */
    public function findProfileById(int $profileId): ?array
    {
        $builder = $this->db->table('profiles');
        $builder->select('profiles.id as profile_id, profiles.tenant_id, users.id as user_id, users.email, users.password_hash, profiles.display_name');
        $builder->join('users', 'users.id = profiles.user_id');
        $builder->where('profiles.id', $profileId);
        $row = $builder->get()->getRow();
        if (! $row) {
            return null;
        }
        return [
            'profile_id'    => (int) $row->profile_id,
            'tenant_id'     => (int) $row->tenant_id,
            'user_id'       => (int) $row->user_id,
            'email'         => $row->email,
            'password_hash' => $row->password_hash,
            'display_name'  => $row->display_name,
        ];
    }
}