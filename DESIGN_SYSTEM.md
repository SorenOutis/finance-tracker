# Finance Tracker - Design System

This document outlines the design system applied to the Finance Tracker application, inspired by a modern financial dashboard design.

## Color Palette

### Light Mode
The application uses an OKLCH color space for modern perceptual uniformity:

- **Background**: Pure white (`oklch(1 0 0)`)
- **Foreground**: Near black (`oklch(0.145 0 0)`)
- **Primary**: Dark (`oklch(0.205 0 0)`)
- **Card**: White with subtle borders
- **Accent**: Light gray (`oklch(0.97 0 0)`)
- **Muted**: Light gray (`oklch(0.97 0 0)`)

### Dark Mode
- **Background**: Near black (`oklch(0.145 0 0)`)
- **Foreground**: White (`oklch(0.985 0 0)`)
- **Primary**: White for high contrast
- **Card**: Dark surface (`oklch(0.145 0 0)`)
- **Accent**: Slightly lighter (`oklch(0.269 0 0)`)

### Financial Indicators
- **Gain**: Teal (`oklch(0.76 0.16 162)`) - Used for positive returns, increases
- **Loss**: Red (`oklch(0.62 0.22 18)`) - Used for negative returns, decreases

### Chart Colors
- **Chart 1**: Orange/Amber (`oklch(0.646 0.222 41.116)`)
- **Chart 2**: Cyan/Teal (`oklch(0.6 0.118 184.704)`)
- **Chart 3**: Blue (`oklch(0.398 0.07 227.392)`)
- **Chart 4**: Yellow/Gold (`oklch(0.828 0.189 84.429)`)
- **Chart 5**: Purple/Magenta (`oklch(0.769 0.188 70.08)`)

## Typography

### Font Stack
- **Sans-serif**: Instrument Sans (with fallbacks)
- **Mono**: System monospace fonts

### Text Hierarchy
- **Display/Headings**: Bold, uppercase tracking for major titles
- **Body**: Regular weight for content
- **Labels**: Semibold uppercase (11px) with letter-spacing: 0.08em
- **Values**: Monospace font for financial figures

## Components

### Surface Classes
```css
.surface-card
/* Rounded, bordered card with elevated shadow */
/* Used for main content containers */

.surface-elevated
/* Compact elevated surface with backdrop blur */
/* Used for tooltips and floating elements */
```

### Card Styling
- **Border Radius**: 0.625rem (10px)
- **Border Color**: Transparent borders using opacity
- **Shadow**: Multi-layer shadow system for depth
  ```
  0px 0px 0px 1px (outline)
  0px 1px 1px -0.5px (shallow)
  0px 3px 3px -1.5px (light)
  0px 6px 6px -3px (medium)
  0px 12px 12px -6px (deep)
  0px 24px 24px -12px (very deep)
  ```

### Button Styles

#### Primary Button
```html
class="rounded-lg border border-primary bg-primary px-5 py-2.5 
       text-sm font-semibold text-primary-foreground shadow-sm 
       transition-all duration-200 hover:bg-primary/90 hover:shadow-md"
```

#### Secondary Button
```html
class="rounded-lg border border-border bg-card px-5 py-2.5 
       text-sm font-semibold text-foreground shadow-sm 
       transition-all duration-200 hover:bg-accent/50 hover:shadow-md"
```

#### Subtle Button
```html
class="rounded-lg border border-border bg-card px-4 py-2.5 
       text-sm font-semibold text-foreground transition-all 
       duration-200 hover:bg-accent/50"
```

### Input Fields
```html
class="rounded-lg border border-border bg-muted/40 px-4 py-2.5 
       text-sm transition-all focus:border-primary 
       focus:outline-none focus:ring-2 focus:ring-primary/10"
```

## Layout Patterns

