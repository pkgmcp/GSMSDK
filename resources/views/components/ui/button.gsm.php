<?php
/**
 * Button Component
 * Usage: @include('components.ui.button', [
 *   'type' => 'primary|secondary|danger|ghost',
 *   'size' => 'sm|md|lg',
 *   'icon' => 'icon-name',
 *   'loading' => true|false,
 *   'disabled' => true|false,
 *   'class' => 'additional classes',
 *   'onClick' => 'handler()'
 * ])
 */

$type = $type ?? 'primary';
$size = $size ?? 'md';
$icon = $icon ?? null;
$loading = $loading ?? false;
$disabled = $disabled ?? false;
$class = $class ?? '';
$onClick = $onClick ?? null;

$typeClasses = [
    'primary' => 'btn-primary',
    'secondary' => 'btn-secondary',
    'danger' => 'btn-danger',
    'ghost' => 'btn-ghost',
];

$sizeClasses = [
    'sm' => 'btn-sm',
    'md' => '',
    'lg' => 'btn-lg',
];

$typeClass = $typeClasses[$type] ?? $typeClasses['primary'];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];

$allClasses = "btn {$typeClass} {$sizeClass} {$class}";
if ($loading) $allClasses .= ' opacity-75 cursor-not-allowed';
if ($disabled) $allClasses .= ' opacity-50 cursor-not-allowed';

$onClickAttr = $onClick ? " onclick=\"{$onClick}\"" : '';
$disabledAttr = ($disabled || $loading) ? ' disabled' : '';
?>
<button type="button" class="<?php echo $allClasses; ?>"<?php echo $onClickAttr; ?><?php echo $disabledAttr; ?>>
  <?php if ($icon && !$loading): ?>
    <svg class="icon-<?php echo $size === 'sm' ? 'sm' : ($size === 'lg' ? 'lg' : ''); ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <?php if ($icon === 'plus'): ?><path d="M12 5v14M5 12h14"/><?php endif; ?>
      <?php if ($icon === 'edit'): ?><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/><?php endif; ?>
      <?php if ($icon === 'delete'): ?><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/><?php endif; ?>
      <?php if ($icon === 'save'): ?><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/><?php endif; ?>
    </svg>
  <?php endif; ?>
  <?php if ($loading): ?>
    <svg class="icon-<?php echo $size === 'sm' ? 'sm' : ($size === 'lg' ? 'lg' : ''); ?> animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
      <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"/>
    </svg>
  <?php endif; ?>
  <span><?php echo $text ?? 'Button'; ?></span>
</button>
