# CodeGirls – GeneratePress Child

Child of GeneratePress. Design source: Figma "Redesign CG website 2026", homepage frame "UI".

## Requires the "CodeGirls Core" plugin
Content types (Courses, Voices, Reports, Recognitions, Press, Blogs, Friends of Codegirls),
all forms and their handlers, Form Entries, notification email/SMTP, Site Stats and the one-time
content migrations live in `wp-content/plugins/codegirls-core`. This theme only presents them.
Without the plugin, wp-admin shows a notice and shortcodes like `[cg_courses]` appear as plain text.
Deploy order: upload the plugin (do NOT activate yet), deploy this theme version, then activate
the plugin straight away. The plugin and the old theme define the same functions, so activating
the plugin while the old theme is live fails with a fatal "Cannot redeclare" error.

## Structure
```
functions.php            loads /inc only
inc/enqueue.php          CSS/JS, font preload, editor stylesheet
inc/setup.php            palette (single source), supports, image sizes, pattern category "codegirls"
inc/hooks.php            GeneratePress hook customizations
inc/blog.php             cg_render_pattern(), posts-page filters, body class
inc/footer-settings.php  Customizer: footer locations, social links, footer menu
inc/hero-video.php       Customizer: homepage hero video, [cg_hero_media]
inc/homepage-only.php    demo mode (CG_HOMEPAGE_ONLY)
parts/                   header, footer, course popups markup (enroll-modals.php)
single-cg_*.php          story / press / blog detail templates
assets/css/main.css      fonts, tokens, base, components
assets/fonts/            self-hosted variable woff2 (Parkinsans, Onest), latin subset
patterns/                one PHP file per section
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
- Patterns use GenerateBlocks v2 blocks only (element, text, media), BEM class names, styles in main.css. Generated markup is saved as patterns/*.php.
- WordPress caches theme patterns per theme Version: bump `Version` in style.css (or run `wp_get_theme()->delete_pattern_cache()`) after editing a pattern.
- Header/footer: `parts/header.php`, `parts/footer.php`, swapped in via `inc/hooks.php`. Menu: Appearance → Menus → "Primary (header)".
- Contact form: `[cg_contact_form]` (plugin: `includes/forms/contact.php`), emails the Settings → General address.

## Pages (all built from patterns in /patterns, except the blog)
Home (front page), Hire a Codegirl, Become a Trainer, Our Courses, About, Support CodeGirls, Recognitions, Reports, In The Press, Voices (+ Ghulam Sakina story), Privacy Policy.
Blog: `home.php` (index with author/category/date/search filters) and `single.php` render real WordPress posts, so it stays dynamic.
Forms (plugin `includes/forms/`): `config.php` (hire, trainer, enroll, notify), `validation.php`, `renderer.php`, `handler.php`, and `contact.php` (general contact). All email the Settings → General address.
Menu: Appearance → Menus → "CodeGirls Primary" (assigned to "Primary (header)"), two levels deep.

## Demo mode
`CG_HOMEPAGE_ONLY` in functions.php: true = only the homepage is live (other URLs redirect home, their links are inert). Set to false to restore all pages.

## Course popups
"Enroll Now" (data-cg-enroll) and "Notify Me" (data-cg-notify) open native <dialog> popups from parts/enroll-modals.php; forms "enroll" and "notify" (plugin includes/forms/config.php, shown by includes/forms/popups.php) email the Settings -> General address. Slots and course name come from data attributes on each button (see patterns/courses*.php). Dropdown choices: cg_education_options() / cg_relation_options() (education/relationship lists are assumed, confirm with the client).

## Admin-managed content
- **Courses** (admin menu): each course = title, excerpt, illustration (featured image) + the "Course details" box (status, phase, start date, duration, mode, schedule slots, what you will learn). Cards on the homepage and Courses page are rendered by the `[cg_courses]` shortcode (plugin includes/post-types/courses.php); the Enroll popup reads the slots from here.
- **Form Entries** (admin menu): every form submission (contact, enroll, notify, hire, trainer) is saved first (plugin includes/entries/entries.php), then emailed best-effort. Filter by form, view details, download uploaded files (private folder), export CSV.
