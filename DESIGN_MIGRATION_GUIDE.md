# Design System Migration Guide

This guide helps you apply the new design system to other pages in the Finance Tracker application.

## What's Been Updated

✅ **CSS Design System** (`resources/css/app.css`)
- OKLCH color palette for light and dark modes
- New utility classes: `.surface-card`, `.surface-elevated`, `.glow-*`
- Financial indicators: `.text-fin-gain`, `.text-fin-loss`
- Consistent shadows and border styles

✅ **Dashboard Page** (`resources/js/pages/Dashboard.vue`)
- Updated stat cards with new styling
- Modernized entry lists and dividers
- Redesigned modals with new color system
- Updated buttons and form inputs

## Pages Needing Updates

### 1. **Analytics Page** (`resources/js/pages/Analytics.vue`)

#### Changes to Make:
- Replace gray colors with design system colors
- Update chart container styling
- Apply new typography classes
- Add `.surface-card` to chart panels

**Example:**
```vue
<!-- Before -->
<div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm 
           dark:border-gray-700 dark:bg-gray-800/50">

<!-- After -->
<div class="surface-card p-5 lg:p-6">
```

### 2. **Assets Page** (`resources/js/pages/Assets.vue`)

#### Changes to Make:
- Apply `.surface-card` to asset cards
- Update table styling with new border colors
- Replace gray color classes
- Use financial indicator colors for gains/losses

**Example:**
```vue
<!-- Asset gain -->
<span class="text-fin-gain">+5.2%</span>

<!-- Asset loss -->
<span class="text-fin-loss">-2.1%</span>
```

### 3. **Yearly Page** (`resources/js/pages/Yearly.vue`)

#### Changes to Make:
- Modernize yearly summary cards
- Update chart styling
- Apply new spacing and typography

## Step-by-Step Migration Process

### For Each Page:

1. **Replace Card Backgrounds**
   ```vue
   // Old Pattern
   class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm 
          dark:border-gray-700 dark:bg-gray-800/50"
   
   // New Pattern
   class="surface-card p-5 lg:p-6"
   ```

2. **Update Text Colors**
   ```vue
   // Old
   text-gray-600 dark:text-gray-400          → text-muted-foreground
   text-gray-900 dark:text-gray-100          → text-foreground
   text-gray-500 dark:text-gray-400          → text-muted-foreground
   bg-gray-100 dark:bg-gray-700              → bg-accent
   border-gray-200 dark:border-gray-700      → border-border
   ```

3. **Update Button Styles**
   ```vue
   // Old
   class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 
          text-sm font-semibold text-gray-900 hover:bg-gray-50 
          dark:border-gray-600 dark:bg-gray-800"
   
   // New (Primary)
   class="rounded-lg border border-primary bg-primary px-5 py-2.5 
          text-sm font-semibold text-primary-foreground hover:bg-primary/90"
   
   // New (Secondary)
   class="rounded-lg border border-border bg-card px-5 py-2.5 
          text-sm font-semibold text-foreground hover:bg-accent/50"
   ```

4. **Update Table Styling**
   ```vue
   // Old
   divide-y divide-gray-200 dark:divide-gray-700
   
   // New
   divide-y divide-border/30
   
   // Row hover
   hover:bg-gray-50/50 dark:hover:bg-gray-700/30
   → hover:bg-accent/20
   ```

5. **Update Labels and Tags**
   ```vue
   // Old
   <span class="text-xs font-medium text-gray-600">Label</span>
   
   // New (With background)
   <span class="text-xs font-medium text-muted-foreground bg-muted/40 
          px-2.5 py-1 rounded-lg">Label</span>
   ```

6. **Apply New Typography**
   ```vue
   // Section headers
   class="text-sm font-bold text-foreground font-display tracking-tight"
   
   // Descriptive text
   class="text-[11px] text-muted-foreground font-sans"
   
   // Values
   class="text-lg font-mono font-bold text-foreground"
   ```

## Color Reference Map