### KPI/Stat Cards
- **Grid**: 2 columns (mobile), 4 columns (desktop)
- **Padding**: 1rem (4px) on small, 1.25rem (5px) on large
- **Icon Background**: Subtle opacity background (3%) in top-right
- **Hover Effect**: `hover:scale-105` with transition

### Entry Lists
- **Divider**: `divide-y divide-border/30`
- **Hover State**: `hover:bg-accent/20`
- **Entry Spacing**: `py-4` with `first:pt-0`
- **Category Tags**: `bg-muted/40 px-2.5 py-1 rounded-lg`

### Modals
- **Backdrop**: `bg-black/40` with `backdrop-blur-sm`
- **Modal Surface**: `surface-card` styling
- **Width**: `max-w-md` for standard forms
- **Padding**: 2rem (8px)

## Spacing System

| Size | Value |
|------|-------|
| xs   | 0.25rem (4px) |
| sm   | 0.5rem (8px) |
| md   | 1rem (16px) |
| lg   | 1.5rem (24px) |
| xl   | 2rem (32px) |

## Animation & Transitions

### Default Transitions
- **Duration**: 200ms to 300ms
- **Timing**: `ease-out` for natural motion
- **Hover Effects**: Subtle scale, color, and shadow changes

### Card Interactions
- Hover: `scale-105` with shadow enhancement
- Entries: `hover:bg-accent/20` with text color change
- Buttons: Background opacity change with shadow

## Responsive Breakpoints

- **Mobile**: < 640px (single column for cards)
- **Tablet**: 640px - 1024px (2 columns)
- **Desktop**: > 1024px (4 columns)

## Accessibility

- All colors have sufficient contrast ratios (WCAG AA)
- Focus states use `ring-2 ring-primary/10` with outline
- Semantic HTML structure maintained
- Icon buttons have aria-labels
- Form labels are properly associated with inputs

## Implementation Examples

### Stats Card
```vue
<div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:gap-6">
  <div class="group surface-card p-4 lg:p-5 overflow-hidden relative 
              hover:scale-105 transition-transform duration-300">
    <div class="absolute top-0 right-0 w-24 h-24 opacity-[0.03] 
                pointer-events-none">
      <Icon class="size-24 -translate-y-4 translate-x-4" />
    </div>
    <p class="text-[11px] font-semibold tracking-[0.08em] uppercase 
              text-muted-foreground mb-2.5">Label</p>
    <p class="text-2xl lg:text-3xl font-bold text-foreground font-mono">Value</p>
  </div>
</div>
```

### Entry List Item
```vue
<div class="group flex items-start justify-between py-4 transition-colors 
            first:pt-0 hover:bg-accent/20">
  <div class="flex-1">
    <p class="font-semibold text-foreground text-[13px]">$1,234.50</p>
    <div class="mt-2 flex flex-wrap gap-2">
      <span class="text-xs font-medium text-muted-foreground">2026-03-03</span>
      <span class="text-xs font-medium text-muted-foreground bg-muted/40 
                   px-2.5 py-1 rounded-lg">Salary</span>
    </div>
  </div>
  <button class="ml-4 rounded-lg p-1.5 text-muted-foreground opacity-0 
                 transition-all hover:bg-destructive/10 hover:text-destructive 
                 group-hover:opacity-100">
    ✕
  </button>
</div>
```

## Migration from Previous Design

The following changes were made:
1. **Color Space**: HSL → OKLCH for better perceptual uniformity
2. **Shadows**: Simplified to multi-layer approach
3. **Typography**: Adjusted font sizes and tracking for hierarchy
4. **Components**: Unified `.surface-card` class for consistency
5. **Interactions**: Enhanced hover states with scale and color changes
6. **Dark Mode**: Updated color values for better contrast

## Maintenance Notes

- All color variables are defined in `:root` and `.dark` selectors
- Utility classes in `@layer utilities` can be extended
- The design uses CSS custom properties for easy theming
- Financial colors (gain/loss) are available as `--fin-gain` and `--fin-loss`
