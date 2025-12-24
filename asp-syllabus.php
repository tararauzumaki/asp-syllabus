<?php
/**
 * Plugin Name: ASP Syllabus
 * Plugin URI: https://github.com/tararauzumaki/asp-syllabus
 * Description: Create and manage multiple independent syllabus tables with PDF download and view functionality. Use shortcode [asp_syllabus id="X"] to display tables.
 * Version: 1.0.3
 * Author: Tanvir Rana Rabbi
 * Author URI: 
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: asp-syllabus
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ASP_SYLLABUS_VERSION', '1.0.2');
define('ASP_SYLLABUS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ASP_SYLLABUS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class
 */
class ASP_Syllabus {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_shortcode('asp_syllabus', array($this, 'shortcode_handler'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
        add_filter('manage_asp_syllabus_table_posts_columns', array($this, 'add_shortcode_column'));
        add_action('manage_asp_syllabus_table_posts_custom_column', array($this, 'render_shortcode_column'), 10, 2);
        add_filter('post_row_actions', array($this, 'add_preview_row_action'), 10, 2);
        add_action('template_redirect', array($this, 'preview_redirect'));
    }
    
    /**
     * Register Custom Post Type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Syllabus Tables', 'asp-syllabus'),
            'singular_name'      => __('Syllabus Table', 'asp-syllabus'),
            'menu_name'          => __('ASP Syllabus', 'asp-syllabus'),
            'add_new'            => __('Add New Table', 'asp-syllabus'),
            'add_new_item'       => __('Add New Syllabus Table', 'asp-syllabus'),
            'edit_item'          => __('Edit Syllabus Table', 'asp-syllabus'),
            'new_item'           => __('New Syllabus Table', 'asp-syllabus'),
            'view_item'          => __('View Syllabus Table', 'asp-syllabus'),
            'search_items'       => __('Search Syllabus Tables', 'asp-syllabus'),
            'not_found'          => __('No syllabus tables found', 'asp-syllabus'),
            'not_found_in_trash' => __('No syllabus tables found in Trash', 'asp-syllabus'),
        );
        
        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_icon'           => 'dashicons-list-view',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array('title'),
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
        );
        
        register_post_type('asp_syllabus_table', $args);
    }
    
    /**
     * Add Meta Boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'asp_syllabus_headers',
            __('Table Headers', 'asp-syllabus'),
            array($this, 'render_headers_box'),
            'asp_syllabus_table',
            'normal',
            'high'
        );
        
        add_meta_box(
            'asp_syllabus_rows',
            __('Syllabus Rows', 'asp-syllabus'),
            array($this, 'render_meta_box'),
            'asp_syllabus_table',
            'normal',
            'high'
        );
        
        add_meta_box(
            'asp_syllabus_shortcode',
            __('Shortcode', 'asp-syllabus'),
            array($this, 'render_shortcode_box'),
            'asp_syllabus_table',
            'side',
            'high'
        );
    }
    
    /**
     * Render Shortcode Info Box
     */
    public function render_shortcode_box($post) {
        ?>
        <div class="asp-shortcode-info">
            <p><strong><?php _e('Use this shortcode to display the table:', 'asp-syllabus'); ?></strong></p>
            <input type="text" readonly value='[asp_syllabus id="<?php echo esc_attr($post->ID); ?>"]' onclick="this.select();" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; background: #f9f9f9;">
            <p style="margin-top: 10px; font-size: 12px; color: #666;">
                <?php _e('Click to select, then copy and paste this shortcode into any post or page.', 'asp-syllabus'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render Table Headers Box
     */
    public function render_headers_box($post) {
        $headers = get_post_meta($post->ID, '_asp_syllabus_headers', true);
        if (!is_array($headers) || empty($headers)) {
            $headers = array('Category', 'Class');
        }
        ?>
        <div class="asp-headers-box">
            <p><?php _e('Define your table column headers (minimum 1 column):', 'asp-syllabus'); ?></p>
            <div class="asp-headers-container" id="asp-headers-container">
                <?php foreach ($headers as $index => $header) : ?>
                    <div class="asp-header-item">
                        <span class="asp-header-handle dashicons dashicons-move"></span>
                        <input type="text" name="asp_headers[]" value="<?php echo esc_attr($header); ?>" placeholder="<?php _e('Column Name', 'asp-syllabus'); ?>" required>
                        <?php if (count($headers) > 1) : ?>
                            <button type="button" class="button asp-remove-header" title="<?php _e('Remove Column', 'asp-syllabus'); ?>">
                                <span class="dashicons dashicons-no-alt"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="button button-secondary asp-add-header" style="margin-top: 10px;">
                <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                <?php _e('Add Column', 'asp-syllabus'); ?>
            </button>
            <p class="description" style="margin-top: 10px;">
                <?php _e('Note: A "Download" column with PDF upload functionality will be automatically added at the end.', 'asp-syllabus'); ?>
            </p>
        </div>
        <?php
    }
    
    /**
     * Render Meta Box
     */
    public function render_meta_box($post) {
        wp_nonce_field('asp_syllabus_save', 'asp_syllabus_nonce');
        
        $rows = get_post_meta($post->ID, '_asp_syllabus_rows', true);
        if (!is_array($rows)) {
            $rows = array();
        }
        ?>
        <div class="asp-syllabus-meta-box">
            <div class="asp-rows-container" id="asp-rows-container">
                <?php
                if (!empty($rows)) {
                    foreach ($rows as $index => $row) {
                        $this->render_row($index, $row);
                    }
                }
                ?>
            </div>
            <button type="button" class="button button-primary asp-add-row">
                <span class="dashicons dashicons-plus-alt" style="margin-top: 3px;"></span>
                <?php _e('Add New Row', 'asp-syllabus'); ?>
            </button>
        </div>
        
        <script type="text/html" id="asp-row-template">
            <?php $this->render_row('{{INDEX}}', array()); ?>
        </script>
        <?php
    }
    
    /**
     * Render Single Row
     */
    private function render_row($index, $data = array()) {
        global $post;
        $headers = get_post_meta($post->ID, '_asp_syllabus_headers', true);
        if (!is_array($headers) || empty($headers)) {
            $headers = array('Category', 'Class');
        }
        
        // Handle template placeholder for JavaScript
        $row_number = is_numeric($index) ? ($index + 1) : '{{ROW_NUMBER}}';
        ?>
        <div class="asp-row" data-index="<?php echo esc_attr($index); ?>">
            <div class="asp-row-header">
                <span class="asp-row-handle dashicons dashicons-move"></span>
                <span class="asp-row-title"><?php _e('Row', 'asp-syllabus'); ?> #<span class="row-number"><?php echo esc_html($row_number); ?></span></span>
                <button type="button" class="asp-remove-row" title="<?php _e('Remove Row', 'asp-syllabus'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="asp-row-content">
                <?php foreach ($headers as $col_index => $header) : 
                    $field_name = 'column_' . $col_index;
                    $field_value = isset($data[$field_name]) ? $data[$field_name] : '';
                ?>
                    <div class="asp-field-group">
                        <label><?php echo esc_html($header); ?></label>
                        <input type="text" name="asp_rows[<?php echo esc_attr($index); ?>][<?php echo esc_attr($field_name); ?>]" value="<?php echo esc_attr($field_value); ?>" placeholder="<?php echo esc_attr(sprintf(__('Enter %s', 'asp-syllabus'), $header)); ?>">
                    </div>
                <?php endforeach; ?>
                
                <!-- Always add Download column with PDF functionality -->
                <div class="asp-field-group asp-download-column">
                    <label><?php _e('Download PDF', 'asp-syllabus'); ?></label>
                    <div class="asp-pdf-field">
                        <input type="text" class="asp-pdf-url" name="asp_rows[<?php echo esc_attr($index); ?>][download_pdf]" value="<?php echo esc_url(isset($data['download_pdf']) ? $data['download_pdf'] : ''); ?>" placeholder="<?php _e('PDF URL for download', 'asp-syllabus'); ?>" readonly>
                        <button type="button" class="button asp-upload-pdf" data-target="download">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Select PDF', 'asp-syllabus'); ?>
                        </button>
                    </div>
                </div>
                <div class="asp-field-group asp-download-column">
                    <label><?php _e('View PDF', 'asp-syllabus'); ?></label>
                    <div class="asp-pdf-field">
                        <input type="text" class="asp-pdf-url" name="asp_rows[<?php echo esc_attr($index); ?>][view_pdf]" value="<?php echo esc_url(isset($data['view_pdf']) ? $data['view_pdf'] : ''); ?>" placeholder="<?php _e('PDF URL for viewing', 'asp-syllabus'); ?>" readonly>
                        <button type="button" class="button asp-upload-pdf" data-target="view">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Select PDF', 'asp-syllabus'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save Meta Box
     */
    public function save_meta_box($post_id) {
        // Check nonce
        if (!isset($_POST['asp_syllabus_nonce']) || !wp_verify_nonce($_POST['asp_syllabus_nonce'], 'asp_syllabus_save')) {
            return;
        }
        
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Check post type
        if (get_post_type($post_id) !== 'asp_syllabus_table') {
            return;
        }
        
        // Save headers
        if (isset($_POST['asp_headers']) && is_array($_POST['asp_headers'])) {
            $headers = array_map('sanitize_text_field', $_POST['asp_headers']);
            $headers = array_filter($headers); // Remove empty values
            if (count($headers) >= 1) {
                update_post_meta($post_id, '_asp_syllabus_headers', array_values($headers));
            }
        }
        
        // Save rows
        if (isset($_POST['asp_rows']) && is_array($_POST['asp_rows'])) {
            $rows = array();
            foreach ($_POST['asp_rows'] as $row) {
                $row_data = array();
                foreach ($row as $key => $value) {
                    // Handle PDF fields with URL sanitization
                    if ($key === 'download_pdf' || $key === 'view_pdf') {
                        $row_data[$key] = esc_url_raw($value);
                    } else {
                        $row_data[$key] = sanitize_text_field($value);
                    }
                }
                $rows[] = $row_data;
            }
            update_post_meta($post_id, '_asp_syllabus_rows', $rows);
        } else {
            delete_post_meta($post_id, '_asp_syllabus_rows');
        }
    }
    
    /**
     * Enqueue Admin Scripts
     */
    public function admin_enqueue_scripts($hook) {
        global $post_type;
        
        if (('post.php' === $hook || 'post-new.php' === $hook) && 'asp_syllabus_table' === $post_type) {
            // Enqueue WordPress media uploader
            wp_enqueue_media();
            
            // Enqueue jQuery UI Sortable
            wp_enqueue_script('jquery-ui-sortable');
            
            // Admin CSS
            wp_enqueue_style(
                'asp-syllabus-admin',
                ASP_SYLLABUS_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                ASP_SYLLABUS_VERSION
            );
            
            // Admin JS
            wp_enqueue_script(
                'asp-syllabus-admin',
                ASP_SYLLABUS_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                ASP_SYLLABUS_VERSION,
                true
            );
        }
    }
    
    /**
     * Enqueue Frontend Scripts (Conditional Loading)
     */
    public function frontend_enqueue_scripts() {
        global $post;
        
        // Check if shortcode is present in the content
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'asp_syllabus')) {
            wp_enqueue_style(
                'asp-syllabus-frontend',
                ASP_SYLLABUS_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                ASP_SYLLABUS_VERSION
            );
        }
    }
    
    /**
     * Shortcode Handler
     */
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'asp_syllabus');
        
        $post_id = intval($atts['id']);
        
        if (!$post_id || get_post_type($post_id) !== 'asp_syllabus_table') {
            return '<p class="asp-error">' . __('Invalid syllabus table ID.', 'asp-syllabus') . '</p>';
        }
        
        $rows = get_post_meta($post_id, '_asp_syllabus_rows', true);
        $headers = get_post_meta($post_id, '_asp_syllabus_headers', true);
        $title = get_the_title($post_id);
        
        if (!is_array($headers) || empty($headers)) {
            $headers = array('Category', 'Class');
        }
        
        if (!is_array($rows) || empty($rows)) {
            return '<p class="asp-error">' . __('No syllabus data found.', 'asp-syllabus') . '</p>';
        }
        
        // Start output buffering
        ob_start();
        ?>
        <div class="asp-syllabus-wrapper">
            <?php if (!empty($title)) : ?>
                <h2 class="asp-syllabus-title"><?php echo esc_html($title); ?></h2>
            <?php endif; ?>
            
            <div class="asp-table-responsive">
                <table class="asp-syllabus-table">
                    <thead>
                        <tr>
                            <?php foreach ($headers as $header) : ?>
                                <th><?php echo esc_html($header); ?></th>
                            <?php endforeach; ?>
                            <th><?php _e('Download', 'asp-syllabus'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <?php foreach ($headers as $col_index => $header) : 
                                    $field_name = 'column_' . $col_index;
                                    $value = isset($row[$field_name]) ? $row[$field_name] : '';
                                ?>
                                    <td data-label="<?php echo esc_attr($header); ?>"><?php echo esc_html($value); ?></td>
                                <?php endforeach; ?>
                                
                                <td data-label="<?php _e('Download', 'asp-syllabus'); ?>">
                                    <div class="asp-button-group">
                                        <?php if (!empty($row['download_pdf'])) : ?>
                                            <a href="<?php echo esc_url($row['download_pdf']); ?>" class="asp-btn asp-btn-download" download>
                                                <span class="dashicons dashicons-download"></span>
                                                <?php _e('Download', 'asp-syllabus'); ?>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($row['view_pdf'])) : ?>
                                            <a href="<?php echo esc_url($row['view_pdf']); ?>" class="asp-btn asp-btn-view" target="_blank">
                                                <span class="dashicons dashicons-visibility"></span>
                                                <?php _e('View', 'asp-syllabus'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Add Shortcode Column to Admin Listing
     */
    public function add_shortcode_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['shortcode'] = __('Shortcode', 'asp-syllabus');
            }
        }
        return $new_columns;
    }
    
    /**
     * Add Preview Row Action
     */
    public function add_preview_row_action($actions, $post) {
        if ($post->post_type === 'asp_syllabus_table') {
            $preview_url = add_query_arg(array(
                'p' => $post->ID,
                'asp_preview' => 1
            ), home_url('/'));
            
            $actions['asp_preview'] = '<a href="' . esc_url($preview_url) . '" target="_blank" title="' . esc_attr__('Preview table in new window', 'asp-syllabus') . '">' . __('Preview', 'asp-syllabus') . '</a>';
        }
        return $actions;
    }
    
    /**
     * Render Shortcode Column Content
     */
    public function render_shortcode_column($column, $post_id) {
        if ($column === 'shortcode') {
            $shortcode = '[asp_syllabus id="' . $post_id . '"]';
            echo '<input type="text" readonly value="' . esc_attr($shortcode) . '" onclick="this.select();" style="width: 100%; padding: 4px 8px; border: 1px solid #ddd; border-radius: 3px; font-family: monospace; font-size: 12px; background: #f9f9f9; cursor: pointer;" title="' . esc_attr__('Click to select and copy', 'asp-syllabus') . '">';
        }
    }
    
    /**
     * Handle Preview Redirect
     */
    public function preview_redirect() {
        if (isset($_GET['asp_preview']) && isset($_GET['p'])) {
            $post_id = intval($_GET['p']);
            
            if (get_post_type($post_id) === 'asp_syllabus_table' && current_user_can('edit_post', $post_id)) {
                // Load frontend styles
                wp_enqueue_style(
                    'asp-syllabus-frontend',
                    ASP_SYLLABUS_PLUGIN_URL . 'assets/css/frontend.css',
                    array(),
                    ASP_SYLLABUS_VERSION
                );
                
                // Output preview page
                wp_head();
                echo '<div style="padding: 20px; max-width: 1200px; margin: 0 auto;">';
                echo '<h1 style="margin-bottom: 20px;">Preview: ' . esc_html(get_the_title($post_id)) . '</h1>';
                echo do_shortcode('[asp_syllabus id="' . $post_id . '"]');
                echo '</div>';
                wp_footer();
                exit;
            }
        }
    }
}

// Initialize the plugin
ASP_Syllabus::get_instance();
