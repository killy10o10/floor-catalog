# Floor Catalog

A WordPress plugin for flooring retailers. Add your floors in WP Admin, then show them anywhere as a responsive, filterable product grid with one shortcode.

## Features

- **Floors** custom post type with its own admin menu, archive page (`/floors/`) and block-editor support
- **Material** taxonomy (Hardwood, Tile, Vinyl…) shown as a column in the admin list
- **Floor Details** fields: color, finish, price per sq ft, and a room-visualizer link
- **`[floor_catalog]` shortcode**: mobile-first card grid with filter buttons by material
- Accessible: real buttons with `aria-pressed`, a live region announcing results, visible focus styles, 44px touch targets
- Fast: CSS and JS load only on pages that use the shortcode; no jQuery
- Secure: nonces, capability checks, sanitized input, escaped output

## Usage

```
[floor_catalog]                       all floors
[floor_catalog material="hardwood"]   one material (use its slug)
[floor_catalog limit="6"]             at most 6 floors
```

## Install

1. Copy the `floor-catalog` folder into `wp-content/plugins/`.
2. In WP Admin go to **Plugins** and activate **Floor Catalog**.
3. Add a few floors under **Floor Catalog → Add New Floor** (set a featured image, a material and the details).
4. Put `[floor_catalog]` on any page.

## File structure

```
floor-catalog/
├── floor-catalog.php        Plugin header, constants, activation hooks
├── includes/
│   ├── post-type.php        Floors post type + Material taxonomy
│   ├── meta-box.php         Floor Details fields (save + security checks)
│   └── shortcode.php        [floor_catalog] grid
└── assets/
    ├── floor-catalog.css    Mobile-first grid styles
    └── floor-catalog.js     Filter buttons
```

## Requirements

WordPress 6.0+ and PHP 7.4+.

## License

GPL-2.0-or-later
