<?php
/**
 * Food & Drink SVG Icon Library
 * Each icon: [label_ar, svg_content]
 * SVG viewBox="0 0 32 32", fill-based for visibility at small sizes
 */

define('FOOD_ICONS', [

  // ── الأطباق الرئيسية ──
  'burger'    => ['label'=>'برغر',       'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="4" y="18" width="24" height="4" rx="2"/><rect x="6" y="23" width="20" height="3" rx="1.5"/><path d="M4 16c0-1.1.9-2 2-2h20a2 2 0 0 1 2 2v1H4v-1z"/><rect x="4" y="13" width="24" height="2"/><path d="M8 13c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>'],

  'sandwich'  => ['label'=>'ساندويش',    'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 8h24v3H4zM4 21h24v3H4z"/><rect x="2" y="11" width="28" height="10" rx="1" opacity=".4"/><path d="M6 13h3v6H6zm5 0h3v6h-3zm5 0h3v6h-3zm5 0h3v6h-3z" opacity=".6"/></svg>'],

  'pizza'     => ['label'=>'بيتزا',      'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 3 3 27h26L16 3z"/><circle cx="16" cy="15" r="2.2" fill="#fff" opacity=".6"/><circle cx="11" cy="21" r="1.5" fill="#fff" opacity=".6"/><circle cx="21" cy="21" r="1.5" fill="#fff" opacity=".6"/><path d="M3 27h26" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>'],

  'rice'      => ['label'=>'أرز',        'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><ellipse cx="16" cy="20" rx="12" ry="6"/><path d="M7 20c0-8 4-14 9-14s9 6 9 14"/><circle cx="12" cy="16" r="1.2" fill="#fff" opacity=".5"/><circle cx="16" cy="13" r="1.2" fill="#fff" opacity=".5"/><circle cx="20" cy="16" r="1.2" fill="#fff" opacity=".5"/></svg>'],

  'chicken'   => ['label'=>'دجاج',       'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M10 26c-2 0-4-1-4-3 0-3 3-5 7-6l8-10c1-1 3-1 4 0s1 3 0 4L17 19c1 4-1 7-4 7H10z"/><path d="M24 8a3 3 0 1 0-6 0 3 3 0 0 0 6 0z" opacity=".5"/><rect x="6" y="26" width="8" height="2" rx="1"/></svg>'],

  'steak'     => ['label'=>'لحم',        'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M5 18c0-7 5-13 11-13s11 6 11 13c0 4-2 6-5 7H10c-3-1-5-3-5-7z"/><path d="M10 14c0-3 2-5 6-5" stroke="#fff" stroke-width="1.5" fill="none" stroke-linecap="round" opacity=".5"/><ellipse cx="16" cy="27" rx="10" ry="2" opacity=".3"/></svg>'],

  'fish'      => ['label'=>'سمك',        'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M6 16c0 0 3-8 10-8s13 4 13 8-6 8-13 8S6 16 6 16z"/><path d="M3 10l4 6-4 6" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="23" cy="14" r="1.5" fill="#fff" opacity=".7"/></svg>'],

  'shrimp'    => ['label'=>'روبيان',     'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M22 6c-4 0-7 3-7 8 0 3-2 5-5 5H8c-1 0-2 1-2 2s1 2 2 2h2c5 0 9-4 9-9 0-3 1.5-5 4-5 1.5 0 2.5 1 2.5 2.5S24.5 14 23 14"/><path d="M20 4c2 0 4 1 4 3" fill="none" stroke="currentColor" stroke-width="1.5"/><circle cx="24" cy="7" r="2"/></svg>'],

  'pasta'     => ['label'=>'مكرونة',     'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><ellipse cx="16" cy="21" rx="12" ry="5"/><path d="M10 21c0-5 2-10 6-12" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".6"/><path d="M16 21c0-5 2-10 6-12" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".6"/><path d="M13 21c0-5 2-10 6-12" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".4"/><ellipse cx="16" cy="26" rx="12" ry="2" opacity=".3"/></svg>'],

  'hotdog'    => ['label'=>'هوت دوج',   'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M6 14h20c0 4.4-4.5 8-10 8S6 18.4 6 14z"/><path d="M4 12h24v2H4z"/><path d="M6 12c0-4.4 4.5-8 10-8s10 3.6 10 8"/><path d="M10 18h12" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".5"/></svg>'],

  'taco'      => ['label'=>'تاكو',       'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 22C4 14 9 8 16 8s12 6 12 14H4z"/><path d="M10 16c2-2 4-3 6-3s4 1 6 3" fill="none" stroke="#fff" stroke-width="1.5" opacity=".5"/><circle cx="13" cy="18" r="1.5" fill="#fff" opacity=".4"/><circle cx="19" cy="18" r="1.5" fill="#fff" opacity=".4"/></svg>'],

  'sushi'     => ['label'=>'سوشي',       'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><ellipse cx="16" cy="18" rx="10" ry="7"/><ellipse cx="16" cy="16" rx="7" ry="5" opacity=".3"/><ellipse cx="16" cy="15" rx="4" ry="3" fill="#fff" opacity=".5"/><rect x="6" y="22" width="20" height="3" rx="1.5"/></svg>'],

  'noodles'   => ['label'=>'نودلز',      'group'=>'main',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 16h24v2c0 4-5 8-12 8S4 22 4 18v-2z"/><path d="M4 16c0-1 1-2 2-2h20c1 0 2 1 2 2"/><path d="M9 14c0-3 2-6 7-6 4 0 7 3 7 6" fill="none" stroke="currentColor" stroke-width="1.5"/><path d="M8 18h3" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".5"/><path d="M13 19h6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".5"/></svg>'],

  // ── مشروبات ──
  'coffee'    => ['label'=>'قهوة',       'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M6 12h16l-2 12H8L6 12z"/><path d="M22 14h2a3 3 0 0 1 0 6h-2"/><path d="M10 6c0-2 2-3 2-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/><path d="M15 6c0-2 2-3 2-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/></svg>'],

  'tea'       => ['label'=>'شاي',        'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M7 12h14l-1.5 12H8.5L7 12z"/><path d="M21 14h2a3 3 0 0 1 0 6h-2"/><path d="M10 10c1-2 4-2 4-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/><ellipse cx="15" cy="27" rx="7" ry="1.5" opacity=".3"/></svg>'],

  'juice'     => ['label'=>'عصير',       'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M9 10h14l-2 16H11L9 10z"/><path d="M7 10h18v2H7z"/><circle cx="14" cy="17" r="2" fill="#fff" opacity=".4"/><circle cx="19" cy="20" r="1.5" fill="#fff" opacity=".4"/><path d="M18 6V10" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>'],

  'smoothie'  => ['label'=>'سموثي',      'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M10 8h12l-2 18H12L10 8z"/><path d="M8 8h16v3H8z"/><path d="M16 4V8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="15" cy="15" r="2.5" fill="#fff" opacity=".3"/><circle cx="19" cy="19" r="2" fill="#fff" opacity=".3"/></svg>'],

  'water'     => ['label'=>'ماء',        'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M16 4C16 4 8 12 8 19a8 8 0 0 0 16 0c0-7-8-15-8-15z"/><path d="M11 20a5 5 0 0 0 4 4" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".5"/></svg>'],

  'milk'      => ['label'=>'حليب',       'group'=>'drinks',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M10 8l2-4h8l2 4v16a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V8z"/><path d="M10 12h12"/><path d="M14 16h4" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".6"/></svg>'],

  // ── مشويات ──
  'kebab'     => ['label'=>'كباب',       'group'=>'grill',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="15" y="3" width="2" height="26" rx="1"/><ellipse cx="16" cy="9" rx="5" ry="3.5"/><ellipse cx="16" cy="16" rx="5" ry="3.5" opacity=".7"/><ellipse cx="16" cy="23" rx="5" ry="3.5" opacity=".5"/></svg>'],

  'bbq'       => ['label'=>'مشوى',       'group'=>'grill',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 14h24v2c0 5-5 9-12 9S4 21 4 16v-2z"/><path d="M3 14h26v2H3z"/><path d="M13 25v4m6-4v4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10 10c1-3 4-5 6-8 2 3 5 5 6 8" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".5"/></svg>'],

  'grill'     => ['label'=>'شواية',      'group'=>'grill',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="3" y="12" width="26" height="3" rx="1.5"/><rect x="3" y="17" width="26" height="3" rx="1.5" opacity=".6"/><rect x="6" y="22" width="20" height="3" rx="1.5" opacity=".4"/><path d="M9 8c0-3 3-4 3-6 2 1 2 4 2 6" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".5"/><path d="M17 8c0-3 3-4 3-6 2 1 2 4 2 6" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".5"/></svg>'],

  'skewer'    => ['label'=>'شيش',        'group'=>'grill',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M5 27L27 5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/><ellipse cx="13" cy="19" rx="4" ry="3" transform="rotate(-45 13 19)"/><ellipse cx="19" cy="13" rx="4" ry="3" transform="rotate(-45 19 13)" opacity=".7"/><circle cx="8" cy="24" r="3" opacity=".5"/></svg>'],

  // ── حلويات ──
  'cake'      => ['label'=>'كيك',        'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="3" y="15" width="26" height="12" rx="2"/><rect x="7" y="9" width="18" height="6" rx="1"/><path d="M12 9V7a4 4 0 0 1 8 0v2"/><path d="M9 15v12" fill="none" stroke="#fff" stroke-width="1" opacity=".3"/><path d="M16 15v12" fill="none" stroke="#fff" stroke-width="1" opacity=".3"/><path d="M23 15v12" fill="none" stroke="#fff" stroke-width="1" opacity=".3"/><circle cx="16" cy="5" r="2"/></svg>'],

  'icecream'  => ['label'=>'آيس كريم',   'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M12 16l4 12 4-12"/><ellipse cx="16" cy="13" rx="8" ry="8"/><path d="M10 13a6 6 0 0 1 6-6" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".4"/></svg>'],

  'donut'     => ['label'=>'دونت',       'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path fill-rule="evenodd" d="M16 4C9.4 4 4 9.4 4 16s5.4 12 12 12 12-5.4 12-12S22.6 4 16 4zm0 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z"/><circle cx="10" cy="12" r="1.2" fill="#fff" opacity=".5"/><circle cx="22" cy="14" r="1" fill="#fff" opacity=".5"/><circle cx="16" cy="8" r="1.2" fill="#fff" opacity=".5"/></svg>'],

  'cookie'    => ['label'=>'بسكويت',     'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><circle cx="16" cy="16" r="12"/><circle cx="12" cy="12" r="1.8" fill="#fff" opacity=".5"/><circle cx="19" cy="12" r="1.5" fill="#fff" opacity=".5"/><circle cx="11" cy="19" r="1.5" fill="#fff" opacity=".5"/><circle cx="19" cy="20" r="1.8" fill="#fff" opacity=".5"/><circle cx="15" cy="17" r="1.2" fill="#fff" opacity=".5"/></svg>'],

  'chocolate' => ['label'=>'شوكولاتة',   'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="4" y="8" width="24" height="16" rx="3"/><line x1="12" y1="8" x2="12" y2="24" stroke="#fff" stroke-width="1.5" opacity=".3"/><line x1="20" y1="8" x2="20" y2="24" stroke="#fff" stroke-width="1.5" opacity=".3"/><line x1="4" y1="14" x2="28" y2="14" stroke="#fff" stroke-width="1.5" opacity=".3"/><line x1="4" y1="19" x2="28" y2="19" stroke="#fff" stroke-width="1.5" opacity=".3"/></svg>'],

  'waffle'    => ['label'=>'وافل',       'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="4" y="10" width="24" height="15" rx="3"/><line x1="4" y1="15" x2="28" y2="15" stroke="#fff" stroke-width="1.5" opacity=".35"/><line x1="4" y1="20" x2="28" y2="20" stroke="#fff" stroke-width="1.5" opacity=".35"/><line x1="10" y1="10" x2="10" y2="25" stroke="#fff" stroke-width="1.5" opacity=".35"/><line x1="16" y1="10" x2="16" y2="25" stroke="#fff" stroke-width="1.5" opacity=".35"/><line x1="22" y1="10" x2="22" y2="25" stroke="#fff" stroke-width="1.5" opacity=".35"/><path d="M8 7c0-2 2-3 4-2l12 5H8V7z"/></svg>'],

  'pancake'   => ['label'=>'فطيرة',      'group'=>'desserts',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><ellipse cx="16" cy="22" rx="12" ry="3"/><ellipse cx="16" cy="18" rx="11" ry="3" opacity=".8"/><ellipse cx="16" cy="14" rx="10" ry="3" opacity=".6"/><path d="M10 14c1-1 3-1 4 0" fill="none" stroke="#fff" stroke-width="1" opacity=".4"/></svg>'],

  // ── مقبلات وسلطات ──
  'salad'     => ['label'=>'سلطة',       'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 18h24v2a6 6 0 0 1-6 6H10a6 6 0 0 1-6-6v-2z"/><path d="M4 18c0-3 2-5 5-4 0-4 3-7 7-7s7 3 7 7c3-1 5 1 5 4"/><circle cx="12" cy="14" r="1.5" fill="#fff" opacity=".4"/><circle cx="19" cy="13" r="1.5" fill="#fff" opacity=".4"/><circle cx="16" cy="17" r="1.5" fill="#fff" opacity=".4"/></svg>'],

  'soup'      => ['label'=>'شوربة',      'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M4 16h24v2c0 5-5 9-12 9S4 23 4 18v-2z"/><path d="M4 16h24"/><path d="M10 11c0-2 2-3 2-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/><path d="M16 10c0-2 2-3 2-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/><path d="M22 11c0-2 2-3 2-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".4"/></svg>'],

  'bread'     => ['label'=>'خبز',        'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M6 14c0-5 4-9 10-9s10 4 10 9v1H6v-1z"/><rect x="4" y="15" width="24" height="10" rx="2"/><path d="M10 14c0-3 3-5 6-5" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".4"/></svg>'],

  'fries'     => ['label'=>'بطاطا',      'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M8 26h16l2-12H6l2 12z"/><rect x="10" y="8" width="3" height="8" rx="1.5"/><rect x="14.5" y="6" width="3" height="10" rx="1.5"/><rect x="19" y="8" width="3" height="8" rx="1.5"/></svg>'],

  'cheese'    => ['label'=>'جبن',        'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M3 20L16 8l13 12v3H3v-3z"/><circle cx="20" cy="18" r="2" fill="#fff" opacity=".5"/><circle cx="12" cy="20" r="1.5" fill="#fff" opacity=".5"/><circle cx="17" cy="22" r="1.2" fill="#fff" opacity=".5"/></svg>'],

  'egg'       => ['label'=>'بيض',        'group'=>'starters',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><ellipse cx="16" cy="19" rx="11" ry="8"/><path d="M16 11C16 5 22 3 22 8" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="16" cy="19" r="4" fill="#fff" opacity=".5"/></svg>'],

  // ── عام ──
  'utensils'  => ['label'=>'أدوات',      'group'=>'general',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="14" y="3" width="4" height="26" rx="2"/><path d="M8 3v8a4 4 0 0 0 4 4V3"/><rect x="6" y="3" width="2" height="8"/><rect x="12" y="3" width="2" height="8"/><path d="M22 3c2 0 4 2 4 6s-2 7-4 7v10a2 2 0 0 1-4 0V3h4z"/></svg>'],

  'chef-hat'  => ['label'=>'شيف',        'group'=>'general',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M10 16h12v9a1 1 0 0 1-1 1H11a1 1 0 0 1-1-1v-9z"/><path d="M10 18c-3 0-5-2-5-5s2-5 5-5a5 5 0 0 1 10 0c1-1 2-1 3-1a5 5 0 0 1 0 10H10z"/><rect x="10" y="24" width="12" height="2" opacity=".4"/></svg>'],

  'chili'     => ['label'=>'فلفل',       'group'=>'general',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M20 6c0 0 2-2 4-2-1 3-3 4-3 8-1 6-5 12-11 12-4 0-6-3-5-7 1-5 7-8 10-8 2 0 3 1 3 1s2-4 2-4z"/><path d="M20 6c2-3 5-4 5-4" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/><path d="M10 18c0 3 2 5 4 5" fill="none" stroke="#fff" stroke-width="1.5" stroke-linecap="round" opacity=".4"/></svg>'],

  'restaurant'=> ['label'=>'مطعم',       'group'=>'general',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><rect x="3" y="14" width="26" height="14" rx="2"/><path d="M3 14L16 4l13 10"/><rect x="12" y="19" width="8" height="9" rx="1"/><rect x="6" y="18" width="4" height="4" rx="1" opacity=".6"/><rect x="22" y="18" width="4" height="4" rx="1" opacity=".6"/></svg>'],

  'pot'       => ['label'=>'قدر',        'group'=>'general',
    'svg'=>'<svg viewBox="0 0 32 32" fill="currentColor"><path d="M6 14h20v8a4 4 0 0 1-4 4H10a4 4 0 0 1-4-4v-8z"/><rect x="4" y="12" width="24" height="3" rx="1.5"/><path d="M2 14h4m20 0h4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M12 8c0-2 2-4 4-4s4 2 4 4" fill="none" stroke="currentColor" stroke-width="1.5" opacity=".5"/></svg>'],

]);

// Icon groups for display
define('ICON_GROUPS', [
  'main'     => 'الأطباق الرئيسية',
  'drinks'   => 'المشروبات',
  'grill'    => 'المشويات',
  'desserts' => 'الحلويات',
  'starters' => 'المقبلات والسلطات',
  'general'  => 'عام',
]);

/**
 * Get SVG for a given icon key
 */
function getFoodIconSvg($key, $size = 24, $class = '') {
  $icons = FOOD_ICONS;
  if (!isset($icons[$key])) {
    // fallback utensils
    return '<svg viewBox="0 0 32 32" fill="currentColor" width="'.$size.'" height="'.$size.'" class="'.$class.'"><rect x="14" y="3" width="4" height="26" rx="2"/><path d="M8 3v8a4 4 0 0 0 4 4V3"/><rect x="6" y="3" width="2" height="8"/><rect x="12" y="3" width="2" height="8"/><path d="M22 3c2 0 4 2 4 6s-2 7-4 7v10a2 2 0 0 1-4 0V3h4z"/></svg>';
  }
  $svg = $icons[$key]['svg'];
  // Inject width/height/class into the SVG tag
  $svg = preg_replace('/<svg /', '<svg width="'.$size.'" height="'.$size.'" class="'.$class.'" ', $svg, 1);
  return $svg;
}