| Old CSS Class | New Design Token |
|---------------|------------------|
| `bg-white` | `bg-card` |
| `bg-gray-50` | `bg-accent` |
| `bg-gray-100` | `bg-accent/50` |
| `bg-gray-200` | `bg-border` |
| `text-gray-900` | `text-foreground` |
| `text-gray-600` | `text-muted-foreground` |
| `text-gray-400` | `text-muted-foreground` |
| `border-gray-200` | `border-border` |
| `border-gray-300` | `border-border` |
| `border-blue-500` | `border-primary` / `bg-primary` |
| `text-red-600` | `text-destructive` |
| `text-green-600` | `text-fin-gain` |

## Component Patterns

### KPI/Summary Card
```vue
<div class="surface-card p-5 lg:p-6">
  <p class="text-[11px] font-semibold tracking-[0.08em] uppercase 
            text-muted-foreground mb-2.5">{{ label }}</p>
  <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono">
    {{ value }}
  </p>
  <p v-if="change" class="text-xs text-muted-foreground/70 mt-3">
    {{ change }}% vs last period
  </p>
</div>
```

### Data Table Row
```vue
<tr class="border-b border-border/30 hover:bg-accent/20 transition-colors">
  <td class="p-4 text-sm font-semibold text-foreground">{{ item.name }}</td>
  <td class="p-4 text-xs font-mono text-muted-foreground">{{ item.value }}</td>
  <td class="p-4 text-right">
    <span :class="item.change > 0 ? 'text-fin-gain' : 'text-fin-loss'">
      {{ item.change > 0 ? '+' : '' }}{{ item.change }}%
    </span>
  </td>
</tr>
```

### Section Header
```vue
<div class="mb-4 flex items-center justify-between">
  <div>
    <h3 class="text-sm font-bold text-foreground font-display tracking-tight">
      {{ title }}
    </h3>
    <p class="text-[11px] text-muted-foreground mt-0.5 font-sans">
      {{ subtitle }}
    </p>
  </div>
</div>
```

## Testing Checklist

For each updated page:

- [ ] Light mode colors are correct
- [ ] Dark mode colors are correct
- [ ] Buttons have proper hover states
- [ ] Form inputs show focus ring
- [ ] Cards have proper shadows
- [ ] Text contrast meets WCAG AA
- [ ] Layout is responsive (mobile/tablet/desktop)
- [ ] Icons are visible and properly colored
- [ ] Tables are readable with new colors
- [ ] Modal dialogs are properly styled

## Quick Copy-Paste Fixes

### Remove all dark: color classes
```bash
# Find and replace pattern:
# Replace: dark:text-gray-\d+ with nothing
# Replace: dark:bg-gray-\d+ with nothing
# Replace: dark:border-gray-\d+ with nothing
```

### Convert text colors
```bash
# text-gray-900/100 → text-foreground
# text-gray-600/400 → text-muted-foreground
# text-gray-500 → text-muted-foreground/70
```

## Need Help?

Refer to `DESIGN_SYSTEM.md` for:
- Complete color palette
- Typography guidelines
- Component examples
- Shadow system details
- Animation patterns

## Common Issues

**Issue**: Colors don't match in dark mode
**Solution**: Ensure you're not using explicit dark: overrides. The design system handles dark mode automatically via `.dark` class.

**Issue**: Buttons look wrong
**Solution**: Use complete class sets - don't mix old and new systems.

**Issue**: Spacing looks off
**Solution**: Check that you're using `p-5 lg:p-6` pattern consistently.

**Issue**: Text is hard to read
**Solution**: Always pair text color with proper background. Use `text-foreground` on `bg-card` or `text-muted-foreground` on light backgrounds.

## Apply to All Pages (Bulk Update)

You can systematically update all `.vue` files by:

1. Find all `.rounded-xl.border.border-gray-*` patterns
2. Replace with `.surface-card`
3. Update color classes using the reference map
4. Test in browser
5. Verify responsive design
