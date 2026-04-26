<?php
/**
 * Alert Component
 */
$type = $type ?? 'info';
$title = $title ?? null;
$dismissible = $dismissible ?? false;
$icon = $icon ?? true;

$typeClasses = [
    'info' => 'border-blue-500/20 bg-blue-500/10 text-blue-300',
    'success' => 'border-green-500/20 bg-green-500/10 text-green-300',
    'warning' => 'border-yellow-500/20 bg-yellow-500/10 text-yellow-300',
    'danger' => 'border-red-500/20 bg-red-500/10 text-red-300',
];

$typeIcons = [
    'info' => '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
    'success' => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
    'warning' => '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    'danger' => '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
];

$typeClass = $typeClasses[$type] ?? $typeClasses['info'];
?>
<div class="rounded-lg border p-4 <?php echo $typeClass; ?> <?php echo $class ?? ''; ?>">
  <div class="flex">
    <?php if ($icon): ?>
      <div class="flex-shrink-0">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
          <?php echo $typeIcons[$type] ?? $typeIcons['info']; ?>
        </svg>
      </div>
    <?php endif; ?>
    <div class="<?php echo $icon ? 'ml-3' : ''; ?>">
      <?php if ($title): ?>
        <h3 class="text-sm font-medium mb-1" style="font-family:'Space Grotesk',sans-serif;">
          <?php echo $title; ?>
        </h3>
      <?php endif; ?>
      <div class="text-sm">
        <?php echo $message ?? $slot ?? ''; ?>
      </div>
    </div>
    <?php if ($dismissible): ?>
      <div class="ml-auto pl-3">
        <button type="button" class="-mr-1 flex rounded-md p-1.5 hover:bg-white/10" onclick="this.closest('.alert').remove()">
          <span class="sr-only">Dismiss</span>
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>
      </div>
    <?php endif; ?>
  </div>
</div>
