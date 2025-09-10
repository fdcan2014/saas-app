# Módulo Auth

Este módulo concentra a lógica de autenticação e autorização do sistema.
Ele foi desenhado para ser híbrido, fornecendo login via sessão (para o
painel administrativo) e login via JWT (para a API/PDV).  Além disso, ele
é multi‑tenant, ou seja, cada usuário pertence a uma loja (`tenant_id`) e
possui permissões/roles específicas.

## Estrutura

* **Config** – arquivos de configuração e rotas exclusivas do módulo.
* **Controllers** – controladores Web e API para login/logout/refresh.
* **Database** – migrations e seeds para criar tabelas de usuários,
  identidades, tokens, roles e permissões.
* **Entities** – representações das entidades de domínio (User).
* **Repositories** – classes responsáveis por abstrair o acesso ao banco.
* **Services** – camada de serviço que encapsula a lógica de autenticação
  (`AuthService`) e a verificação de permissões (`AuthorizationService`).
* **Views** – páginas de login e recuperação de senha (quando não via SPA).

## Próximos Passos

* Implementar as migrations completas para todas as tabelas de autenticação.
* Finalizar a lógica do `AuthService` (sessão + JWT) conforme descrito no
  blueprint.
* Adicionar políticas de permissão/role multi‑tenant.  O serviço
  `AuthorizationService` incluído serve como ponto de partida para
  centralizar checagens de autorização.
* Criar testes de unidade e integração para as funções críticas.