# Design System - Quick Reference

## Color Classes

### Text Colors
```
text-foreground          → Primary text (almost black/white)
text-muted-foreground   → Secondary text (muted gray)
text-primary            → Accent text
text-destructive        → Error/delete text
text-fin-gain          → Positive returns (teal)
text-fin-loss          → Negative returns (red)
```

### Background Colors
```
bg-background          → Page background
bg-card                → Card/surface background
bg-primary             → Primary button
bg-accent              → Hover/highlight state
bg-muted               → Disabled/inactive state
bg-border              → Border color
```

### Border Colors
```
border-border          → Standard borders
border-primary         → Primary action borders
border-destructive     → Delete/warning borders
```

## Component Classes

### Cards
```vue
<!-- Standard Card -->
<div class="surface-card p-5 lg:p-6">
  <!-- content -->
</div>

<!-- Floating/Elevated (tooltip, popover) -->
<div class="surface-elevated p-3">
  <!-- content -->
</div>
```

### Buttons
```vue
<!-- Primary Button -->
<button class="rounded-lg border border-primary bg-primary px-5 py-2.5 
               text-sm font-semibold text-primary-foreground 
               hover:bg-primary/90 transition-all">
  Button
</button>

<!-- Secondary Button -->
<button class="rounded-lg border border-border bg-card px-5 py-2.5 
               text-sm font-semibold text-foreground 
               hover:bg-accent/50 transition-all">
  Button
</button>

<!-- Subtle Button -->
<button class="rounded-lg px-4 py-2 text-sm font-semibold 
               text-foreground hover:bg-accent/50">
  Button
</button>
```

### Inputs
```vue
<input class="rounded-lg border border-border bg-muted/40 px-4 py-2.5 
            text-sm transition-all focus:border-primary 
            focus:outline-none focus:ring-2 focus:ring-primary/10" />
```

### Form Labels
```vue
<label class="block text-sm font-semibold text-muted-foreground font-sans">
  Label
</label>
```

## Typography Classes

```
font-display          → Headings, important text
font-mono             → Financial values, codes
font-sans             → Body text (default)

text-[11px]           → Label text
text-sm               → Normal text
text-lg               → Headings
text-2xl/3xl          → Large headings

font-semibold         → Labels, section headers
font-bold             → Display headings
tracking-[0.08em]     → Uppercase spacing
```

## Common Patterns

### Stat/KPI Card
```vue
<div class="surface-card p-4 lg:p-5">
  <p class="text-[11px] font-semibold tracking-[0.08em] uppercase 
            text-muted-foreground mb-2.5">Label</p>
  <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono">
    Value
  </p>
</div>
```

### List Item
```vue
<div class="flex items-center justify-between py-3 border-b border-border/30 
           hover:bg-accent/20 transition-colors">
  <div>
    <p class="text-sm font-semibold text-foreground">Item Name</p>
    <p class="text-xs text-muted-foreground mt-1">Description</p>
  </div>
  <p class="text-sm font-mono font-semibold text-foreground">Value</p>
</div>
```

### Section Header
```vue
<div class="mb-4">
  <h3 class="text-sm font-bold text-foreground font-display">Title</h3>
  <p class="text-[11px] text-muted-foreground mt-0.5">Subtitle</p>
</div>
```

### Table
```vue
<table class="w-full text-sm">
  <thead>
    <tr class="border-b border-border/30">
      <th class="text-left p-4 text-[11px] font-semibold uppercase 
                 text-muted-foreground tracking-[0.08em]">Header</th>
    </tr>
  </thead>
  <tbody>
    <tr class="border-b border-border/30 hover:bg-accent/20">
      <td class="p-4 text-foreground">Data</td>
    </tr>
  </tbody>
</table>
```

### Modal
```vue
<div class="fixed inset-0 z-50 flex items-center justify-center 
           bg-black/40 backdrop-blur-sm p-4">
  <div class="surface-card p-8 w-full max-w-md">
    <h2 class="text-lg font-bold">Title</h2>
    <!-- content -->
  </div>
</div>
```

## Tag/Badge
```vue
<span class="text-xs font-medium text-muted-foreground bg-muted/40 
            px-2.5 py-1 rounded-lg">Tag</span>
```

## Financial Indicators
```vue
<!-- Gain/Positive -->
<span class="text-fin-gain">+5.2%</span>

<!-- Loss/Negative -->
<span class="text-fin-loss">-2.1%</span>

<!-- With Background -->
<div class="bg-fin-gain/10 text-fin-gain px-3 py-1 rounded-lg">
  +$1,234.50
</div>
```

## Spacing Scale
```
p-4 / px-4 / py-4    = 1rem (16px)
p-5 / px-5 / py-5    = 1.25rem (20px)
p-6 / px-6 / py-6    = 1.5rem (24px)
gap-4                = 1rem gap between items
gap-6                = 1.5rem gap between items
```

## Responsive
```
Mobile:  < 640px
Tablet:  640px - 1024px
Desktop: > 1024px

grid-cols-2 md:grid-cols-4          → 2 cols on mobile, 4 on desktop
p-4 lg:p-5                          → 4px padding, 5px on large
text-sm lg:text-lg                  → Responsive text size
hidden sm:table-cell                → Hide on mobile, show on tablet+
```

## Hover/Focus States
```
hover:scale-105                     → Subtle zoom
hover:bg-accent/20                  → Highlight background
hover:shadow-md                      → Elevated shadow
focus:border-primary                → Focus border color
focus:ring-2 focus:ring-primary/10  → Focus ring
transition-all duration-300         → Smooth transition
```

## Opacity Utilities
```
/10 (10%), /20 (20%), /30 (30%), /40 (40%), /50 (50%)
opacity-[0.03]                      → Very subtle (icon watermarks)
```

## Transitions
```
transition-all                      → All properties
transition-colors                   → Colors only
transition-transform                → Transform only
duration-200 / duration-300         → Animation length
ease-out                            → Natural motion
```

## Common Replacements

| Old | New |
|-----|-----|
| `border-gray-200` | `border-border` |
| `text-gray-900` | `text-foreground` |
| `text-gray-600` | `text-muted-foreground` |
| `bg-white` | `bg-card` |
| `bg-gray-50` | `bg-accent` |
| `border border-blue-500 bg-blue-500` | `border border-primary bg-primary` |
| `text-green-600` | `text-fin-gain` |
| `text-red-600` | `text-fin-loss` |
| `rounded-xl border shadow-sm` | `surface-card` |

## Dark Mode
Dark mode is automatic! When user selects dark theme:
- CSS custom properties update
- All colors adjust automatically
- No need for per-element `dark:` classes

## Tips & Tricks

1. **Use `.surface-card` first** - It handles borders, background, and shadows
2. **Pair text colors with backgrounds** - Use `text-foreground` on `bg-card`
3. **Use font-sans by default** - Only use `font-display` for headings and `font-mono` for values
4. **Responsive padding** - Use `p-4 lg:p-5` pattern for automatic scaling
5. **Opacity borders** - Use `border-border/30` for subtle borders instead of custom opacity
6. **Hover consistency** - Most interactive elements use `hover:bg-accent/20`
7. **Financial colors** - Always use `text-fin-gain` and `text-fin-loss` for consistency

---

**Print this page for quick reference during development!**
