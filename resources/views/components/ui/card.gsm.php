<?php
/**
 * Card Component
 */
$elevated = $elevated ?? false;
$hoverable = $hoverable ?? false;
$class = $class ?? '';

$elevationClass = $elevated ? 'shadow-2xl shadow-purple-500/10' : '';
$hoverClass = $hoverable ? 'hover:-translate-y-1 hover:shadow-2xl hover:shadow-purple-500/10' : '';
?>
<div class="card <?php echo $elevationClass; ?> <?php echo $hoverClass; ?> <?php echo $class; ?>">
  <?php if (!empty($header) || $collapsible ?? false): ?>
    <div class="card-header">
      <?php if (!empty($header)):
        $headerIcon = $headerIcon ?? null;
        $headerAction = $headerAction ?? null;
      ?>
        <div class="card-title">
          <?php if ($headerIcon): ?>
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="var(--accent2)" stroke-width="2">
              <?php if ($headerIcon === 'devices'): ?><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/><?php endif; ?>
              <?php if ($headerIcon === 'flash'): ?><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/><?php endif; ?>
              <?php if ($headerIcon === 'adb'): ?><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/><?php endif; ?>
              <?php if ($headerIcon === 'log'): ?><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/><?php endif; ?>
            </svg>
          <?php endif; ?>
          <?php echo $header; ?>
        </div>
      <?php endif; ?>
      <?php if ($headerAction && !empty($header)): ?>
        <?php echo $headerAction; ?>
      <?php endif; ?>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($title) && empty($header)): ?>
    <h3 class="text-lg font-semibold text-gray-100 mb-4" style="font-family:'Space Grotesk',sans-serif;">
      <?php echo $title; ?>
    </h3>
  <?php endif; ?>
  
  <?php if (!empty($body)):
    echo $body;
  else:
    echo $slot ?? '';
  endif; ?>
  
  <?php if (!empty($footer)):
    $footerClass = $footerClass ?? 'pt-4 mt-4 border-t border-gray-700';
  ?>
    <div class="card-footer <?php echo $footerClass; ?>">
      <?php echo $footer; ?>
    </div>
  <?php endif; ?>
</div>
