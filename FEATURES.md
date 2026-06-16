# Welwitschia ERP — Inventário de Funcionalidades

> Estado em 2026-06-16. **58 testes automatizados verdes.**
> Legenda: ✅ implementado e testado · 🟡 backend pronto, falta UI (ou parcial) · ⬜ por implementar (roadmap)

---

## 1. Núcleo & Multi-tenancy
| Funcionalidade | Estado |
|---|---|
| Multi-tenant **schema-per-tenant** (stancl/tenancy, PostgreSQL) | ✅ |
| Provisionamento de empresa (schema + roles + admin + subscrição) | ✅ |
| Isolamento total de dados entre empresas | ✅ |
| Diretório central de login (`memberships`, email → empresa) | ✅ |
| Domínio único SaaS (welwitschia.ao, sem subdomínios) | ✅ |
| Plano de contas PGC semeado por empresa | ✅ |

## 2. Autenticação & Segurança
| Funcionalidade | Estado |
|---|---|
| Login único por sessão (email + password) | ✅ |
| Tokens API (Sanctum) por tenant | ✅ |
| **2FA TOTP** (Google Authenticator) | 🟡 (serviço pronto, falta ecrã) |
| Reset de password | ⬜ |
| RBAC por empresa (spatie, 9 papéis) | 🟡 (papéis criados, falta gestão na UI) |
| **Auditoria imutável hash-chain** + comando `audit:verify-chain` | ✅ |
| Encriptação de chaves (ProxyPay/SMS) AES + mascaramento em logs | ✅ |

## 3. Faturação & Cobrança
| Funcionalidade | Estado |
|---|---|
| Faturas: criar, itens, **IVA 14%** | ✅ |
| Emitir com **numeração AGT** (sequência atómica) | ✅ |
| Cancelar fatura | ✅ |
| Gerar **referência ProxyPay** + **SMS** ao cliente | ✅ |
| Motor de pagamentos (state machine + idempotência) | ✅ |
| Callback ProxyPay (webhook + HMAC + fila Horizon) | ✅ |
| Reconciliação → fatura paga (automático) | ✅ |
| Lista de **Cobranças** (estados dos pagamentos) | ✅ |
| Proformas / recibos separados | ⬜ |
| **PDF** de faturas e recibos | ⬜ |
| Submissão **e-Fatura AGT** (JWS, QR Code) | ⬜ (aguarda API AGT) |
| Múltiplos métodos de pagamento / prestações | ⬜ |

## 4. Contabilidade (PGC Angola)
| Funcionalidade | Estado |
|---|---|
| Razão em **partidas dobradas** (validação débito=crédito) | ✅ |
| Lançamentos automáticos: venda, recebimento, salários | ✅ |
| **Balancete** (saldos por conta) | ✅ |
| Plano de contas PGC (subconjunto operacional) | ✅ |
| Centros de custo | ⬜ |
| Demonstrações financeiras (Balanço, DR) | ⬜ |
| Ativos / amortizações (EAM) | ⬜ |

## 5. RH & Salários
| Funcionalidade | Estado |
|---|---|
| Colaboradores (criar, listar, desactivar) | ✅ |
| **Folha salarial** (IRT + INSS automáticos) | ✅ |
| Recibos por colaborador | ✅ |
| Lançamento contabilístico dos salários | ✅ |
| **Recibo PDF** | ⬜ |
| Subsídios/benefícios avançados, férias, presenças | ⬜ |
| Recrutamento (ATS), avaliação, formação | ⬜ |

## 6. SaaS — Planos, Subscrições & Módulos
| Funcionalidade | Estado |
|---|---|
| 4 Planos (Starter/Business/Enterprise/Unlimited) | ✅ |
| Subscrição criada no registo (trial) | 🟡 (modelo pronto, falta cobrança recorrente) |
| Add-ons / módulos à la carte (modelo + middleware) | 🟡 (sem fluxo de ativação na UI) |
| Cobrança recorrente de subscrições via ProxyPay | ⬜ |

## 7. Back-office da Plataforma (super-admin Welwitschia) — **PRÓXIMO**
| Funcionalidade | Estado |
|---|---|
| Consola super-admin (separada do tenant) | ⬜ |
| Gerir empresas (listar, suspender, ver métricas) | ⬜ |
| Gerir planos e subscrições | ⬜ |
| **Activar TelcoSMS por empresa** (governança) | ⬜ |
| Gerir clientes/parceiros da landing | ⬜ |
| Dashboard de receita da plataforma (MRR, churn) | ⬜ |

## 8. Frontend / UI
| Ecrã | Estado |
|---|---|
| Landing pública (hero, módulos, integrações) | ✅ |
| Wizard de registo de empresa (3 passos) | ✅ |
| Login único | ✅ |
| Painel (KPIs, gráfico receitas, donut, atividade) | ✅ |
| Faturas (lista/criar/detalhe/ações) | ✅ |
| Cobranças | ✅ |
| RH & Salários | ✅ |
| Contabilidade (balancete, razão) | ✅ |
| Configuração (ProxyPay/SMS) | ✅ |
| **Relatórios** | ⬜ |
| App mobile (iOS/Android) | ⬜ |

## 9. Infra & DevOps
| Funcionalidade | Estado |
|---|---|
| Git como fonte única (GitHub `clean900/welwitschia`) | ✅ |
| CI (58 testes, Postgres+Redis) | ✅ |
| Deploy SSH (GitHub Actions, gated por secrets) | ✅ (falta servidor) |
| Horizon (fila de pagamentos) | ✅ |
| **Deploy em VPS/Proxmox** | ⬜ |
| Validação fiscal (IRT/INSS/PGC/AGT) com contabilista | ⬜ **(bloqueador de produção)** |

## 10. Módulos avançados (roadmap — do catálogo de 77)
⬜ CRM Pipeline · Encomendas · POS offline · Armazéns/Lotes · Projetos/Timesheet ·
Helpdesk · DMS + IA documental · Contratos/Assinatura digital · BI self-service ·
Agentes IA/MCP · Kanban · Secretária Virtual IA · Frota GPS · Video (Jitsi) ·
VoIP (Asterisk/GoIP) · CCTV NVR · Gestão de envios/POD · eCommerce/Storefronts ·
Marketing/Redes Sociais · Omnichannel WhatsApp · Open Banking · Expansão PALOP

---

### Resumo
- **Ciclo financeiro completo** (faturação → cobrança → contabilidade → salários): ✅ **implementado e testado**
- **MVP utilizável** com UI escura e identidade visual: ✅
- **Falta para produção:** back-office da plataforma, PDF, validação fiscal, deploy real
- **Roadmap longo:** ~55 dos 77 módulos do catálogo original
