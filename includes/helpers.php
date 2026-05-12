<?php
/**
 * View helpers — status badges, image fallback, date formatting.
 * Kept tiny on purpose; mirrors the React components 1-to-1.
 */

declare(strict_types=1);

// ── STATUS BADGE ──────────────────────────────────────────────────────────
// Mirrors src/components/ui/StatusBadge.tsx
const STATUS_STYLES = [
    'available' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'approved'  => 'bg-blue-50 text-blue-700 ring-blue-200',
    'completed' => 'bg-stone-100 text-stone-600 ring-stone-200',
    'pending'   => 'bg-amber-50 text-amber-700 ring-amber-200',
    'booked'    => 'bg-rose-50 text-rose-700 ring-rose-200',
    'rejected'  => 'bg-rose-50 text-rose-700 ring-rose-200',
];

const STATUS_LABELS = [
    'available' => 'พร้อมใช้งาน',
    'booked'    => 'ไม่ว่าง',
    'pending'   => 'รอดำเนินการ',
    'approved'  => 'อนุมัติแล้ว',
    'rejected'  => 'ไม่อนุมัติ',
    'completed' => 'คืนรถแล้ว',
];

const STATUS_ICONS = [
    // lucide names — rendered via <i data-lucide="…">
    'available' => 'circle',
    'approved'  => 'check-circle-2',
    'completed' => 'check-circle-2',
    'pending'   => 'clock',
    'booked'    => 'x-circle',
    'rejected'  => 'x-circle',
];

function status_badge(string $status): string
{
    $status = strtolower($status);
    $style  = STATUS_STYLES[$status] ?? STATUS_STYLES['completed'];
    $label  = STATUS_LABELS[$status] ?? $status;
    $icon   = STATUS_ICONS[$status]  ?? 'circle';
    return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-medium ring-1 ring-inset ' . $style . '">'
         . '<i data-lucide="' . $icon . '" class="mr-1.5 flex-shrink-0" style="width:11px;height:11px"></i>'
         . e($label)
         . '</span>';
}

// ── CAR IMAGE LOOKUP ──────────────────────────────────────────────────────
// Mirrors IMAGE_MAP in src/components/cars/CarCard.tsx
const CAR_IMAGE_MAP = [
    'van'     => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?q=80&w=1400&auto=format&fit=crop',
    'suv'     => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?q=80&w=1400&auto=format&fit=crop',
    'sedan'   => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?q=80&w=1400&auto=format&fit=crop',
    'pickup'  => 'https://images.unsplash.com/photo-1559405623-61e389e8dbad?q=80&w=1400&auto=format&fit=crop',
    'minivan' => 'https://images.unsplash.com/photo-1563720223185-11003d516935?q=80&w=1400&auto=format&fit=crop',
];
const CAR_FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?q=80&w=1400&auto=format&fit=crop';

function car_image(string $type, ?string $uploadedImage = null): string
{
    if ($uploadedImage !== null && $uploadedImage !== '') {
        return url('/assets/images/cars/' . $uploadedImage);
    }
    return CAR_IMAGE_MAP[strtolower($type)] ?? CAR_FALLBACK_IMAGE;
}

// ── DATE FORMATTING (Thai locale-ish without intl extension) ──────────────
const THAI_MONTHS = [
    1 => 'ม.ค.',  2 => 'ก.พ.',  3 => 'มี.ค.',  4 => 'เม.ย.',
    5 => 'พ.ค.',  6 => 'มิ.ย.',  7 => 'ก.ค.',  8 => 'ส.ค.',
    9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.',
];
const THAI_MONTHS_LONG = [
    1 => 'มกราคม',  2 => 'กุมภาพันธ์', 3 => 'มีนาคม',   4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน',   7 => 'กรกฎาคม',  8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม',   11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];

/** Returns ['day' => '12', 'month' => 'พ.ค.', 'year' => '2569', 'date' => '12 พ.ค.', 'long' => '12 พฤษภาคม 2569'] */
function thai_date(string $dbDate): array
{
    $ts = strtotime($dbDate);
    if ($ts === false) {
        return ['day' => '—', 'month' => '', 'year' => '', 'date' => '—', 'long' => '—'];
    }
    $d = (int) date('j', $ts);
    $m = (int) date('n', $ts);
    $y = (int) date('Y', $ts) + 543; // Buddhist Era
    return [
        'day'   => (string) $d,
        'month' => THAI_MONTHS[$m],
        'year'  => (string) $y,
        'date'  => $d . ' ' . THAI_MONTHS[$m],
        'long'  => $d . ' ' . THAI_MONTHS_LONG[$m] . ' ' . $y,
    ];
}

/** Inclusive day count between two YYYY-MM-DD strings. */
function date_range_days(string $start, string $end): int
{
    $a = strtotime($start);
    $b = strtotime($end);
    if ($a === false || $b === false) return 0;
    return (int) floor(($b - $a) / 86400) + 1;
}

/**
 * Detects overlapping bookings for a car (pending or approved) within a date range.
 * Returns true when the requested window CONFLICTS with an existing booking.
 *
 * $excludeBookingId lets you skip a given booking row (used when editing).
 */
function has_overlap(PDO $pdo, int $carId, string $start, string $end, ?int $excludeBookingId = null): bool
{
    $sql = "SELECT COUNT(*) FROM bookings
            WHERE car_id = :car
              AND status IN ('pending','approved')
              AND start_date <= :end
              AND end_date   >= :start";
    $params = [':car' => $carId, ':start' => $start, ':end' => $end];
    if ($excludeBookingId !== null) {
        $sql .= ' AND id <> :exclude';
        $params[':exclude'] = $excludeBookingId;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return ((int) $stmt->fetchColumn()) > 0;
}
