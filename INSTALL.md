# Sistema JEM - Guia de Instalação e Configuração

## 🚀 Instalação Rápida

### Passo 1: Preparar o Ambiente

1. **Verifique os requisitos:**
   - PHP 8.0+
   - MySQL 8.0+
   - Servidor web (Apache/Nginx)

2. **Verifique as extensões PHP necessárias:**
```bash
php -m | grep -E 'pdo|pdo_mysql|mbstring|fileinfo'
```

### Passo 2: Configurar o Banco de Dados

1. **Acesse o MySQL:**
```bash
mysql -u root -p
```

2. **Crie o banco de dados:**
```sql
CREATE DATABASE jem_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

3. **Importe o schema:**
```bash
cd e:/Programação/JogosEscolares
mysql -u root -p jem_database < database/schema.sql
```

4. **Importe os dados iniciais:**
```bash
mysql -u root -p jem_database < database/seed.sql
```

### Passo 3: Configurar o Sistema

1. **Edite o arquivo de configuração:**

Abra `config/config.php` e ajuste:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'jem_database');
define('DB_USER', 'root');          // Seu usuário MySQL
define('DB_PASS', '');              // Sua senha MySQL

// System Configuration
define('SITE_URL', 'http://localhost/JogosEscolares'); // Ajuste conforme necessário
```

2. **Configure permissões de upload:**

```bash
# Windows (PowerShell)
New-Item -ItemType Directory -Force -Path uploads\students

# Linux/Mac
mkdir -p uploads/students
chmod 755 uploads
chmod 755 uploads/students
```

### Passo 4: Configurar o Servidor Web

#### Opção A: Apache com XAMPP/WAMP

1. Copie a pasta `JogosEscolares` para `htdocs/`
2. Acesse: `http://localhost/JogosEscolares`

#### Opção B: PHP Built-in Server (Desenvolvimento)

```bash
cd e:/Programação/JogosEscolares
php -S localhost:8000
```

Acesse: `http://localhost:8000`

#### Opção C: Nginx

Crie um arquivo de configuração em `/etc/nginx/sites-available/jem`:

```nginx
server {
    listen 80;
    server_name jem.local;
    root /caminho/para/JogosEscolares;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
    
    client_max_body_size 5M;
}
```

Ative o site:
```bash
sudo ln -s /etc/nginx/sites-available/jem /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Passo 5: Primeiro Acesso

1. **Acesse o sistema:**
   - URL: `http://localhost/JogosEscolares` (ou conforme configurado)

2. **Faça login com as credenciais padrão:**
   - Email: `admin@jem.com`
   - Senha: `Admin@123`

3. **⚠️ IMPORTANTE: Altere a senha do administrador imediatamente!**

## 🔧 Configurações Avançadas

### Configurar Upload de Arquivos

Edite `php.ini`:

```ini
upload_max_filesize = 5M
post_max_size = 5M
max_execution_time = 300
max_input_time = 300
memory_limit = 128M
```

Reinicie o servidor web após as alterações.

### Configurar Email (Opcional)

Para enviar notificações por email, adicione em `config/config.php`:

```php
// Email Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'seu@email.com');
define('SMTP_PASS', 'sua_senha');
define('SMTP_FROM', 'noreply@jem.com');
```

### Configurar SSL/HTTPS

Para produção, sempre use HTTPS:

```bash
# Com Certbot (Let's Encrypt)
sudo certbot --nginx -d seu-dominio.com
```

## 📝 Tarefas Pós-Instalação

### 1. Configurar Categorias e Modalidades

Acesse como admin:
1. Vá em **Categorias** e crie as categorias de idade
2. Vá em **Modalidades** e cadastre os esportes

### 2. Cadastrar Escolas

1. Acesse **Escolas**
2. Cadastre todas as escolas participantes

### 3. Aprovar Professores

Quando professores solicitarem acesso:
1. Acesse **Professores**
2. Revise as solicitações pendentes
3. Aprove e vincule à escola correta

### 4. Backup Regular

Configure backup automático do banco de dados:

```bash
# Criar script de backup (backup.sh)
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u root -p jem_database > backup_$DATE.sql
```

## 🐛 Solução de Problemas

### Erro: "Access denied for user"

**Problema:** Credenciais incorretas do MySQL

**Solução:**
1. Verifique usuário e senha em `config/config.php`
2. Teste a conexão: `mysql -u root -p`

### Erro: "Table doesn't exist"

**Problema:** Schema não foi importado

**Solução:**
```bash
mysql -u root -p jem_database < database/schema.sql
mysql -u root -p jem_database < database/seed.sql
```

### Erro: "Failed to open stream" no upload

**Problema:** Permissões da pasta uploads

**Solução:**
```bash
# Windows
icacls uploads /grant Everyone:F /T

# Linux/Mac
chmod -R 755 uploads
chown -R www-data:www-data uploads
```

### Sessão expira muito rápido

**Solução:** Ajuste em `config/config.php`:
```php
define('SESSION_LIFETIME', 3600 * 8); // 8 horas
```

### Página em branco / Erro 500

**Solução:**
1. Ative exibição de erros em `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

2. Verifique logs do servidor:
```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/error.log
```

## 🔒 Segurança em Produção

### 1. Desabilitar exibição de erros

Em `config/config.php`:
```php
error_reporting(0);
ini_set('display_errors', 0);
```

### 2. Proteger arquivos sensíveis

Crie `.htaccess` na raiz:
```apache
# Proteger arquivos de configuração
<Files "config.php">
    Require all denied
</Files>

# Proteger banco de dados
<FilesMatch "\.(sql|db)$">
    Require all denied
</FilesMatch>
```

### 3. Usar HTTPS

Force HTTPS em `.htaccess`:
```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 4. Alterar credenciais padrão

Após primeiro login:
1. Altere a senha do admin
2. Altere o email do admin

### 5. Backup automático

Configure cron job para backup diário:
```bash
0 2 * * * /caminho/para/backup.sh
```

## 📊 Monitoramento

### Verificar espaço em disco

```bash
df -h
```

### Verificar uso de memória

```bash
free -m
```

### Monitorar logs

```bash
tail -f /var/log/apache2/access.log
tail -f /var/log/mysql/error.log
```

## 🎯 Próximos Passos

Após a instalação:

1. ✅ Alterar senha do administrador
2. ✅ Configurar categorias e modalidades
3. ✅ Cadastrar escolas
4. ✅ Testar fluxo completo (professor → inscrição → aprovação)
5. ✅ Configurar backup automático
6. ✅ Configurar SSL para produção

## 📞 Suporte

Para problemas ou dúvidas:
- Verifique a documentação no `README.md`
- Consulte os logs de erro
- Entre em contato com o administrador do sistema

---

**Sistema JEM - Jogos Escolares Municipais**
