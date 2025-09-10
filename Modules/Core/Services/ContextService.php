<?php
namespace Modules\Core\Services;

use Modules\Tenant\Entities\TenantEntity;
use Modules\Auth\Entities\UserEntity;

/**
 * ContextService armazena o contexto da requisição atual, como o
 * tenant (loja) resolvido e o usuário autenticado.  Ele fornece
 * métodos estáticos para definir e recuperar esses valores de forma
 * global durante o ciclo de vida de uma requisição HTTP.
 *
 * Em aplicações reais, pode-se usar contextos ligados à requisição
 * (por exemplo, injetados via middleware) ou serviços singleton.  Este
 * serviço simplificado usa propriedades estáticas para demonstrar o
 * conceito.
 */
class ContextService
{
    /**
     * @var TenantEntity|null
     */
    protected static ?TenantEntity $tenant = null;

    /**
     * @var UserEntity|null
     */
    protected static ?UserEntity $user = null;

    /**
     * Define o tenant atual.
     */
    public static function setTenant(?TenantEntity $tenant): void
    {
        self::$tenant = $tenant;
    }

    /**
     * Retorna o tenant atual.
     */
    public static function getTenant(): ?TenantEntity
    {
        return self::$tenant;
    }

    /**
     * Retorna o identificador do tenant atual (ou null se indefinido).
     */
    public static function getTenantId(): ?int
    {
        return self::$tenant?->id;
    }

    /**
     * Define o usuário atual.
     */
    public static function setUser(?UserEntity $user): void
    {
        self::$user = $user;
    }

    /**
     * Retorna o usuário atual.
     */
    public static function getUser(): ?UserEntity
    {
        return self::$user;
    }

    /**
     * Limpa o contexto da requisição (tenant e usuário).
     */
    public static function clear(): void
    {
        self::$tenant = null;
        self::$user   = null;
    }
}