<?php
/**
 * Plugin Name: ASP Syllabus
 * Plugin URI: https://github.com/tararauzumaki/asp-syllabus
 * Description: Create and manage multiple independent syllabus tables with PDF download and view functionality. Use shortcode [asp_syllabus id="X"] to display tables.
 * Version: 1.0.0
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
define('ASP_SYLLABUS_VERSION', '1.0.0');
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
        $category = isset($data['category']) ? $data['category'] : '';
        $class = isset($data['class']) ? $data['class'] : '';
        $download_pdf = isset($data['download_pdf']) ? $data['download_pdf'] : '';
        $view_pdf = isset($data['view_pdf']) ? $data['view_pdf'] : '';
        ?>
        <div class="asp-row" data-index="<?php echo esc_attr($index); ?>">
            <div class="asp-row-header">
                <span class="asp-row-handle dashicons dashicons-move"></span>
                <span class="asp-row-title"><?php _e('Row', 'asp-syllabus'); ?> #<span class="row-number"><?php echo esc_html($index + 1); ?></span></span>
                <button type="button" class="asp-remove-row" title="<?php _e('Remove Row', 'asp-syllabus'); ?>">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="asp-row-content">
                <div class="asp-field-group">
                    <label><?php _e('Category', 'asp-syllabus'); ?></label>
                    <input type="text" name="asp_rows[<?php echo esc_attr($index); ?>][category]" value="<?php echo esc_attr($category); ?>" placeholder="<?php _e('Enter category name', 'asp-syllabus'); ?>">
                </div>
                <div class="asp-field-group">
                    <label><?php _e('Class', 'asp-syllabus'); ?></label>
                    <input type="text" name="asp_rows[<?php echo esc_attr($index); ?>][class]" value="<?php echo esc_attr($class); ?>" placeholder="<?php _e('Enter class name', 'asp-syllabus'); ?>">
                </div>
                <div class="asp-field-group">
                    <label><?php _e('Download PDF', 'asp-syllabus'); ?></label>
                    <div class="asp-pdf-field">
                        <input type="text" class="asp-pdf-url" name="asp_rows[<?php echo esc_attr($index); ?>][download_pdf]" value="<?php echo esc_url($download_pdf); ?>" placeholder="<?php _e('PDF URL for download', 'asp-syllabus'); ?>" readonly>
                        <button type="button" class="button asp-upload-pdf" data-target="download">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Select PDF', 'asp-syllabus'); ?>
                        </button>
                    </div>
                </div>
                <div class="asp-field-group">
                    <label><?php _e('View PDF', 'asp-syllabus'); ?></label>
                    <div class="asp-pdf-field">
                        <input type="text" class="asp-pdf-url" name="asp_rows[<?php echo esc_attr($index); ?>][view_pdf]" value="<?php echo esc_url($view_pdf); ?>" placeholder="<?php _e('PDF URL for viewing', 'asp-syllabus'); ?>" readonly>
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
        
        // Save rows
        if (isset($_POST['asp_rows']) && is_array($_POST['asp_rows'])) {
            $rows = array();
            foreach ($_POST['asp_rows'] as $row) {
                $rows[] = array(
                    'category'     => sanitize_text_field($row['category']),
                    'class'        => sanitize_text_field($row['class']),
                    'download_pdf' => esc_url_raw($row['download_pdf']),
                    'view_pdf'     => esc_url_raw($row['view_pdf']),
                );
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
        $title = get_the_title($post_id);
        
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
                            <th><?php _e('Category', 'asp-syllabus'); ?></th>
                            <th><?php _e('Class', 'asp-syllabus'); ?></th>
                            <th><?php _e('Actions', 'asp-syllabus'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row) : ?>
                            <tr>
                                <td data-label="<?php _e('Category', 'asp-syllabus'); ?>"><?php echo esc_html($row['category']); ?></td>
                                <td data-label="<?php _e('Class', 'asp-syllabus'); ?>"><?php echo esc_html($row['class']); ?></td>
                                <td data-label="<?php _e('Actions', 'asp-syllabus'); ?>">
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
}

// Initialize the plugin
ASP_Syllabus::get_instance();
