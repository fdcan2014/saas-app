
# Plano do Projeto — v35
_Atualizado em 2025-09-09 13:00 UTC_

## 1) Contexto e estado atual
- **Base recebida:** `saas-app-v29.zip`
- **Entregas realizadas:**
  - **v33**: Módulo **Integrations** + `CryptoService` (AES‑256‑GCM), migrations (`api_keys`, `webhooks`, `integration_credentials`, `notification_templates`, `outbox_messages`), serviços e rotas (`/api/integrations/*`).
  - **v34**: **Worker de webhooks** + **HMAC** (`X-Webhook-Signature`, `X-Webhook-Timestamp`, `X-Webhook-Event`, `X-Webhook-Id`), retries com **backoff exponencial** + jitter, logging em `webhook_deliveries`, comando CLI `integrations:outbox-worker`.
  - **v35**: **Filtros** essenciais:
    - `ApiKeyAuthFilter` (Bearer `v1.api_*` → valida `sha256` com `api_keys`, injeta tenant e escopos).
    - `ScopeFilter` (verifica escopos exigidos por rota).
    - `RateLimitFilter` (Redis → `REDIS_URL`; fallback BD `api_key_rate_limits`).
    - `TenantContextFilter` (opcional, seta tenant via `X-Tenant-Id`).
  - **Migrations novas:** `webhook_deliveries`, `api_key_rate_limits`.

## 2) Variáveis e requisitos
- `.env` obrigatório:
  - `ENCRYPTION_KEY=base64:<32 bytes>` — `openssl rand -base64 32`
- `.env` opcional:
  - `REDIS_URL=redis://:senha@127.0.0.1:6379/0` (habilita rate limit em Redis)
- PHP cURL habilitado para o worker.
- Autoload dos Modules ativo (para configs/filters de `Modules/Core` e `Modules/Integrations`).

## 3) Instalação / upgrade
1. **Atualizar código** para a versão v35.
2. **Migrations:**
   ```bash
   php spark migrate
   ```
3. **Rotas:** garanta carregamento das rotas de `Modules`; o grupo `/api/integrations/*` já vem com `'filter' => 'rate-limit'` (ajuste conforme necessário).
4. **Worker:** executar contínuo ou via cron:
   ```bash
   php spark integrations:outbox-worker          # contínuo
   php spark integrations:outbox-worker --once   # uma passada (cron)
   ```

## 4) Como proteger endpoints com API Key + Escopos + Rate Limit
Exemplo (em routes):
```php
$routes->group('api/public', [
  'filter' => 'api-key,scopes:erp.read,rate-limit'
], static function($routes) {
  $routes->get('ping', 'Api\PublicController::ping');
});
```
- Crie keys em `/api/integrations/api-keys` passando `{ "name": "...", "scopes": ["erp.read","orders.write"] }`.
- Envie `Authorization: Bearer v1.api_<...>` nas chamadas.

## 5) Checklist de validação (v35)
- [ ] `.env` com `ENCRYPTION_KEY` válido (32 bytes).
- [ ] `php spark migrate` executado sem erros.
- [ ] Criada ao menos **1** API key e chamada autenticada ok (200).
- [ ] Rota protegida por `scopes` retorna **403** quando faltar escopo.
- [ ] Rate limit retorna headers `X-RateLimit-*` e **429** ao exceder.
- [ ] Webhook configurado (`webhooks`) com `secret` e entrega validada (HMAC).
- [ ] `webhook_deliveries` populado; `outbox_messages` mudando de `pending` → `delivered`/`failed`.
- [ ] Worker em execução (daemon ou cron).

## 6) Backlog — Próximos passos (propostos)
**Fase 2.2 — Hardening**
1. **ApiKeyAuth**: modo dev `Test <token>` sob `ENVIRONMENT=development`.
2. **Presets de escopo** (ex.: `reporting`, `fulfillment`) mapeando múltiplos escopos.
3. **Rate limit por escopo** (ex.: `webhooks:*` com limites superiores).
4. **Idempotency-Key filter** para POST/PUT (grava `idempotency_keys` com TTL).
5. **Audit filter** — log mínimo de request/response em `api_audit_logs` com política de retenção.
6. **OpenAPI/Swagger** — geração e publicação estática (dev + prod).
7. **Admin UI** (opcional) para Integrations (API keys, webhooks, templates, outbox).

**Fase 3 — Observabilidade & Operação**
1. **Métricas** (entregas por minuto, latência média, taxa de erro) — Prometheus/StatsD.
2. **Alertas** (ex.: falha > X% por 10 min; fila acumulando).
3. **Jobs de limpeza** (`outbox_messages` e `webhook_deliveries` antigas).

**Fase 4 — Segurança avançada**
1. **Rotação de segredos** (webhooks/credentials) e _secret hints_.
2. **Assinatura de resposta** (opcional) e _replay protection_ por timestamp/nonce lado cliente.
3. **Key rolling** (duas chaves ativas por janela de corte).

## 7) Plano de corte / rollback
- **Deploy** em estágio com `migrate --all`. 
- **Smoke test** nos endpoints críticos (`api-keys`, `webhooks`, entrega real).
- **Feature flags** (desativar rate limit se necessário via env/config).
- **Rollback:** reverter pacote e `migrate:rollback` das tabelas novas (`webhook_deliveries`, `api_key_rate_limits`).

## 8) Dicas para “histórico pesado” no chat
- **Limitações:** não consigo **apagar** o histórico desta conversa aqui na plataforma.
- **Solução prática:** 
  1. **Começar um novo chat** e **colar o link para este arquivo** (plano) como _fonte única de verdade_.  
  2. Manter os anexos **apenas do último ZIP** (excluir anexos antigos que não serão mais usados).
  3. Opcional: criar repositório (Git) e abrir **issues** com os itens do backlog (cada fase/feature como issue).
- Posso **gerar as issues** (em Markdown) para colar no seu Git — é só pedir.

## 9) Apêndice — Tabelas criadas/alteradas
- `api_keys (v33)`
- `webhooks (v33)`
- `integration_credentials (v33)`
- `notification_templates (v33)`
- `outbox_messages (v33)`
- `webhook_deliveries (v34)`
- `api_key_rate_limits (v35)`

## 10) Cheatsheet
```bash
# Migrations
php spark migrate

# Worker
php spark integrations:outbox-worker         # contínuo
php spark integrations:outbox-worker --once  # uma passada

# Teste rápido (exemplos)
curl -H "Authorization: Bearer v1.api_..." http://localhost/api/integrations/api-keys
curl -H "Authorization: Bearer v1.api_..." http://localhost/api/integrations/webhooks

# Worker
php spark integrations:outbox-worker --once
```

---
**Próxima decisão**: implementar Fase 2.2 (hardening) agora? Se sim, eu gero `saas-app-full-v36.zip` com os filtros Idempotency + Audit + presets de escopo e documentação OpenAPI básica.
