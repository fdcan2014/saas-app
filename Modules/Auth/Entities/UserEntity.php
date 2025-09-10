<?php
namespace Modules\Auth\Entities;

/**
 * Representação de um usuário no contexto do módulo de autenticação.
 *
 * No CodeIgniter 4, entidades são objetos simples que encapsulam
 * propriedades do modelo e fornecem getters/setters.  Esta classe
 * é apenas um exemplo simplificado.
 */
class UserEntity
{
    /**
     * Identificador do usuário (referência à tabela `users` do Shield).
     */
    public int $id;

    /**
     * Identificador do perfil (referência à tabela `profiles`).  Este
     * valor é importante para consultas de roles e permissões.
     */
    public int $profile_id;
    public int $tenant_id;
    public string $email;
    public string $password_hash;
    public ?string $name = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}