# 📌 Envio Automático de Relatório de Cronotacógrafos

Este projeto gera automaticamente um relatório em Excel (XLSX) com os cronotacógrafos que vencem no mês atual e envia o arquivo por e-mail como anexo.

<br>

## 🚀 Funcionalidades

- Conexão com banco de dados PostgreSQL.

- Geração de planilha Excel com:

  - Cabeçalho formatado.

  - Linhas com cores alternadas.

- Envio automático do arquivo gerado por e-mail via SMTP (Gmail) usando PHPMailer.

<br>

## 📂 Estrutura do Projeto

```bash
📁 projeto/
 ├── enviar_relatorio.php   # Script principal (gera e envia o relatório)
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

## config.php

É preciso criar um arquivo chamado  **`config.php`** e inserir os dados do E-mail e do banco, dessa forma:

```php
return [
    // Email
    'email' => 'seu_email@gmail.com',
    'senha' => 'sua_senha_de_app', 
    'destinatario' => 'destinatario@email.com',
    'nome_destinatario' => 'Nome do Destinatário',
    'nome_usuario' => 'Seu Nome',

    // Banco de dados
    'db_host' => '127.0.0.1',
    'db_port' => 5432,
    'db_name' => 'seu_banco',
    'db_user' => 'usuario',
    'db_pass' => 'senha'
];
```

<br>

## ▶️ Uso

Execute o script principal:


```bash
php enviar_relatorio.php
```
Se houver registros no banco, será gerado o arquivo **`vencimentos.xlsx`** e enviado para o destinatário configurado.

<br>

## 📧 Observações

- Para Gmail, é necessário ativar a opção de Senhas de App (não use a senha normal da conta).
- Se quiser rodar em um servidor automaticamente, configure um cron job:

```bash
0 9 1 * * /usr/bin/php /caminho/projeto/enviar_relatorio.php
```
(este exemplo executa todo dia 1º às 9h).
