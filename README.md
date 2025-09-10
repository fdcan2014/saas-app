# SaaS Multi-Tenant Platform – Esboço do Projeto

Este repositório contém um esqueleto inicial para um SaaS modular multi‑tenant
baseado no framework PHP CodeIgniter 4.  O objetivo deste esqueleto é
fornecer uma fundação organizada e extensível para um sistema completo de
gestão de lojas/marketplace com diversos módulos (ERP, PDV, CMS, Marketplace
etc.), conforme discutido no blueprint original.

## Estrutura de Pastas

```
saas-app/
├── composer.json           # Configuração do autoload PSR‑4 para os módulos
├── public/                 # Pasta pública (Web root)
│   └── index.php           # Bootstrap da aplicação (placeholder)
└── Modules/                # Módulos isolados
    ├── Auth/               # Módulo de autenticação e segurança
    │   ├── Config/         # Configurações e rotas do módulo
    │   ├── Controllers/    # Controladores Web/API
    │   ├── Database/
    │   │   ├── Migrations/ # Migrations de banco de dados
    │   │   └── Seeds/      # Seeds iniciais
    │   ├── Entities/       # Entidades (Models) específicos do módulo
    │   ├── Repositories/   # Repositórios para acesso a dados
    │   ├── Services/       # Serviços de domínio (p. ex. AuthService)
    │   └── Views/          # Views (painel/admin)
    └── Tenant/             # Módulo de gestão de lojas (a ser implementado)
```

## Como Usar

* **Instalação do Composer:** Este esqueleto pressupõe que você usará
  Composer para gerenciar dependências e autoload.  Como o ambiente atual
  não inclui o PHP/Composer, os arquivos PHP aqui são exemplos e não
  executáveis.  Em um ambiente real você deverá instalar o CodeIgniter 4
  (pelo `composer create-project codeigniter4/appstarter`) e então copiar
  esses módulos para dentro do diretório do projeto.

* **Autoload de Módulos:** O `composer.json` incluído define um namespace
  `Modules\` apontando para a pasta `Modules/`.  Isso permite que as classes
  sejam carregadas automaticamente usando PSR‑4.

* **Estrutura Modular:** Cada módulo possui suas próprias pastas de
  `Controllers`, `Entities`, `Repositories`, `Services`, `Database` (para
  migrations/seeds), `Config` e `Views`.  Essa organização facilita a
  manutenção e permite que novos módulos sejam adicionados sem acoplar
  código no núcleo da aplicação.

* **Próximos Passos:**
  1. **Configuração do CI4:** Inicie um projeto CodeIgniter 4 em um
     ambiente PHP com Composer.  Ajuste o `composer.json` para incluir o
     autoload dos módulos.
  2. **Registro de Migrations e Rotas:** Crie um script (ou use hooks do CI4)
     para carregar automaticamente as migrations e rotas de cada módulo.
  3. **Implementar Auth:** Complete o módulo `Auth` seguindo os arquivos
     fornecidos como exemplos — implemente login por sessão e via JWT,
     controle de roles/permissões e suporte multi‑tenant.
  4. **Expandir para Tenant:** Após o Auth, crie o módulo `Tenant` para
     cadastro de lojas, domínios e planos.  O esboço inclui um exemplo de
     filtro (`TenantResolverFilter`) e serviço (`TenantService`) para
     resolver o `tenant_id` a partir do subdomínio ou header da requisição e
     disponibilizá‑lo no contexto de execução.

Este esboço serve como ponto de partida; adapte conforme necessário para
atender às necessidades específicas do seu SaaS.