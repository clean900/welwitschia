# Conformidade AGT — Facturação Eletrónica (FE)

> Análise da **Especificação Técnica FE** (DS.120, v1.1 — Nov/2025) e mapeamento para o Welwitschia ERP.
> Base legal: **Decreto Presidencial 71/25**.

## 1. Modelo da AGT (o que mudou face ao que assumíamos)

Não é "software certificado com assinatura offline + QR" apenas — é uma **integração por API
em tempo real**: o software **submete cada factura à AGT** e recebe validação.

| Item | Detalhe |
|---|---|
| Protocolo | **REST** (JSON) ou **SOAP** · HTTPS · **síncrono** |
| Base homologação | `https://sifphml.minfin.gov.ao/sigt/fe/v1/…` |
| Autenticação | Cabeçalho **Username + Password** (token, via OWSM) |
| Assinaturas | **JWS RS256 (RSA + SHA-256)** |
| Limite | até **30 facturas** por chamada a `registarFactura` |

### Serviços
| Serviço | Endpoint (REST) |
|---|---|
| Solicitar Série | `/sigt/fe/v1/solicitarSerie` |
| Listar Séries | `/sigt/fe/v1/listarSeries` |
| Registar Factura | `/sigt/fe/v1/registarFactura` |
| Obter Estado | `/sigt/fe/v1/obterEstado` |
| Listar / Consultar Factura | `/sigt/fe/v1/listarFacturas` · `/consultarFactura` |
| Validar Documento | `/sigt/fe/v1/validarDocumento` |

### Assinaturas JWS (RS256)
- **`jwsSoftwareSignature`** — sobre `softwareInfo` (productId, productVersion, softwareValidationNumber), com a **chave privada do SOFTWARE**.
- **`jwsDocumentSignature`** — sobre `documentNo, taxRegistrationNumber, documentType, documentDate, customerTaxID, customerCountry, companyName, documentTotals`, com a **chave privada do EMISSOR**.
- **`jwsSignature`** (séries) — sobre `taxRegistrationNumber, requestID`, com a chave privada do emissor.

### Numeração (documentNo) — formato SAF-T(AO)
`<código interno> <espaço> <série> /-/ <nº sequencial>`. A **série é atribuída pela AGT**
(`solicitarSerie` devolve `seriesCode`, `authorizedQuantity`, `firstDocumentNo`, `lastDocumentNo`).

### Tipos de documento (documentType)
`FT` Factura · `FR` Factura/Recibo · `FA` Adiantamento · `FG` Global · `GF` Genérica ·
`ND` Nota Débito · `NC` Nota Crédito · `RC/RG` Recibos · `TV` Talão · `AF` Autofacturação · etc.

### Estados (documentStatus)
`N` Normal · `S` Autofacturação · `A` Anulado (exige `documentCancelReason` I/N) ·
`R` Resumo · `C` Correcção (exige `rejectedDocumentNo`).

---

## 2. O que já temos vs. o que falta (gap analysis)

| Requisito AGT | Estado no Welwitschia |
|---|---|
| Faturas com itens, IVA, totais | ✅ (mapear para `documentTotals`/`taxes`) |
| Numeração sequencial sem lacunas | ✅ (local; **falta alinhar com a série da AGT**) |
| Plano de contas / SAF-T base | 🟡 (PGC ✅; SAF-T(AO) export é rascunho) |
| **Assinatura JWS RS256** (software + documento) | ⬜ **a construir** |
| **Solicitar/gerir série na AGT** | ⬜ **a construir** |
| **Submeter factura (`registarFactura`)** | ⬜ **a construir** |
| **Estados de validação** (obter/consultar) | ⬜ **a construir** |
| Tipos de documento (FT/FR/ND/NC/…) | 🟡 (só FT; falta os outros) |
| `customerTaxID/Country`, `companyName` | 🟡 (cliente como texto; falta NIF estruturado) |
| `softwareValidationNumber` | ⬜ (emitido pela AGT após registo) |
| Username/Password do serviço | ⬜ (fornecido pela AGT) |
| Códigos CAE, isenções IVA/IS/IEC | ⬜ (tabelas no anexo da spec — a importar) |

---

## 3. Plano de conformidade (faseado)

**Fase A — Núcleo criptográfico e cliente** *(em curso)*
- `JwsSigner` (RS256) — assina/verifica com chaves PEM.
- `config/agt.php` — base URL, username/password, softwareValidationNumber, chaves.
- `AgtFeClient` — `solicitarSerie`, `registarFactura`, `obterEstado` (HTTP + Http::fake nos testes).

**Fase B — Modelo de dados**
- `agt_series` (série atribuída pela AGT por tipo/ano/estabelecimento).
- Campos na factura: `document_type`, `agt_request_id`, `agt_status`, `customer_country`.
- Builder do payload `registarFactura` a partir da `Invoice`.

**Fase C — Fluxo**
- Ao emitir factura: obter série (se não houver) → assinar → submeter → guardar estado.
- Ecrã de estado AGT na factura + reprocessamento de rejeições (documentStatus=C).

**Fase D — SAF-T(AO) + QR + tabelas**
- Export SAF-T(AO) conforme schema · QR conforme · importar CAE/isenções.

## 4. Credenciais

- **Homologação:** 5 NIF de teste + chaves RSA (do xlsx da AGT) guardados em
  `storage/app/agt/homologacao.json` (**fora do git**). NIFs: 5000413178, 5001441337,
  5000471283, 5000537039, 5000930091.
- **Falta obter da AGT:** `Username/Password` do serviço FE + `softwareValidationNumber`
  (após registo do software). Sem estes, testa-se a construção/assinatura mas não a submissão real.

## 5. Para dar entrada do pedido de certificação

1. Registar o software (Soluções Simples) e obter `softwareValidationNumber` + credenciais do serviço.
2. Implementar Fases A–C (assinatura + série + submissão) e validar contra **homologação** (sifphml) com os NIF de teste.
3. Passar a bateria de testes de conformidade da AGT.
4. Submeter o pedido com o dossier técnico.
