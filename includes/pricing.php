<?php
/**
 * Dynamic Price Estimation
 *
 * LOOSE GEM            total = gem price
 * CUSTOM JEWELLERY     total = gem price
 *                            + metal cost     (grams x rate per gram of chosen purity)
 *                            + making charge  (% of metal cost)
 *                            + gem-setting fee (fixed per jewellery type)
 *   grams = base grams of the type, scaled for ring size (bigger ring = more metal)
 * FINISHED JEWELLERY   total = catalogue price (fixed)
 *
 * All rates live in the database (metal_rates, jewellery_types) so the owner can change them
 * without touching code. Prices are ALWAYS calculated on the server - the browser value is
 * only a preview.
 */

function metal_rates(): array {
    static $c = null;
    if ($c === null) {
        $c = [];
        foreach (db()->query('SELECT * FROM metal_rates ORDER BY sort_order') as $r) $c[$r['code']] = $r;
    }
    return $c;
}
function jewellery_types(): array {
    static $c = null;
    if ($c === null) {
        $c = [];
        foreach (db()->query('SELECT * FROM jewellery_types ORDER BY sort_order') as $r) $c[$r['code']] = $r;
    }
    return $c;
}
function get_product(int $id): ?array {
    $st = db()->prepare('SELECT * FROM products WHERE id = ? AND is_active = 1');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Ring size 1..33 (site's size list). Size 15 = reference. +/-2% metal weight per size step. */
function ring_size_factor(int $size): float {
    return max(0.7, 1 + ($size - 15) * 0.02);
}

/**
 * @param array $opts ['jtype'=>'ring|earrings|necklace|bracelet|""', 'size'=>int, 'metal'=>'18k|...']
 * @return array ['ok'=>bool, 'error'=>?string, 'opts'=>normalised options, 'breakdown'=>[...], 'total'=>float]
 */
function estimate_price(array $product, array $opts): array {
    $fail = fn(string $m) => ['ok' => false, 'error' => $m, 'opts' => [], 'breakdown' => [], 'total' => 0.0];
    $base = (float)$product['price'];

    // ---- finished jewellery: fixed price, only ring size matters ----
    if ($product['kind'] === 'jewellery') {
        $o = ['type' => $product['jewellery_type'], 'metal' => $product['metal_code']];
        if ($product['jewellery_type'] === 'ring') {
            $size = (int)($opts['size'] ?? 0);
            if ($size < 1 || $size > 33) return $fail('Please select a ring size.');
            $o['size'] = $size;
        }
        return ['ok' => true, 'error' => null, 'opts' => $o, 'breakdown' => ['Item price' => round($base, 2)], 'total' => round($base, 2)];
    }

    // ---- gemstone ----
    $jtype = $opts['jtype'] ?? '';
    if ($jtype === '' || $jtype === 'none') {
        return ['ok' => true, 'error' => null, 'opts' => ['type' => 'loose'],
                'breakdown' => ['Gemstone' => round($base, 2)], 'total' => round($base, 2)];
    }
    $types = jewellery_types();
    if (!isset($types[$jtype])) return $fail('Invalid jewellery type.');
    $t = $types[$jtype];

    $metals = metal_rates();
    $metal = $opts['metal'] ?? '';
    if (!isset($metals[$metal])) return $fail('Please select a metal type & purity.');
    $m = $metals[$metal];

    $grams = (float)$t['base_grams'];
    $o = ['type' => $jtype, 'metal' => $metal];
    if ((int)$t['has_size']) {
        $size = (int)($opts['size'] ?? 0);
        if ($size < 1 || $size > 33) return $fail('Please select a ring size.');
        $grams *= ring_size_factor($size);
        $o['size'] = $size;
    }
    $grams      = round($grams, 2);
    $metalCost  = round($grams * (float)$m['rate_per_gram'], 2);
    $making     = round($metalCost * (float)$t['making_pct'] / 100, 2);
    $setting    = round((float)$t['setting_fee'], 2);
    $total      = round($base + $metalCost + $making + $setting, 2);

    return [
        'ok' => true, 'error' => null, 'opts' => $o, 'total' => $total,
        'breakdown' => [
            'Gemstone'                                        => round($base, 2),
            "Metal ({$m['label']}, {$grams} g)"               => $metalCost,
            "Making charge ({$t['making_pct']}%)"             => $making,
            'Gem setting fee'                                 => $setting,
        ],
    ];
}

/** Human readable description of the chosen options (cart, orders). */
function describe_opts(array $o): string {
    $parts = [];
    if (!empty($o['type']) && $o['type'] === 'loose') return 'Loose gemstone';
    $types = jewellery_types(); $metals = metal_rates();
    if (!empty($o['type']) && isset($types[$o['type']])) $parts[] = $types[$o['type']]['label'];
    if (!empty($o['metal']) && isset($metals[$o['metal']])) $parts[] = $metals[$o['metal']]['label'];
    if (!empty($o['size'])) $parts[] = 'Size ' . $o['size'];
    return implode(' · ', $parts);
}
