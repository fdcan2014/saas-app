
# Contribuindo

Obrigado por contribuir! Este guia descreve como propor mudanças com segurança.

## Fluxo de trabalho
1. Crie um **fork** ou **branch**: `feature/<resumo>` ou `fix/<resumo>`.
2. Desenvolva em pequenas _commits_ (sugestão: **Conventional Commits**).
3. Garanta que o projeto sobe em dev e que as migrations rodam.
4. Abra um **Pull Request** com descrição clara do que mudou e de **como testar**.

## Padrões de código
- PHP 8.1+, padrão **PSR‑12**.
- Evite **breaking changes** sem discutir antes.
- Nomeie migrations com prefixo de data CI4 (YYYY‑MM‑DD‑NNNNNN_Descricao.php).

## Migrations
- Inclua **índices** para colunas de filtro (`tenant_id`, `status`, `event`, etc.).
- Inclua `down()` correto para permitir rollback.
- Escreva migrações **idempotentes** (use `createTable(..., true)` para evitar falhas se já existir).

## Segurança
- Nunca exponha **segredos/keys** em logs ou commits.
- Use `CryptoService` para segredos sensíveis (webhooks, credentials).
- Revise endpoints: exigem `api-key`? Precisam de `scopes`? Possuem `rate-limit`?

## Documentação
- Atualize o **README.md** e **docs/plano-projeto-*.md** quando necessário.
- Se criar endpoints, atualize o **OpenAPI** quando disponível.

## Testes manuais rápidos
```bash
# Migrations
php spark migrate

# Endpoints principais
curl -H "Authorization: Bearer v1.api_..." http://localhost/api/integrations/api-keys
curl -H "Authorization: Bearer v1.api_..." http://localhost/api/integrations/webhooks

# Worker
php spark integrations:outbox-worker --once
```

## PR Checklist
- [ ] Código compila e executa sem falhas.
- [ ] Migrations criadas e testadas (up/down).
- [ ] Segurança revisada (api-key, scopes, rate-limit quando aplicável).
- [ ] Documentação atualizada.
- [ ] Impacto em produção avaliado (rollout/rollback).

## Versionamento & Releases
- Use changelog claro no PR.
- Após merge, crie tag de **Release** (ex.: `v36`) com artefatos (ZIP se necessário).
