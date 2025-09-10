# Módulo Tenant

Este módulo será responsável pela gestão de lojas (tenants) no SaaS.  As
principais responsabilidades incluem:

* Cadastro de novas lojas, domínios/subdomínios e planos.
* Definição de limites e recursos disponíveis conforme o plano.
* Resolução de tenant por subdomínio ou header para cada requisição.
* Provisionamento inicial da loja (criação de dados padrão, configuração de
  temas etc.).

Este diretório está vazio por enquanto.  Após a conclusão do módulo
Auth, implemente aqui as entidades, serviços, migrations e controladores
necessários.