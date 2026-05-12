<?php
require_once __DIR__ . '/../includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . url(is_admin() ? '/admin/dashboard.php' : '/user/dashboard.php'));
    exit;
}

$errors = [];
$old    = ['name' => '', 'email' => '', 'phone' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $old['name']  = trim((string) ($_POST['name']  ?? ''));
    $old['email'] = trim((string) ($_POST['email'] ?? ''));
    $old['phone'] = trim((string) ($_POST['phone'] ?? ''));
    $password     = (string)        ($_POST['password'] ?? '');

    if ($old['name'] === '' || $old['email'] === '' || $password === '') {
        $errors[] = 'Name, email and password are required.';
    } elseif (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please provide a valid email address.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'An account with this email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, "user", ?)');
            $stmt->execute([$old['name'], $old['email'], $hash, $old['phone'] ?: null]);
            flash('success', 'Account created — please sign in');
            header('Location: ' . url('/auth/login.php'));
            exit;
        }
    }
}

$pageTitle = 'Request Access';
require __DIR__ . '/../includes/header.php';
?>
<div class="flex min-h-screen flex-row-reverse">
  <!-- ── Right: Hero ─────────────────────────────── -->
  <div class="relative hidden w-[52%] overflow-hidden bg-stone-950 lg:block">
    <img src="<?= e(url('/assets/images/corporate-hero.png')) ?>" alt="Corporate fleet"
         class="absolute inset-0 h-full w-full object-cover object-center opacity-50" style="transform:scaleX(-1)">
    <div class="absolute inset-0 bg-gradient-to-t from-stone-950 via-stone-950/30 to-transparent"></div>
    <div class="relative flex h-full flex-col justify-between p-12 text-right">
      <div class="flex justify-end">
        <a href="<?= e(url('/')) ?>" class="flex items-center gap-2.5">
          <span class="display-serif text-xl text-white">AutoBook</span>
          <span class="flex h-8 w-8 items-center justify-center rounded bg-white text-stone-950">
            <i data-lucide="car-front" style="width:17px;height:17px"></i>
          </span>
        </a>
      </div>
      <div>
        <p class="eyebrow mb-4 text-copper-400">Join the fleet</p>
        <h1 class="display-serif text-[46px] leading-[1.05] text-white">
          Request access<br>
          <span class="italic text-stone-300">to get started.</span>
        </h1>
        <p class="mt-5 max-w-sm ml-auto text-[15px] leading-relaxed text-stone-400">
          Create your account and start booking corporate vehicles in minutes. Admin approval required.
        </p>
      </div>
    </div>
  </div>

  <!-- ── Left: Form ─────────────────────────────── -->
  <div class="flex flex-1 flex-col justify-center bg-stone-50 px-8 py-16 sm:px-12 lg:px-16 xl:px-24">
    <div class="mx-auto w-full max-w-sm">
      <a href="<?= e(url('/')) ?>" class="mb-10 flex items-center gap-2.5 lg:hidden">
        <span class="flex h-8 w-8 items-center justify-center rounded bg-stone-900 text-white">
          <i data-lucide="car-front" style="width:17px;height:17px"></i>
        </span>
        <span class="display-serif text-xl text-stone-900">AutoBook</span>
      </a>

      <div class="mb-8">
        <h2 class="display-serif text-[34px] leading-[1] text-stone-900">Request Access</h2>
        <p class="mt-2 text-sm text-stone-500">Fill in your details to create an account.</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-5 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
          <?= e($errors[0]) ?>
        </div>
      <?php endif; ?>

      <form method="post" class="space-y-4">
        <?= csrf_field() ?>
        <div>
          <label for="name" class="block text-sm font-medium text-stone-700 mb-1.5">Full Name</label>
          <input id="name" name="name" type="text" required value="<?= e($old['name']) ?>"
                 placeholder="e.g. John Smith" class="input-modern">
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-stone-700 mb-1.5">Corporate Email</label>
          <input id="email" name="email" type="email" required autocomplete="email"
                 placeholder="name@company.com" value="<?= e($old['email']) ?>" class="input-modern">
        </div>
        <div>
          <label for="phone" class="block text-sm font-medium text-stone-700 mb-1.5">Phone Number</label>
          <input id="phone" name="phone" type="tel" value="<?= e($old['phone']) ?>"
                 placeholder="08x-xxx-xxxx" class="input-modern">
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-stone-700 mb-1.5">Password</label>
          <input id="password" name="password" type="password" required autocomplete="new-password"
                 placeholder="Create a strong password" class="input-modern">
        </div>
        <div class="pt-1">
          <button type="submit" class="btn-primary w-full py-2.5">Create Account</button>
        </div>
      </form>

      <p class="mt-10 text-center text-sm text-stone-500">
        Already have an account?
        <a href="<?= e(url('/auth/login.php')) ?>" class="font-medium text-stone-900 underline underline-offset-4 hover:text-stone-700 transition-colors">Sign in instead</a>
      </p>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
