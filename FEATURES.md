# Welwitschia ERP — Estado vs. Plano Final v4.0

> Mapeado ao **Plano Final & Cronograma v4.0** (77 módulos · 22 MVP · 32 Fase 2 · 23 Roadmap · 14 sprints · 3 gates).
> Estado em 2026-06-16 · **58 testes automatizados verdes.**
> Legenda: ✅ feito e testado · 🟡 parcial / backend pronto sem UI · ⬜ por fazer

---

## Gates do plano
| Gate | Critério | Estado |
|---|---|---|
| **Gate 1** (D28) | Ciclo cobrança completo (factura→ProxyPay→SMS→callback→reconciliação→lançamento) | ✅ **alcançado** (lógica + testes; falta credenciais reais) |
| **Gate 2** (D70) | Os 22 módulos MVP integrados | 🟡 **~14/22** (core financeiro+RH+contab. feitos; faltam vendas/compras/stock) |
| **Gate 3** (D86) | UAT + sign-off fiscal/laboral + deploy produção | ⬜ (bloqueado por validação fiscal + servidor) |

## 22 Módulos MVP — estado real
| Módulo (plano) | Estado |
|---|---|
| M01–M02 Auth + RBAC (2FA, papéis) | ✅ (login+Sanctum+2FA serviço; RBAC 9 papéis) |
| M03 Tenant middleware | ✅ |
| M04 Auditoria hash-chain | ✅ (+ comando `audit:verify-chain`) |
| M05 Configuração tenant (ProxyPay/SMS) | ✅ |
| M06 API REST | 🟡 (Sanctum; falta OpenAPI/Swagger) |
| M07 Notificações | 🟡 (SMS sim; email/push não) |
| M10 Logs do sistema | 🟡 |
| **M11 Motor de pagamentos** (state machine, idempotência, retry) | ✅ |
| **M12 ProxyPay por tenant** (HMAC, circuit breaker) | ✅ (falta circuit breaker) |
| **M13 TelcoSMS por tenant** (Sender ID) | ✅ |
| **M14 Reconciliação** | ✅ |
| M15–M16 Cobrança automática (lembretes D+1/7/15, comprovativos) | ⬜ |
| M19–M20 Facturação + Numeração AGT | ✅ |
| M22–M23 Proformas + **PDF** | ⬜ |
| M24–M25 PGC + Lançamentos | ✅ |
| M27 IVA (apuramento mensal) | 🟡 (IVA na factura; falta apuramento) |
| M32–M33 Colaboradores + Salários (IRT/INSS) | ✅ |
| M34–M36 Presenças + Férias + **Recibos PDF** | 🟡 (recibos no ecrã; falta presenças/férias/PDF) |
| M41 Clientes CRM (NIF, crédito, score) | 🟡 (cliente como texto na factura) |
| M44 Tabelas de preços | ⬜ |
| M49–M51 Fornecedores + Compras + Stock | ⬜ |
| M59 DMS básico | ⬜ |
| M66 Analytics cobrança (DSO, aging) | 🟡 (painel com KPIs; falta DSO/aging) |
| M68 n8n (10 workflows) | ⬜ (substituído por listeners+Horizon) |

## SaaS / Backend Admin (plano S5 + S8) — **EM CURSO**
| Funcionalidade | Estado |
|---|---|
| Schema landlord (tenants, plans, subscriptions, tenant_modules) | ✅ |
| Onboarding: registo → tenant criado | ✅ (falta pagar plano via ProxyPay) |
| **Backend Admin: gerir tenants** | ⬜ (a construir) |
| **Activar TelcoSMS por tenant (admin)** | ⬜ (a construir) |
| **Métricas plataforma (MRR, módulos, consumo SMS)** | ⬜ (a construir) |
| Clientes/parceiros da landing | ⬜ (a construir) |
| `ModuleEnabled` middleware (bloqueia módulos inactivos) | ✅ (middleware; falta fluxo de activação) |
| Cobrança recorrente de subscrições | ⬜ |

## Frontend (Vue 3) — ecrãs
✅ Landing · Wizard registo · Login · Painel · Faturas · Cobranças · RH & Salários · Contabilidade · Configuração
⬜ Relatórios · Vendas · Compras/Stock · Clientes · App mobile

## Infra & DevOps
| | Estado |
|---|---|
| Git fonte única (GitHub) + CI (58 testes) | ✅ |
| Deploy SSH (GitHub Actions) | ✅ (falta servidor) |
| Horizon (fila pagamentos) | ✅ |
| Proxmox/VPS + Caddy SSL wildcard | ⬜ |
| n8n self-hosted | ⬜ |
| Backups PostgreSQL→MinIO, monitoring | ⬜ |

## Fase 2 (32 módulos) e Roadmap (23) — resumo
⬜ CRM Pipeline · POS offline · Armazéns/Lotes · Projetos/Timesheet · Helpdesk · DMS+IA ·
Contratos/Assinatura · BI · Agentes IA/MCP · Kanban · Secretária Virtual IA · Frota GPS ·
Jitsi · VoIP/GoIP · CCTV NVR · POD · eCommerce/Storefronts · WhatsApp · e-Fatura AGT ·
Orçamentação · Recrutamento ATS · e-learning · Open Banking · Expansão PALOP

---

## Decisões que diferem do plano (aprovadas pelo Bráulio)
1. **Domínio único** (welwitschia.ao + login email→empresa) em vez de **subdomínio wildcard** `*.welwitschia.ao` — isolamento por schema mantém-se.
2. **Sanctum** (tokens + sessão) em vez de **JWT** — equivalente, mais simples.
3. **Listeners + Horizon** em vez de **n8n** para as automações do ciclo (mais testável; n8n fica para Fase 2).
4. **Deploy**: VPS/Proxmox em vez de cPanel (incompatível com Postgres/Redis/Horizon).

## Bloqueadores reais para produção
- **Validação fiscal/laboral AO** (IRT, INSS, IVA, PGC, numeração AGT) — sign-off de consultor.
- **Credenciais reais** ProxyPay + TelcoSMS.
- **Servidor** (Proxmox/VPS) provisionado.
