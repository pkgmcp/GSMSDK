<?php
/**
 * Badge Component
 */
$type = $type ?? 'default';
$size = $size ?? 'md';
$rounded = $rounded ?? true;

$typeClasses = [
    'default' => 'bg-gray-600 text-gray-100',
    'primary' => 'bg-purple-500 text-white',
    'success' => 'bg-green-500 text-white',
    'danger' => 'bg-red-500 text-white',
    'warning' => 'bg-yellow-500 text-gray-900',
    'info' => 'bg-cyan-500 text-gray-900',
    'accent' => 'bg-pink-500 text-white',
];

$sizeClasses = [
    'sm' => 'px-2 py-0.5 text-xs',
    'md' => 'px-2.5 py-1 text-xs font-semibold',
    'lg' => 'px-3 py-1 text-sm font-semibold',
];

$roundedClass = $rounded ? 'rounded-full' : 'rounded';

$typeClass = $typeClasses[$type] ?? $typeClasses['default'];
$sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
?>
<span class="inline-flex items-center <?php echo $typeClass; ?> <?php echo $sizeClass; ?> <?php echo $roundedClass; ?> <?php echo $class ?? ''; ?>">
  <?php echo $text ?? $slot ?? ''; ?>
</span>
