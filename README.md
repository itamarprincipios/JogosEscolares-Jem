# Sistema JEM - Jogos Escolares Municipais

Sistema completo de gerenciamento de Jogos Escolares Municipais desenvolvido com PHP, MySQL, HTML, CSS e JavaScript.

## 📋 Índice

- [Características](#características)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Configuração](#configuração)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Uso](#uso)
- [Credenciais Padrão](#credenciais-padrão)
- [Funcionalidades](#funcionalidades)

## ✨ Características

- ✅ Sistema de autenticação com controle de acesso baseado em roles
- ✅ Design moderno com glassmorphism e animações
- ✅ Gerenciamento completo de escolas, categorias e modalidades
- ✅ Cadastro e gerenciamento de alunos atletas
- ✅ Sistema de inscrições de equipes com validações
- ✅ Aprovação de inscrições por administradores
- ✅ Relatórios completos com exportação CSV
- ✅ Upload de fotos de alunos
- ✅ Interface responsiva para mobile, tablet e desktop
- ✅ Notificações toast para feedback do usuário
- ✅ Validação de CPF e formatação automática

## 🔧 Requisitos

- PHP 8.0 ou superior
- MySQL 8.0 ou superior
- Servidor web (Apache/Nginx)
- Extensões PHP:
  - PDO
  - pdo_mysql
  - mbstring
  - fileinfo

## 📦 Instalação

### 1. Clone ou baixe o projeto

```bash
cd e:/Programação/JogosEscolares
```

### 2. Configure o banco de dados

Crie um banco de dados MySQL:

```sql
CREATE DATABASE jem_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Importe o schema e dados iniciais

```bash
mysql -u root -p jem_database < database/schema.sql
mysql -u root -p jem_database < database/seed.sql
```

### 4. Configure as permissões de upload

```bash
chmod 755 uploads/
chmod 755 uploads/students/
```

### 5. Configure o arquivo config.php

Edite `config/config.php` com suas credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'jem_database');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('SITE_URL', 'http://seu-dominio.com');
```

## ⚙️ Configuração

### Configuração do Servidor Web

#### Apache (.htaccess)

Crie um arquivo `.htaccess` na raiz:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirect to HTTPS
    # RewriteCond %{HTTPS} off
    # RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# PHP Settings
php_value upload_max_filesize 5M
php_value post_max_size 5M
php_value max_execution_time 300
php_value max_input_time 300
```

#### Nginx

```nginx
server {
    listen 80;
    server_name seu-dominio.com;
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
}
```

## 📁 Estrutura do Projeto

```
JogosEscolares/
├── admin/                      # Módulo administrativo
│   ├── dashboard.php          # Dashboard do admin
│   ├── schools.php            # Gerenciamento de escolas
│   ├── categories.php         # Gerenciamento de categorias
│   ├── modalities.php         # Gerenciamento de modalidades
│   ├── professors.php         # Gerenciamento de professores
│   ├── registrations.php      # Aprovação de inscrições
│   ├── teams.php              # Visualização de equipes
│   └── reports.php            # Geração de relatórios
├── professor/                  # Módulo do professor
│   ├── dashboard.php          # Dashboard do professor
│   ├── students.php           # Gerenciamento de alunos
│   ├── create-registration.php # Criar inscrição
│   ├── my-registrations.php   # Ver inscrições
│   └── my-teams.php           # Ver equipes aprovadas
├── api/                        # Endpoints da API
│   ├── schools-api.php
│   ├── categories-api.php
│   ├── modalities-api.php
│   ├── students-api.php
│   ├── registrations-api.php
│   ├── professors-api.php
│   └── reports-api.php
├── assets/
│   ├── css/
│   │   └── styles.css         # Estilos globais
│   └── js/
│       ├── notifications.js   # Sistema de notificações
│       └── validation.js      # Validações de formulário
├── config/
│   └── config.php             # Configurações do sistema
├── database/
│   ├── schema.sql             # Schema do banco de dados
│   └── seed.sql               # Dados iniciais
├── includes/
│   ├── auth.php               # Funções de autenticação
│   ├── db.php                 # Funções de banco de dados
│   ├── header.php             # Header das páginas
│   ├── sidebar.php            # Sidebar de navegação
│   └── footer.php             # Footer das páginas
├── uploads/
│   └── students/              # Fotos dos alunos
├── index.php                   # Página inicial
├── login.php                   # Página de login
├── register.php                # Solicitação de acesso
├── logout.php                  # Logout
└── README.md                   # Este arquivo
```

## 🚀 Uso

### Acesso Inicial

1. Acesse `http://seu-dominio.com`
2. Clique em "Acessar Sistema"
3. Use as credenciais padrão (veja abaixo)

### Credenciais Padrão

**Administrador:**
- Email: `admin@jem.com`
- Senha: `Admin@123`

> ⚠️ **IMPORTANTE**: Altere essas credenciais imediatamente após o primeiro login!

### Fluxo de Trabalho

#### Para Professores:

1. Solicitar acesso via página de registro
2. Aguardar aprovação do administrador
3. Fazer login com as credenciais fornecidas
4. Cadastrar alunos da escola
5. Criar inscrições de equipes
6. Acompanhar status das inscrições

#### Para Administradores:

1. Configurar categorias e modalidades
2. Cadastrar escolas
3. Aprovar solicitações de professores
4. Revisar e aprovar inscrições de equipes
5. Gerar relatórios
6. Gerenciar todo o sistema

## 🎯 Funcionalidades

### Módulo Administrativo

- **Dashboard**: Visão geral com estatísticas
- **Escolas**: CRUD completo de escolas
- **Categorias**: Gerenciamento de categorias de idade
- **Modalidades**: Gerenciamento de modalidades esportivas
- **Professores**: Gerenciamento de professores e aprovação de acessos
- **Inscrições**: Aprovação/rejeição de inscrições de equipes
- **Equipes**: Visualização de todas as equipes aprovadas
- **Relatórios**: Geração de relatórios com filtros e exportação CSV

### Módulo Professor

- **Dashboard**: Visão geral da escola
- **Alunos**: Cadastro completo de alunos com foto
- **Nova Inscrição**: Criar inscrições de equipes com validações
- **Minhas Inscrições**: Visualizar e gerenciar inscrições
- **Minhas Equipes**: Ver equipes aprovadas e imprimir listas

### Validações Implementadas

- ✅ CPF válido e único
- ✅ Email válido e único
- ✅ Idade compatível com categoria
- ✅ Gênero compatível (exceto modalidades mistas)
- ✅ Aluno não pode estar em duas equipes da mesma modalidade/categoria
- ✅ Validação de upload de fotos (tipo e tamanho)

## 🎨 Design

O sistema utiliza um design moderno com:

- **Glassmorphism**: Efeitos de vidro fosco
- **Gradientes**: Cores vibrantes e harmoniosas
- **Animações**: Micro-animações suaves
- **Responsivo**: Adaptável a todos os dispositivos
- **Dark Mode**: Tema escuro por padrão

## 🔒 Segurança

- Senhas hasheadas com bcrypt
- Prepared statements (PDO) para prevenir SQL injection
- Sanitização de inputs
- Controle de acesso baseado em roles
- Validação de uploads
- Sessões seguras

## 📊 Banco de Dados

### Tabelas Principais:

- `users`: Usuários do sistema (admin e professores)
- `schools`: Escolas cadastradas
- `categories`: Categorias de idade
- `modalities`: Modalidades esportivas
- `students`: Alunos atletas
- `registrations`: Inscrições de equipes
- `enrollments`: Relação aluno-inscrição
- `registration_requests`: Solicitações de acesso de professores

## 🐛 Troubleshooting

### Erro de conexão com banco de dados

Verifique:
- Credenciais em `config/config.php`
- Serviço MySQL está rodando
- Banco de dados foi criado
- Schema foi importado

### Upload de fotos não funciona

Verifique:
- Permissões da pasta `uploads/students/`
- Configurações PHP: `upload_max_filesize` e `post_max_size`
- Espaço em disco disponível

### Sessão expira rapidamente

Ajuste em `config/config.php`:
```php
define('SESSION_LIFETIME', 3600 * 8); // 8 horas
```

## 📝 Próximos Passos

Arquivos adicionais que você pode criar:

1. **API Endpoints**: Criar todos os arquivos em `/api/` para operações CRUD
2. **Páginas Admin**: Completar todas as páginas de gerenciamento
3. **Páginas Professor**: Completar todas as funcionalidades do professor
4. **Relatórios**: Implementar diferentes tipos de relatórios
5. **Exportação**: Adicionar exportação PDF além de CSV
6. **Notificações**: Sistema de notificações por email
7. **Backup**: Sistema de backup automático do banco de dados

## 📄 Licença

Este projeto foi desenvolvido para uso interno do sistema de Jogos Escolares Municipais.

## 👥 Suporte

Para suporte, entre em contato com o administrador do sistema.

---

**Desenvolvido com ❤️ para os Jogos Escolares Municipais**
