# GSMSDK UI Components Documentation

## Overview

GSMSDK provides a comprehensive UI component library built with Tailwind CSS and XHTML. All components are designed with accessibility, responsiveness, and consistency in mind.

## Color System

### Primary Colors
```css
--accent: #a855f7;      /* Purple */
--accent2: #c084fc;    /* Light Purple */
--accent3: #9333ea;    /* Dark Purple */
--teal: #14b8a6;       /* Teal */
--cyan: #06b6d4;       /* Cyan */
```

### Semantic Colors
```css
--green: #10b981;      /* Success */
--red: #f43f5e;        /* Error */
--yellow: #eab308;     /* Warning */
```

### Neutral Colors
```css
--bg: #0a0a0f;         /* Background */
--bg2: #111118;        /* Surface */
--bg3: #18181f;        /* Cards */
--border: #2a2a38;     /* Borders */
--text: #e8e8f0;       /* Primary Text */
--text2: #a0a0b0;      /* Secondary Text */
--text3: #606070;      /* Muted Text */
```

## Components

### 1. Button Component

**Location**: `resources/views/components/ui/button.gsm.php`

**Usage**:
```xml
@include('components.ui.button', [
    'type' => 'primary',
    'size' => 'md',
    'icon' => 'plus',
    'text' => 'Add Item',
    'onClick' => 'handleAdd()'
])
```

**Types**:
- `primary` - Main action (purple gradient)
- `secondary` - Secondary action (glass effect)
- `danger` - Destructive action (red)
- `ghost` - Minimal action (transparent)

**Sizes**:
- `sm` - Small (0.375rem padding)
- `md` - Medium (0.5rem padding)
- `lg` - Large (0.625rem padding)

**Icons**:
- `plus` - Add icon
- `edit` - Edit icon
- `delete` - Delete icon
- `save` - Save icon

### 2. Card Component

**Location**: `resources/views/components/ui/card.gsm.php`

**Usage**:
```xml
@include('components.ui.card', [
    'elevated' => true,
    'hoverable' => true,
    'header' => 'Card Title',
    'headerIcon' => 'devices',
    'body' => 'Card content here...'
])
```

**Props**:
- `elevated` - Add shadow elevation
- `hoverable` - Lift on hover
- `header` - Card header text
- `headerIcon` - Header icon (devices, flash, adb, log)
- `title` - Section title
- `body` - Main content
- `footer` - Footer content

### 3. Alert Component

**Location**: `resources/views/components/ui/alert.gsm.php`

**Usage**:
```xml
@include('components.ui.alert', [
    'type' => 'success',
    'title' => 'Success!',
    'message' => 'Operation completed successfully',
    'dismissible' => true
])
```

**Types**:
- `info` - Informational (blue)
- `success` - Success (green)
- `warning` - Warning (yellow)
- `danger` - Error (red)

### 4. Badge Component

**Location**: `resources/views/components/ui/badge.gsm.php`

**Usage**:
```xml
@include('components.ui.badge', [
    'type' => 'success',
    'size' => 'md',
    'text' => 'Active'
])
```

**Types**:
- `default` - Gray
- `primary` - Purple
- `success` - Green
- `danger` - Red
- `warning` - Yellow
- `info` - Cyan
- `accent` - Pink

**Sizes**:
- `sm` - Extra small
- `md` - Medium
- `lg` - Large

## Layouts

### Admin Layout

**Structure**:
```
resources/views/layouts/admin/base.gsm.php  (Base template)
resources/views/layouts/admin.gsm.php        (Wrapper)
resources/views/admin/partials/header.gsm.php  (Header)
resources/views/admin/partials/sidebar.gsm.php (Navigation)
resources/views/admin/partials/footer.gsm.php  (Footer)
```

**Features**:
- Fixed header with search
- Collapsible sidebar
- Responsive grid system
- Toast notifications
- Modal overlays

### User Layout

**Structure**:
```
resources/views/layouts/user/base.gsm.php   (Base template)
resources/views/user/partials/header.gsm.php (Header)
resources/views/user/partials/sidebar.gsm.php (Navigation)
```

