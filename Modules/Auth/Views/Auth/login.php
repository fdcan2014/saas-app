<!--
    Formulário de login simples.
    Em um projeto real você usaria um template engine (view) do CodeIgniter
    e Tailwind/HTMX para uma melhor experiência.
-->
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>Login</h1>
    <form method="post" action="/auth/login">
        <label>E‑mail: <input type="email" name="email" required></label><br>
        <label>Senha: <input type="password" name="password" required></label><br>
        <label>ID do Tenant: <input type="number" name="tenant_id" required></label><br>
        <button type="submit">Entrar</button>
    </form>
</body>
</html>