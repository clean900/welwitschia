# Automação com n8n — Welwitschia ERP

> O n8n é um serviço **separado** (Docker) que orquestra workflows. A app Welwitschia
> **emite eventos por webhook**; o n8n recebe-os e executa as automações.

## Como ligar

1. **No servidor** (Proxmox/VPS), subir o n8n:
   ```bash
   docker compose -f docker-compose.n8n.yml up -d
   ```
   Aceder a `https://n8n.welwitschia.ao` (utilizador/password definidos no compose).

2. **Na app** (`.env`), apontar para o n8n:
   ```env
   N8N_WEBHOOK_URL=https://n8n.welwitschia.ao/webhook
   ```
   Vazio = automação desligada (a app funciona na mesma).

3. **No n8n**, criar um workflow com um nó **Webhook** por evento, no caminho
   `/<evento>` (ex.: `/invoice.issued`). A app faz `POST` com o corpo:
   ```json
   { "event": "invoice.issued", "tenant": "acme", "data": { ... } }
   ```

## Eventos que a app já emite (outbound)

| Evento | Quando | Origem |
|---|---|---|
| `invoice.issued` | factura emitida | InvoiceService |
| `payment.reconciled` | pagamento reconciliado → factura paga | MarkInvoicePaid |
| `stock.low` | stock ≤ mínimo após movimento | StockService |
| `tenant.created` | nova empresa provisionada | TenantProvisioning |

Mais eventos são triviais de adicionar: `WebhookDispatcher::send('evento', [...])`.

## Os 10 workflows do plano (a montar no n8n)

| # | Workflow | Gatilho |
|---|---|---|
| WF1 | Factura → referência ProxyPay → SMS | `invoice.issued` |
| WF2 | Callback → reconciliação → SMS confirmação | `payment.reconciled` |
| WF3 | Facturas vencidas → lembretes D+1/D+7/D+15 (SMS+email) | cron (n8n chama API) |
| WF4 | Discrepância de pagamento → alerta equipa | `payment.manual_review` *(a emitir)* |
| WF5 | Health check de 5 em 5 min | cron |
| WF6 | Folha aprovada → PDF recibos → email colaboradores | `payroll.processed` *(a emitir)* |
| WF7 | Stock mínimo → alerta compras → draft de ordem de compra | `stock.low` |
| WF8 | Nova empresa → email boas-vindas + activar TelcoSMS | `tenant.created` |
| WF9 | Módulo activado → ProxyPay confirmado → ModuleEnabled | `module.activated` *(a emitir)* |
| WF10 | Backup diário PostgreSQL → MinIO encriptado | cron |

> Os workflows com **cron** são agendados dentro do n8n e chamam a API da app
> (endpoints a expor com token). Os com **gatilho de evento** reagem aos webhooks acima.

## Decisão de arquitetura

O ciclo crítico (factura→ProxyPay→SMS→reconciliação→contabilidade) está implementado
em **Laravel (listeners + Horizon)**, não no n8n — é mais testável e não depende de um
serviço externo para o core financeiro. O n8n acrescenta automações **periféricas**
(lembretes, alertas, notificações, backups) sem tocar no núcleo.
