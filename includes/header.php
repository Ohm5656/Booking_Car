<?php
/**
 * Shared <head> + <body> opening.
 * Sets up Tailwind (Play CDN) with the same theme tokens that the Next.js build
 * used (stone+copper editorial palette, Fraunces serif, Sarabun Thai fallback,
 * accent navy). Followed by the custom CSS in assets/css/style.css.
 *
 * Pages set:
 *   $pageTitle     — string, prepended to "— AutoBook"
 *   $bodyClass     — extra <body> classes (optional)
 *   $transparentNav — bool, hint for the user navbar (dashboard hero)
 */

if (!isset($pageTitle))     $pageTitle = 'AutoBook';
if (!isset($bodyClass))     $bodyClass = '';
if (!isset($transparentNav)) $transparentNav = false;

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';
?><!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?> — AutoBook</title>
  <link rel="icon" type="image/png" href="<?= e(url('/assets/images/icon.png')) ?>">

  <!-- Tailwind CSS (Play CDN — JIT in the browser) -->
  <script src="https://cdn.tailwindcss.com?plugins=line-clamp,forms"></script>

  <!-- Lucide icons (script #createIcons runs after DOM ready in main.js) -->
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  <!-- Recharts is loaded only on the reports page, lazily -->

  <!-- Fonts: Inter (variable, Latin) · Sarabun (Thai) · Fraunces (display serif + italic)
       Google Fonts axis order rule: lowercase axes alphabetical first (ital,opsz,wght),
       then uppercase. Tuple values follow the same order after the @. -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Sarabun:wght@300;400;500;600;700&family=Fraunces:ital,opsz,wght@0,9..144,300..700;1,9..144,300..700&display=swap"
    rel="stylesheet">

  <script>
    // ─── Tailwind theme (mirrors src/app/globals.css → @theme) ───
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'Sarabun', 'system-ui', 'sans-serif'],
            thai: ['Sarabun', 'system-ui', 'sans-serif'],
            serif: ['Fraunces', 'Sarabun', 'Georgia', 'serif'],
          },
          colors: {
            primary: {
              50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
              400: '#94a3b8', 500: '#64748b', 600: '#475569', 700: '#334155',
              800: '#1e293b', 900: '#0f172a',
            },
            accent: {
              50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 300: '#a5b4fc',
              400: '#818cf8', 500: '#4f46e5', 600: '#1e40af', 700: '#1e3a8a',
              800: '#172554', 900: '#0f172a',
            },
            copper: {
              50: '#fdf8f3', 100: '#fae5cf', 200: '#f4c896', 300: '#e8a05a',
              400: '#d97c2e', 500: '#b45309', 600: '#92400e', 700: '#78350f',
            },
          },
          keyframes: {
            'marquee-x': {
              from: { transform: 'translateX(0)' },
              to:   { transform: 'translateX(-50%)' },
            },
            'fade-up': {
              '0%':   { opacity: 0, transform: 'translateY(8px)' },
              '100%': { opacity: 1, transform: 'translateY(0)' },
            },
          },
          animation: {
            'marquee': 'marquee-x 40s linear infinite',
            'fade-up': 'fade-up 0.5s ease-out both',
          },
        },
      },
    };
  </script>

  <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')) ?>">

  <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body class="font-sans bg-stone-50 text-stone-900 antialiased <?= e($bodyClass) ?>">

<!-- Toast container (top-right) -->
<div id="toast-container" class="fixed top-4 right-4 z-[100] flex flex-col gap-2 pointer-events-none"></div>

<?php
$flash = get_flash();
if ($flash):
?>
<script>
  window.__INITIAL_FLASH__ = <?= json_encode($flash, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php endif; ?>
