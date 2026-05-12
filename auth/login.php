<?php
require_once __DIR__ . '/../includes/auth.php';

// If already logged in, jump straight to the right dashboard.
if (is_logged_in()) {
    header('Location: ' . url(is_admin() ? '/admin/dashboard.php' : '/user/dashboard.php'));
    exit;
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $email    = trim((string) ($_POST['email']    ?? ''));
    $password = (string)        ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $errors[] = 'Please enter both email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            // Session fixation defense
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];

            flash('success', 'Signed in successfully');
            header('Location: ' . url($user['role'] === 'admin' ? '/admin/dashboard.php' : '/user/dashboard.php'));
            exit;
        }
    }
}

$pageTitle = 'Sign In';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex min-h-screen">
  <!-- ── Left: Hero ─────────────────────────────── -->
  <div class="relative hidden w-[52%] overflow-hidden bg-stone-950 lg:block">
    <img src="<?= e(url('/assets/images/corporate-hero.png')) ?>" alt="Corporate fleet"
         class="absolute inset-0 h-full w-full object-cover object-center opacity-50">
    <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/30 to-transparent"></div>

    <div class="relative flex h-full flex-col justify-between p-12">
      <a href="<?= e(url('/')) ?>" class="flex items-center gap-2.5">
        <span class="flex h-8 w-8 items-center justify-center rounded bg-white text-stone-950">
          <i data-lucide="car-front" style="width:17px;height:17px"></i>
        </span>
        <span class="display-serif text-xl text-white">AutoBook</span>
      </a>

      <div>
        <p class="eyebrow mb-4 text-copper-400">Enterprise Fleet Management</p>
        <h1 class="display-serif text-[46px] leading-[1.05] text-white">
          The smarter way<br>
          <span class="italic text-stone-300">to manage your fleet.</span>
        </h1>
        <p class="mt-5 max-w-sm text-[15px] leading-relaxed text-stone-400">
          Secure vehicle booking and approval workflows — designed for universities and corporate teams.
        </p>
        <ul class="mt-10 space-y-4">
          <?php foreach ([
            ['shield-check', 'Role-based access control (Admin / User)'],
            ['clock',        'Real-time availability and request tracking'],
            ['building-2',   'Multi-vehicle fleet management in one place'],
          ] as [$icon, $text]): ?>
            <li class="flex items-start gap-3 text-sm text-stone-400">
              <i data-lucide="<?= $icon ?>" class="mt-0.5 flex-shrink-0 text-copper-400" style="width:16px;height:16px"></i>
              <?= e($text) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- ── Right: Form ─────────────────────────────── -->
  <div class="flex flex-1 flex-col justify-center bg-stone-50 px-8 py-16 sm:px-12 lg:px-16 xl:px-24">
    <div class="mx-auto w-full max-w-sm">
      <a href="<?= e(url('/')) ?>" class="mb-10 flex items-center gap-2.5 lg:hidden">
        <span class="flex h-8 w-8 items-center justify-center rounded bg-stone-900 text-white">
          <i data-lucide="car-front" style="width:17px;height:17px"></i>
        </span>
        <span class="display-serif text-xl text-stone-900">AutoBook</span>
      </a>

      <div class="mb-8">
        <h2 class="display-serif text-[34px] leading-[1] text-stone-900">Sign in</h2>
        <p class="mt-2 text-sm text-stone-500">Enter your credentials to access the system.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <?= e($errors[0]) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4" id="login-form">
        <?= csrf_field() ?>
        <div>
          <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Corporate Email</label>
          <input id="email" name="email" type="email" required autocomplete="email"
                 placeholder="name@company.com" value="<?= e($email) ?>" class="input-modern">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
          <input id="password" name="password" type="password" required autocomplete="current-password"
                 placeholder="••••••••" class="input-modern">
        </div>
        <div class="pt-1">
          <button type="submit" class="btn-primary w-full py-2.5" id="signin-btn">
            <span class="inline-flex items-center gap-2 signin-default">Sign In</span>
            <span class="hidden items-center gap-2 signin-loading">
              <i data-lucide="loader-2" class="animate-spin-slow" style="width:16px;height:16px"></i>
              Authenticating…
            </span>
          </button>
        </div>
      </form>

      <!-- Demo accounts -->
      <div class="mt-8">
        <div class="relative flex items-center gap-3">
          <div class="flex-1 border-t border-stone-200"></div>
          <span class="text-[10px] font-medium uppercase tracking-[0.14em] text-stone-400">Demo Access</span>
          <div class="flex-1 border-t border-stone-200"></div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-3">
          <button type="button" class="btn-secondary text-xs py-2" data-fill-email="admin@example.com" data-fill-password="admin123456">Admin Account</button>
          <button type="button" class="btn-secondary text-xs py-2" data-fill-email="user@example.com" data-fill-password="user123456">User Account</button>
        </div>
      </div>

      <p class="mt-10 text-center text-sm text-stone-500">
        Need system access?
        <a href="<?= e(url('/auth/register.php')) ?>" class="font-medium text-stone-900 underline underline-offset-4 hover:text-stone-700 transition-colors">Request an account</a>
      </p>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('[data-fill-email]').forEach(function (btn) {
  btn.addEventListener('click', function () {
    document.getElementById('email').value = btn.getAttribute('data-fill-email');
    document.getElementById('password').value = btn.getAttribute('data-fill-password');
  });
});
document.getElementById('login-form').addEventListener('submit', function () {
  document.querySelector('.signin-default').classList.add('hidden');
  document.querySelector('.signin-loading').classList.remove('hidden');
  document.querySelector('.signin-loading').classList.add('inline-flex');
  document.getElementById('signin-btn').disabled = true;
});
</script>
<?php require __DIR__ . '/../includes/footer.php'; ?>