## Pages

### Admin Dashboard

**Location**: `resources/views/admin/dashboard.gsm.php`

**Features**:
- Statistics cards with animated counters
- Device activity chart
- Flash operations chart
- Recent activity table
- Quick actions grid

### API Documentation

**Location**: `resources/views/api/docs.gsm.php`

**Features**:
- Endpoint listing
- Authentication guide
- Code examples
- Error codes reference
- Rate limiting info

### User Panel

**Location**: `resources/views/user/panel.gsm.php`

**Features**:
- Profile settings
- Activity log
- Account management

## Icons

**Location**: `resources/views/components/icons/`

**Available Icons**:
- `chevron_right.gsm.php`
- `chevron_down.gsm.php`
- `check.gsm.php`
- `x.gsm.php`
- `cog.gsm.php`

**Usage**:
```xml
@include('components/icons/chevron_right')
```

## Utilities

### Animations

```css
.animate-in { animation: fadeIn 0.4s ease-out forwards; }
.stagger-1 { animation-delay: 0.05s; }
.stagger-2 { animation-delay: 0.1s; }
.stagger-3 { animation-delay: 0.15s; }
```

### Grid System

```css
.grid-cols-stats { 
  display: grid; 
  grid-template-columns: repeat(4, 1fr); 
  gap: 1rem; 
}

@media (max-width: 1024px) { 
  grid-template-columns: repeat(2, 1fr); 
}

@media (max-width: 640px) { 
  grid-template-columns: 1fr; 
}
```

### Charts

```css
.chart-bar { display: flex; align-items: center; gap: 1rem; }
.chart-bar-label { width: 120px; font-size: 0.85rem; }
.chart-bar-track { 
  flex: 1; 
  height: 8px; 
  background: var(--bg4); 
  border-radius: 4px; 
  overflow: hidden; 
}
```

## Responsive Design

### Breakpoints

- `sm` - 640px (mobile)
- `md` - 768px (tablet)
- `lg` - 1024px (desktop)
- `xl` - 1280px (large desktop)

### Mobile Behavior

- Sidebar collapses on screens < 640px
- Grid becomes single column
- Search box hidden in mobile menu
- Font sizes adjusted

## Accessibility

### Features

- Semantic HTML structure
- ARIA labels on interactive elements
- Keyboard navigation support (Ctrl+K for search)
- Focus-visible states
- Reduced motion support
- Screen reader friendly

### Best Practices

```xml
<!-- Good -->
<button aria-label="Close modal" onclick="close()">
  <span class="sr-only">Close</span>
  <svg>...</svg>
</button>

<!-- Bad -->
<div onclick="close()">X</div>
```

## Theming

### Custom Colors

Add to CSS variables:

```css
:root {
  --accent: #your-color;
  --accent2: #lighter-variant;
  --accent3: #darker-variant;
}
```

### Dark Mode

All components designed for dark mode first. Use CSS variables for easy theming.

## Performance

### Optimization Tips

1. **Lazy load** icons and SVGs
2. **Defer** non-critical JavaScript
3. **Minify** CSS and HTML
4. **Cache** static assets
5. **Preload** critical resources

### Bundle Size

- Base CSS: ~8KB
- JavaScript: ~12KB minified
- Icons: Inline SVG (no extra requests)

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Migration Guide

### v1.x to v2.0

```diff
- <div class="card bg-gray-800">
+ @include('components.ui.card', ['elevated' => true])

- <button class="btn btn-purple">
+ @include('components.ui.button', ['type' => 'primary'])
```

## Contributing

1. Follow existing patterns
2. Use Tailwind utility classes
3. Include ARIA labels
4. Test on mobile
5. Add changelog entry

## Resources

- [Tailwind CSS](https://tailwindcss.com)
- [SVG Icons](https://heroicons.com)
- [Accessibility Guide](https://a11yproject.com)
- [Color Palette](https://coolors.co)