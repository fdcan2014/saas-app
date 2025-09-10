
# Changelog

Todas as alterações são documentadas neste arquivo de changelog, usando o padrão **SemVer** (MAJOR.MINOR.PATCH).

## [v35] - 2025-09-09
### Adicionados:
- **Filtros**:
  - `ApiKeyAuthFilter`: Validação via API Key (`Authorization: Bearer v1.api_*`).
  - `ScopeFilter`: Verificação de escopos exigidos por rota.
  - `RateLimitFilter`: Implementação de rate limit usando **Redis** ou **fallback em BD**.
  - `TenantContextFilter`: Definição do tenant via `X-Tenant-Id` nos headers.
- **Migrations**:
  - `webhook_deliveries` (log de entregas de webhooks).
  - `api_key_rate_limits` (fallback de rate limit no BD).
- **Segurança**:
  - **HMAC** para **webhooks**. Assinatura de payload com header `X-Webhook-Signature`.
- **Desenvolvimento**: Rate limit dinâmico e escopos múltiplos para chaves de API.

## [v34] - 2025-09-08
### Adicionados:
- **Worker de Webhooks**:
  - Implementação do comando `php spark integrations:outbox-worker` para processar entregas de webhooks.
  - Estratégia de **backoff exponencial** com **jitter** para as tentativas de entrega de webhooks.
- **HMAC**: Implementação da assinatura HMAC nas requisições de Webhooks, garantindo integridade e segurança nas entregas.
- **Novas Tabelas**:
  - `webhook_deliveries`: Log de status, resposta e falhas nas entregas de webhooks.

## [v33] - 2025-09-07
### Adicionados:
- **Módulo Integrations**: Criação de módulo para integração com sistemas externos, incluindo **API Keys**, **Webhooks**, **Credentials**, **Notification Templates**, **Outbox Messages**.
- **CryptoService**: Serviço de criptografia para gerenciar segredos e dados sensíveis (ex.: `secret_enc` de webhooks e credentials).
- **Migrations**: 
  - Criação das tabelas: `api_keys`, `webhooks`, `integration_credentials`, `notification_templates`, `outbox_messages`.
- **Filtros**: Primeiros filtros básicos, sem rate limit ou escopos definidos, mas configurados para os endpoints de integração.

