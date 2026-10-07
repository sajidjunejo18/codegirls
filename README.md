# CodeGirls – GeneratePress Child

Child of GeneratePress. Design source: Figma "Redesign CG website 2026", homepage frame "UI".

## Structure
```
functions.php      loads /inc only
inc/enqueue.php    CSS/JS, font preload, editor stylesheet
inc/setup.php      palette (single source), supports, image sizes, pattern category "codegirls"
inc/hooks.php      GeneratePress hook customizations
assets/css/main.css  fonts, tokens, base, components
assets/fonts/      self-hosted variable woff2 (Parkinsans, Onest), latin subset
patterns/          one PHP file per homepage section
```

## Design tokens (`:root` in main.css)
| Token | Value |
|---|---|
| `--cg-green` | `#0A9D58` |
| `--cg-yellow` | `#F2B41B` |
| `--cg-navy` | `#223044` |
| `--cg-font-heading` | Parkinsans (600/700) |
| `--cg-font-body` | Onest (400/500/700) |
| `--cg-container` | 1596px (162px side gutters at 1920) |
| `--cg-shadow-card` | `0 24px 42px rgba(0,0,0,.04)` |

Breakpoints: 1200 / 1024 / 768 / 480.

## Decisions
- No `theme.json`: GeneratePress manages global colors and typography, and a theme.json would compete with them. The palette is registered in `inc/setup.php` instead.
- Patterns use GenerateBlocks v2 blocks only, with BEM class names.
