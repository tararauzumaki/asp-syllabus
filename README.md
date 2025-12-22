# ASP Syllabus

A modern, high-performance WordPress plugin for creating and managing multiple syllabus tables with PDF download and view functionality.

![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![License](https://img.shields.io/badge/License-GPL%20v2-green.svg)

## 📋 Features

- **Multiple Independent Tables**: Create unlimited syllabus tables, each with a unique shortcode
- **Modern Admin UI**: Clean, card-based interface with drag-and-drop row sorting
- **PDF Management**: Easy PDF upload for download and view buttons using WordPress Media Library
- **Responsive Design**: Mobile-friendly tables that adapt to any screen size
- **Performance Optimized**: Conditional asset loading - CSS/JS only loads when shortcode is present
- **Security First**: All inputs sanitized, all outputs escaped, proper nonce verification
- **Shortcode Ready**: Simple shortcode system for embedding tables anywhere

## 🎯 Use Cases

- Educational institutions managing course syllabi
- Training centers organizing class materials
- Organizations distributing curriculum documents
- Any site needing structured PDF distribution tables

## 🚀 Installation

### Method 1: Manual Installation

1. Download the plugin folder
2. Upload to `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Navigate to **ASP Syllabus** in the admin menu

### Method 2: ZIP Upload

1. Compress the plugin folder into a `.zip` file
2. Go to WordPress Admin → Plugins → Add New → Upload Plugin
3. Upload the ZIP file and click "Install Now"
4. Activate the plugin

## 📖 Usage

### Creating a Syllabus Table

1. Go to **ASP Syllabus** → **Add New Table**
2. Enter a **Table Title** (this will be the main heading on the frontend)
3. Click **Add New Row** to add data rows
4. Fill in the fields for each row:
   - **Category**: Subject category or topic name
   - **Class**: Class name or level
   - **Download PDF**: Upload a PDF file for downloading
   - **View PDF**: Upload a PDF file for viewing in browser
5. **Drag and drop** rows to reorder them
6. Click **Publish** to save the table

### Using the Shortcode

After publishing, you'll see a shortcode in the sidebar:

```
[asp_syllabus id="5"]
```

Copy and paste this shortcode into any:
- Post
- Page
- Widget (if your theme supports shortcodes in widgets)
- Custom post type

### Managing Tables

- **Edit**: Click on any table to modify its content
- **Delete**: Move to trash from the tables list
- **Reorder Rows**: Drag rows by the handle icon in the admin panel
- **Remove Rows**: Click the trash icon on any row

## 🎨 Frontend Display

The plugin renders a beautiful, responsive table with:

- **Header**: Dark gray background (#5a6268) with white text
- **Download Button**: Blue button that triggers PDF download
- **View Button**: Blue button that opens PDF in a new tab
- **Mobile Responsive**: Stacks into cards on mobile devices
- **Hover Effects**: Smooth transitions and visual feedback

## 🛠️ Technical Details

### File Structure

```
asp-syllabus/
├── asp-syllabus.php          # Main plugin file
├── assets/
│   ├── css/
│   │   ├── admin.css         # Admin panel styles
│   │   └── frontend.css      # Frontend table styles
│   └── js/
│       └── admin.js          # Admin functionality
└── README.md                 # This file
```

### Custom Post Type

- **Post Type**: `asp_syllabus_table`
- **Supports**: Title only
- **Public**: No (admin only)
- **Menu Icon**: `dashicons-list-view`

### Data Storage

Row data is stored as a JSON array in post meta:
- **Meta Key**: `_asp_syllabus_rows`
- **Structure**: Array of objects containing category, class, download_pdf, and view_pdf fields

### Hooks & Filters

The plugin uses standard WordPress hooks:
- `init` - Register custom post type
- `add_meta_boxes` - Add meta boxes
- `save_post` - Save meta box data
- `admin_enqueue_scripts` - Load admin assets
- `wp_enqueue_scripts` - Conditionally load frontend assets

## 🔒 Security Features

- ✅ Nonce verification on all form submissions
- ✅ Capability checks for user permissions
- ✅ Input sanitization using `sanitize_text_field()` and `esc_url_raw()`
- ✅ Output escaping using `esc_html()`, `esc_attr()`, and `esc_url()`
- ✅ Direct file access prevention
- ✅ SQL injection prevention through WordPress APIs

## ⚡ Performance Features

- **Conditional Loading**: Assets only load on pages with the shortcode
- **Minification Ready**: CSS and JS files are structured for easy minification
- **Database Optimized**: Single meta key stores all row data
- **No External Dependencies**: Uses native WordPress and jQuery UI

## 🎨 Customization

### Styling

You can customize the appearance by adding CSS to your theme:

```css
/* Custom button colors */
.asp-btn-download {
    background: #your-color !important;
}

/* Custom table header */
.asp-syllabus-table thead {
    background: #your-color !important;
}
```

### Button Text

Modify button labels using WordPress translation filters or directly in the shortcode handler.

## 🌐 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 📱 Mobile Responsive

The table automatically adapts to mobile devices:
- **Desktop**: Traditional table layout
- **Tablet**: Scrollable table
- **Mobile**: Stacked card layout with labels

## 🤝 Contributing

Contributions are welcome! To contribute:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 Changelog

### Version 1.0.0
- Initial release
- Custom post type for syllabus tables
- Repeater field functionality
- WordPress media uploader integration
- Drag-and-drop row sorting
- Responsive frontend table
- Conditional asset loading
- Complete sanitization and escaping

### Version 1.0.1
- Fix "Add New Row" button

## 👤 Author

**Tanvir Rana Rabbi**

## 📄 License

This plugin is licensed under the GPL v2 or later.

```
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

## 🐛 Support

For bug reports and feature requests, please use the [GitHub Issues](https://github.com/yourusername/asp-syllabus/issues) page.

## ⭐ Show Your Support

If this plugin helped you, please give it a ⭐ on GitHub!

---

**Made with ❤️ for the WordPress community**

