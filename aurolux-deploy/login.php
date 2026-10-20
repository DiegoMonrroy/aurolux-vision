<?php
require __DIR__ . '/_private/auth.php';
auth_start();
if (auth_user()) { header('Location: index.php'); exit; }

$config = require __DIR__ . '/_private/config.php';
$error  = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = (string)($_POST['password'] ?? '');

    if (!csrf_check((string)($_POST['csrf'] ?? ''))) {
        $error = 'La sesión expiró. Intenta de nuevo.';
    } elseif (auth_locked()) {
        $error = 'Demasiados intentos. Espera un minuto.';
    } else {
        $u  = $config['users'][strtolower($email)] ?? null;
        $ok = $u !== null && password_verify($pass, $u['hash']);
        if ($ok) {
            auth_login($u['name'], strtolower($email));
            header('Location: index.php');
            exit;
        }
        auth_fail();
        $error = 'Correo o contraseña incorrectos.';
    }
}
$h = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
header('Cache-Control: no-store, max-age=0');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<link rel="icon" type="image/png" href="assets/logo.png">
<title>Ingresar · Aurolux Visión</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}
  :root{--ink:#0B132B;--teal:#14b8a6;--purple:#8b5cf6;--brand:linear-gradient(135deg,#14b8a6 0%,#8b5cf6 100%)}
  body{margin:0;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:24px;font-family:Inter,system-ui,sans-serif;color:#1e293b;background:var(--ink);position:relative;overflow:hidden}
  body::before,body::after{content:"";position:absolute;width:420px;height:420px;border-radius:50%;filter:blur(90px);opacity:.28;pointer-events:none}
  body::before{background:var(--teal);top:-120px;left:-100px}
  body::after{background:var(--purple);bottom:-140px;right:-100px}
  .card{position:relative;z-index:1;width:100%;max-width:400px;background:#fff;border-radius:20px;padding:32px 28px;
    box-shadow:0 25px 60px rgba(0,0,0,.45);border-top:4px solid transparent;border-image:var(--brand) 1;border-image-slice:1}
  .logo{display:block;margin:0 auto 18px;height:64px;width:auto}
  h1{font-size:1.35rem;margin:0 0 4px;text-align:center;color:var(--ink)}
  .sub{margin:0 0 22px;text-align:center;font-size:.85rem;color:#64748b}
  label{display:block;font-size:.75rem;font-weight:500;color:#64748b;margin:14px 0 6px}
  .field{position:relative}
  input{width:100%;padding:11px 14px;font:inherit;font-size:.92rem;border:1px solid #e2e8f0;border-radius:10px;outline:none;transition:border-color .15s,box-shadow .15s}
  input:focus{border-color:var(--teal);box-shadow:0 0 0 3px rgba(20,184,166,.15)}
  .field input{padding-right:64px}
  .toggle{position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:0;font:inherit;font-size:.75rem;color:#64748b;cursor:pointer;padding:6px 8px;border-radius:6px}
  .toggle:hover{background:#f1f5f9}
  .btn{width:100%;margin-top:22px;padding:12px;border:0;border-radius:10px;font:inherit;font-weight:600;font-size:.92rem;color:#fff;background:var(--brand);cursor:pointer;box-shadow:0 6px 18px rgba(139,92,246,.3);transition:opacity .15s}
  .btn:hover{opacity:.9}
  .error{margin-top:14px;padding:10px 12px;font-size:.82rem;color:#b91c1c;background:#fef2f2;border:1px solid #fecaca;border-radius:10px}
  .hint{margin-top:20px;padding:12px 14px;font-size:.78rem;color:#115e59;background:#f0fdfa;border:1px solid #99f6e4;border-radius:10px}
  .hint code{background:#ccfbf1;padding:1px 6px;border-radius:5px;font-size:.78rem}
  .hint button{margin-top:8px;font:inherit;font-size:.75rem;font-weight:600;color:#0d9488;background:none;border:0;padding:0;cursor:pointer;text-decoration:underline}
  .foot{position:relative;z-index:1;margin-top:20px;font-size:.72rem;color:#94a3b8;text-align:center}
  .foot a{color:#5eead4}
</style>
</head>
<body>
  <main class="card">
    <img class="logo" src="assets/logo.png" alt="Aurolux Visión · Claridad que ilumina tu mundo">
    <h1>Bienvenida a Aurolux</h1>
    <p class="sub">Ingresa para ver la demo de recetas y órdenes digitales.</p>

    <form method="post" action="login.php" autocomplete="on" novalidate>
      <input type="hidden" name="csrf" value="<?= $h(csrf_token()) ?>">
      <label for="email">Correo</label>
      <input id="email" name="email" type="email" required autofocus autocomplete="username" value="<?= $h($email) ?>" placeholder="tu@correo.cl">
      <label for="password">Contraseña</label>
      <div class="field">
        <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="••••••••">
        <button type="button" class="toggle" id="toggle" aria-label="Mostrar u ocultar contraseña">Mostrar</button>
      </div>
      <?php if ($error): ?><div class="error" role="alert"><?= $h($error) ?></div><?php endif; ?>
      <button class="btn" type="submit">Ingresar</button>
    </form>

    <?php if (!empty($config['show_demo_hint'])): $first = array_key_first($config['users']); ?>
    <div class="hint">
      <b>Acceso demo</b><br>
      Correo: <code><?= $h($first) ?></code><br>
      Clave: <code>Aurolux2026!</code><br>
      <button type="button" id="fill">Usar credenciales demo</button>
    </div>
    <?php endif; ?>
  </main>
  <div class="foot">Demo construida por <a href="https://blaknode.dev">Blaknode.dev</a> para Aurolux Visión</div>

<script>
  const pw = document.getElementById('password');
  document.getElementById('toggle').onclick = e => {
    const show = pw.type === 'password';
    pw.type = show ? 'text' : 'password';
    e.target.textContent = show ? 'Ocultar' : 'Mostrar';
  };
  const fill = document.getElementById('fill');
  if (fill) fill.onclick = () => {
    document.getElementById('email').value = <?= json_encode($first ?? '') ?>;
    pw.value = 'Aurolux2026!';
    pw.focus();
  };
</script>
</body>
</html>
