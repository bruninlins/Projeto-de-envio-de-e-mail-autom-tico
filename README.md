# 📌 Envio Automático de Relatório de Cronotacógrafos

Este projeto gera automaticamente um relatório em Excel (XLSX) com os cronotacógrafos que vencem no mês atual e envia o arquivo por e-mail como anexo.

<br>

## 🚀 Funcionalidades

- Conexão com banco de dados PostgreSQL.

- Geração de planilha Excel com:

  - Cabeçalho formatado (fundo preto, fonte branca, centralizado).

  - Linhas com cores alternadas (preto/branco).

- Envio automático do arquivo gerado por e-mail via SMTP (Gmail) usando PHPMailer.

<br>

## 📂 Estrutura do Projeto

```bash
📁 projeto/
 ├── enviar_relatorio.php   # Script principal (gera e envia o relatório)
 ├── config.php             # Configurações de e-mail e banco
 ├── vendor/                # Dependências instaladas pelo Composer
 └── src/                   # Bibliotecas do PHPMailer
```

<br>

## ⚙️ Pré-requisitos

- PHP 8+
- Composer instalado
- Extensão pdo_pgsql habilitada no PHP
- Conta de e-mail Gmail com App Password habilitado (não funciona com senha normal).

  <br>

  
