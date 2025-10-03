<?php
/**
 * Plugin Name: Linmania Blog e Inscripciones
 * Plugin URI: https://linmania.com
 * Description: Plugin para gestionar inscripciones de torneos y eventos deportivos con integración a WooCommerce.
 * Version: 1.0.0
 * Author: Benjamin Oscco Arias
 * Author URI: https://linmania.com
 * Text Domain: linmania-blog-inscripciones
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LINMANIA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('LINMANIA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('LINMANIA_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once LINMANIA_PLUGIN_PATH . 'includes/export-functions.php';

// Check if WooCommerce is active
add_action('admin_init', 'linmania_check_woocommerce');

function linmania_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', 'linmania_woocommerce_notice');
    }
}

function linmania_woocommerce_notice() {
    echo '<div class="notice notice-error"><p><strong>Linmania Blog e Inscripciones</strong> requiere que WooCommerce esté instalado y activado.</p></div>';
}

// Main plugin class
class LinmaniaBlogInscripciones {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_linmania_get_inscriptions', array($this, 'ajax_get_inscriptions'));
        add_action('wp_ajax_linmania_get_filter_options', array($this, 'ajax_get_filter_options'));
        add_action('wp_ajax_linmania_get_player_details', array($this, 'ajax_get_player_details'));
        add_action('wp_ajax_linmania_export_excel', array($this, 'export_excel'));
        add_action('wp_ajax_linmania_export_pdf', array($this, 'export_pdf'));
        // Test data actions removed - no longer needed
        add_action('wp_ajax_linmania_debug_database', array($this, 'debug_database'));
        add_action('wp_ajax_linmania_test_orders', array($this, 'test_orders_query'));
    }
    
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('linmania-blog-inscripciones', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function add_admin_menu() {
        add_menu_page(
            'Inscripciones',
            'Inscripciones',
            'manage_options',
            'linmania-inscripciones',
            array($this, 'inscriptions_page'),
            'dashicons-groups',
            30
        );
    }
    
    public function enqueue_admin_scripts($hook) {
        if ($hook != 'toplevel_page_linmania-inscripciones') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_style('linmania-admin-css', LINMANIA_PLUGIN_URL . 'assets/css/admin.css', array(), LINMANIA_PLUGIN_VERSION);
        wp_enqueue_script('linmania-admin-js', LINMANIA_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), LINMANIA_PLUGIN_VERSION, true);
        
        wp_localize_script('linmania-admin-js', 'linmania_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('linmania_nonce')
        ));
    }
    
    public function inscriptions_page() {
        ?>
        <div class="wrap">
            <h1>Inscripciones de Torneos</h1>
            
            <!-- Filtros -->
            <div class="linmania-filters">
                <div class="filter-group">
                    <label for="categoria-filter">CATEGORÍAS:</label>
                    <select id="categoria-filter">
                        <option value="">Todas las categorías</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="local-filter">LOCAL:</label>
                    <select id="local-filter">
                        <option value="">Todos los locales</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="equipo-filter">EQUIPO:</label>
                    <input type="text" id="equipo-filter" placeholder="EQUIPO">
                </div>
                
                <div class="export-buttons">
                    <a href="#" id="export-excel" class="button">EXCEL</a>
                    <a href="#" id="export-pdf" class="button">PDF</a>
                </div>
            </div>
            
            <!-- Controles de paginación y búsqueda -->
            <div class="linmania-controls">
                <div class="pagination-control">
                    <label>Mostrar 
                        <select id="records-per-page">
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select> registros
                    </label>
                </div>
                
                <div class="search-control">
                    <label>Buscar:</label>
                    <input type="text" id="general-search" placeholder="Buscar...">
                </div>
            </div>
            
            
            <!-- Tabla de inscripciones -->
            <div class="linmania-table-container">
                <table class="wp-list-table widefat fixed striped" id="inscriptions-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="sortable" data-sort="ID">↑ ID ORDEN</th>
                            <th class="sortable" data-sort="date">FECHA</th>
                            <th class="sortable" data-sort="categoria">CATEGORÍAS</th>
                            <th class="sortable" data-sort="local">LOCAL</th>
                            <th class="sortable" data-sort="equipo">EQUIPO</th>
                        </tr>
                    </thead>
                    <tbody id="inscriptions-tbody">
                        <!-- Los datos se cargarán via AJAX -->
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="linmania-pagination">
                <div class="pagination-info">
                    <span id="pagination-info">Mostrando registros del 1 al 5 de un total de 5 registros</span>
                </div>
                <div class="pagination-links" id="pagination-links">
                    <!-- Los enlaces de paginación se generarán dinámicamente -->
                </div>
            </div>
        </div>
        
        <!-- Modal para detalles de jugadores -->
        <div id="player-details-modal" class="linmania-modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Detalles de Jugadores</h3>
                <div id="player-details-content">
                    <!-- Contenido de jugadores -->
                </div>
            </div>
        </div>
        <?php
    }
    
    public function ajax_get_inscriptions() {
        // Verificar nonce
        if (!wp_verify_nonce($_POST['nonce'], 'linmania_nonce')) {
            wp_send_json_error('Nonce verification failed');
        }
        
        $page = intval($_POST['page']) ?: 1;
        $per_page = intval($_POST['per_page']) ?: 25;
        $search = sanitize_text_field($_POST['search']) ?: '';
        $categoria = sanitize_text_field($_POST['categoria']) ?: '';
        $local = sanitize_text_field($_POST['local']) ?: '';
        $equipo = sanitize_text_field($_POST['equipo']) ?: '';
        $sort_by = sanitize_text_field($_POST['sort_by']) ?: 'ID';
        $sort_order = sanitize_text_field($_POST['sort_order']) ?: 'DESC';
        
        
        
        // Limpiar filtros vacíos o con valores por defecto
        if ($categoria === 'Nothing selected' || $categoria === '' || $categoria === 'Todas las categorías') {
            $categoria = '';
        }
        if ($local === 'Nothing selected' || $local === '' || $local === 'Todos los locales') {
            $local = '';
        }
        if ($equipo === 'Nothing selected' || $equipo === '') {
            $equipo = '';
        }
        
        
        
        
        // Verificar que WooCommerce esté activo
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce no está activo');
        }
        
        // Primero, vamos a obtener TODAS las órdenes sin filtros para debug
        // Intentar con ambos tipos de post
        $debug_args = array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => 'any', // Cambiar a 'any' para ver todos los estados
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        $debug_query = new WP_Query($debug_args);
        $all_orders = $debug_query->posts;
        
        // También probar con get_posts directamente
        $direct_orders = get_posts(array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        
        // Verificar si WooCommerce está realmente activo
        $wc_active = class_exists('WooCommerce');
        $wc_version = $wc_active ? WC()->version : 'No disponible';
        
        // Verificar si hay órdenes usando la función de WooCommerce
        $wc_orders_count = 0;
        if ($wc_active && function_exists('wc_get_orders')) {
            $wc_orders = wc_get_orders(array(
                'limit' => -1,
                'return' => 'ids'
            ));
            $wc_orders_count = count($wc_orders);
        }
        
        // Usar la consulta que sabemos que funciona: shop_order_placehold_any
        // Si se filtra por categoría, necesitamos obtener todos los registros primero
        $need_all_records = (!empty($categoria) && $categoria !== '' && $categoria !== 'Nothing selected');
        
        $args = array(
            'post_type' => 'shop_order_placehold', // Solo el tipo que funciona
            'post_status' => 'any', // Usar 'any' para capturar todos los estados
            'posts_per_page' => $need_all_records ? -1 : $per_page,
            'paged' => $need_all_records ? 1 : $page,
            'orderby' => $this->get_orderby_field($sort_by),
            'order' => $sort_order
        );
        
        // Obtener todas las órdenes y filtrar después
        // Esto evita problemas con meta_query
        
        $query = new WP_Query($args);
        $inscriptions = array();
        
        
        
        if ($query->have_posts()) {
            $processed_count = 0;
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                $order = wc_get_order($order_id);
                
                if (!$order) continue;
                
                // Obtener fecha de forma segura
                $date_created = $order->get_date_created();
                $formatted_date = $date_created ? $date_created->date('d/m/Y h:i A') : 'N/A';
                
                // Obtener información del cliente si está disponible
                $customer_name = '';
                if ($order->get_billing_first_name() || $order->get_billing_last_name()) {
                    $customer_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
                } elseif ($order->get_billing_company()) {
                    $customer_name = $order->get_billing_company();
                }
                
                // Obtener categorías del producto de la orden
                $product_categories = array();
                $items = $order->get_items();
                foreach ($items as $item) {
                    $product = $item->get_product();
                    if ($product) {
                        $categories = wp_get_post_terms($product->get_id(), 'product_cat');
                        foreach ($categories as $category) {
                            $product_categories[] = $category->name;
                        }
                    }
                }
                $orden_categoria = !empty($product_categories) ? implode(', ', array_unique($product_categories)) : 'Sin categoría';
                
                $local_value = $order->get_meta('local') ?: 'Sin local';
                
                $inscriptions[] = array(
                    'ID' => $order_id,
                    'date' => $formatted_date,
                    'categoria' => $orden_categoria,
                    'local' => $local_value,
                    'equipo' => $order->get_meta('nombre_equipo') ?: ($customer_name ?: 'Sin equipo'),
                    'jugador1' => $order->get_meta('nombre_jugador_1') ?: '',
                    'jugador2' => $order->get_meta('nombre_jugador_2') ?: '',
                    'jugador3' => $order->get_meta('nombre_jugador_3') ?: '',
                    'jugador4' => $order->get_meta('nombre_jugador_4') ?: '',
                    'telefono1' => $order->get_meta('telefono_jugador_1') ?: '',
                    'telefono2' => $order->get_meta('telefono_jugador_2') ?: '',
                    'telefono3' => $order->get_meta('telefono_jugador_3') ?: '',
                    'telefono4' => $order->get_meta('telefono_jugador_4') ?: '',
                    'suplentes' => $order->get_meta('suplentes') ?: ''
                );
                $processed_count++;
            }
        }
        
        wp_reset_postdata();
        
        
        // Filtrar por categoría, local y equipo después de obtener los datos
        $total_records = $query->found_posts;
        $total_pages = $query->max_num_pages;
        
        // Aplicar filtros después de obtener los datos
        $filtered_inscriptions = $inscriptions;
        
        // Filtro de categoría
        if (!empty($categoria) && $categoria !== '' && $categoria !== 'Nothing selected' && $categoria !== 'Todas las categorías') {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                if (stripos($inscription['categoria'], $categoria) !== false) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        // Filtro de local
        if (!empty($local) && $local !== '' && $local !== 'Nothing selected' && $local !== 'Todos los locales') {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                if (stripos($inscription['local'], $local) !== false) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        // Filtro de equipo
        if (!empty($equipo) && $equipo !== '' && $equipo !== 'Nothing selected') {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                if (stripos($inscription['equipo'], $equipo) !== false) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        // Recalcular total y páginas después del filtro
        $total_records = count($filtered_inscriptions);
        $total_pages = ceil($total_records / $per_page);
        
        // Aplicar paginación manual
        $offset = ($page - 1) * $per_page;
        $inscriptions = array_slice($filtered_inscriptions, $offset, $per_page);
        
        
        wp_send_json_success(array(
            'inscriptions' => $inscriptions,
            'total' => $total_records,
            'pages' => $total_pages,
            'current_page' => $page,
            'debug' => array(
                'query_args' => $args,
                'found_posts' => $query->found_posts,
                'post_count' => $query->post_count,
                'all_orders_count' => count($all_orders),
                'all_orders' => array_slice($all_orders, 0, 10), // Primeros 10 IDs para debug
                'direct_orders_count' => count($direct_orders),
                'direct_orders' => array_slice($direct_orders, 0, 10),
                'wc_active' => $wc_active,
                'wc_version' => $wc_version,
                'wc_orders_count' => $wc_orders_count,
                'debug_query_info' => array(
                    'found_posts' => $debug_query->found_posts,
                    'post_count' => $debug_query->post_count,
                    'max_num_pages' => $debug_query->max_num_pages
                ),
                'final_query_args' => $args,
                'final_query_found_posts' => $query->found_posts,
                'final_query_post_count' => $query->post_count
            )
        ));
    }
    
    private function get_orderby_field($sort_by) {
        switch ($sort_by) {
            case 'ID':
                return 'ID';
            case 'date':
                return 'date';
            case 'categoria':
                return 'meta_value';
            case 'local':
                return 'meta_value';
            case 'equipo':
                return 'meta_value';
            default:
                return 'ID';
        }
    }
    
    public function export_excel() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        $filters = array(
            'search' => sanitize_text_field($_GET['search']),
            'categoria' => sanitize_text_field($_GET['categoria']),
            'local' => sanitize_text_field($_GET['local']),
            'equipo' => sanitize_text_field($_GET['equipo']),
            'sort_by' => sanitize_text_field($_GET['sort_by']),
            'sort_order' => sanitize_text_field($_GET['sort_order'])
        );
        
        $data = LinmaniaExport::get_inscriptions_data($filters);
        LinmaniaExport::export_to_excel($data, 'inscripciones');
    }
    
    public function ajax_get_filter_options() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        // Usar exactamente la misma lógica que la tabla principal
        $args = array(
            'post_type' => 'shop_order_placehold',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        $locales = array();
        $categorias = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                $order = wc_get_order($order_id);
                
                if (!$order) continue;
                
                // Obtener local usando la misma lógica que la tabla
                $local = $order->get_meta('local');
                if (!empty($local) && $local !== 'Sin local') {
                    $locales[] = $local;
                }
                
                // Obtener categorías del producto
                $items = $order->get_items();
                foreach ($items as $item) {
                    $product = $item->get_product();
                    if ($product) {
                        $product_categories = wp_get_post_terms($product->get_id(), 'product_cat');
                        foreach ($product_categories as $category) {
                            $categorias[] = $category->name;
                        }
                    }
                }
            }
        }
        
        wp_reset_postdata();
        
        // Eliminar duplicados y ordenar
        $locales = array_unique($locales);
        sort($locales);
        $categorias = array_unique($categorias);
        sort($categorias);
        
        wp_send_json_success(array(
            'locales' => $locales,
            'categorias' => $categorias,
            'debug' => array(
                'locales_count' => count($locales),
                'categorias_count' => count($categorias)
            )
        ));
    }
    
    public function ajax_get_player_details() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        $order_id = intval($_POST['order_id']);
        
        if (!$order_id) {
            wp_send_json_error('ID de orden inválido');
        }
        
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Orden no encontrada');
        }
        
        $player_details = array(
                    'jugador1' => $order->get_meta('nombre_jugador_1'),
                    'jugador2' => $order->get_meta('nombre_jugador_2'),
                    'jugador3' => $order->get_meta('nombre_jugador_3'),
                    'jugador4' => $order->get_meta('nombre_jugador_4'),
                    'telefono1' => $order->get_meta('telefono_jugador_1'),
                    'telefono2' => $order->get_meta('telefono_jugador_2'),
                    'telefono3' => $order->get_meta('telefono_jugador_3'),
                    'telefono4' => $order->get_meta('telefono_jugador_4'),
                    'suplentes' => $order->get_meta('suplentes')
        );
        
        wp_send_json_success($player_details);
    }
    
    private function get_unique_meta_values($meta_key) {
        global $wpdb;
        
        // Consulta más robusta para obtener valores únicos
        $results = $wpdb->get_col($wpdb->prepare("
            SELECT DISTINCT pm.meta_value 
            FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
            WHERE pm.meta_key = %s 
            AND p.post_type = 'shop_order_placehold'
            AND pm.meta_value IS NOT NULL
            AND pm.meta_value != ''
            AND pm.meta_value != '0'
            ORDER BY pm.meta_value ASC
        ", $meta_key));
        
        // Si no hay resultados, probar con diferentes variaciones del nombre del campo
        if (empty($results)) {
            // Probar con el nombre exacto que se ve en la tabla
            $results = $wpdb->get_col("
                SELECT DISTINCT pm.meta_value 
                FROM {$wpdb->prefix}postmeta pm
                INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
                WHERE pm.meta_key = 'local'
                AND p.post_type = 'shop_order_placehold'
                AND pm.meta_value IS NOT NULL
                AND pm.meta_value != ''
                AND pm.meta_value != '0'
                ORDER BY pm.meta_value ASC
            ");
        }
        
        return $results ?: array();
    }
    
    private function get_product_categories() {
        global $wpdb;
        
        // Consulta más simple y directa
        $results = $wpdb->get_col("
            SELECT DISTINCT t.name
            FROM {$wpdb->prefix}terms t
            INNER JOIN {$wpdb->prefix}term_taxonomy tt ON t.term_id = tt.term_id
            INNER JOIN {$wpdb->prefix}term_relationships tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN {$wpdb->prefix}posts p ON tr.object_id = p.ID
            WHERE tt.taxonomy = 'product_cat'
            AND p.post_type = 'product'
            AND p.post_status = 'publish'
            ORDER BY t.name
        ");
        
        return $results ?: array();
    }
    
    public function export_pdf() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        $filters = array(
            'search' => sanitize_text_field($_GET['search']),
            'categoria' => sanitize_text_field($_GET['categoria']),
            'local' => sanitize_text_field($_GET['local']),
            'equipo' => sanitize_text_field($_GET['equipo']),
            'sort_by' => sanitize_text_field($_GET['sort_by']),
            'sort_order' => sanitize_text_field($_GET['sort_order'])
        );
        
        $data = LinmaniaExport::get_inscriptions_data($filters);
        LinmaniaExport::export_to_pdf($data, 'inscripciones');
    }
    
    public function create_test_data() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce no está activo');
        }
        
        // Crear algunas órdenes de prueba
        $test_orders = array(
            array(
                'categoria' => 'BULLSHOOTER',
                'local' => 'GWVW',
                'equipo' => 'GERGER',
                'jugador1' => 'QWQWQFQW',
                'jugador2' => 'WEGHWEH',
                'jugador3' => '',
                'jugador4' => '',
                'suplentes' => ''
            ),
            array(
                'categoria' => 'BULLSHOOTER',
                'local' => 'qwg',
                'equipo' => 'qwf',
                'jugador1' => 'Jugador A',
                'jugador2' => 'Jugador B',
                'jugador3' => 'Jugador C',
                'jugador4' => '',
                'suplentes' => 'Suplente 1, Suplente 2'
            ),
            array(
                'categoria' => 'CONNECTION',
                'local' => 'CLASSIC BULEBAR',
                'equipo' => 'Exótic',
                'jugador1' => 'Player One',
                'jugador2' => 'Player Two',
                'jugador3' => 'Player Three',
                'jugador4' => 'Player Four',
                'suplentes' => 'Substitute 1'
            )
        );
        
        $created_count = 0;
        
        foreach ($test_orders as $order_data) {
            // Crear orden de WooCommerce
            $order = wc_create_order();
            
            if ($order) {
                // Agregar un producto de prueba
                $product = wc_get_product(1); // Usar el primer producto disponible
                if (!$product) {
                    // Crear un producto de prueba si no existe
                    $product = new WC_Product_Simple();
                    $product->set_name('Inscripción de Torneo');
                    $product->set_price(50);
                    $product->save();
                }
                
                $order->add_product($product, 1);
                $order->set_status('completed');
                $order->save();
                
                // Agregar campos personalizados
                $order_id = $order->get_id();
                update_post_meta($order_id, 'categoria_torneo', $order_data['categoria']);
                update_post_meta($order_id, 'local_torneo', $order_data['local']);
                update_post_meta($order_id, 'nombre_equipo', $order_data['equipo']);
                update_post_meta($order_id, 'jugador_1', $order_data['jugador1']);
                update_post_meta($order_id, 'jugador_2', $order_data['jugador2']);
                update_post_meta($order_id, 'jugador_3', $order_data['jugador3']);
                update_post_meta($order_id, 'jugador_4', $order_data['jugador4']);
                update_post_meta($order_id, 'suplentes', $order_data['suplentes']);
                
                $created_count++;
            }
        }
        
        wp_send_json_success(array(
            'message' => "Se crearon {$created_count} órdenes de prueba",
            'count' => $created_count
        ));
    }
    
    public function add_fields_to_existing_orders() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce no está activo');
        }
        
        // Obtener todas las órdenes existentes
        $args = array(
            'post_type' => 'shop_order_placehold', // Solo el tipo que funciona
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        );
        
        $query = new WP_Query($args);
        $updated_count = 0;
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                
                // Verificar si ya tiene campos personalizados
                $has_custom_fields = get_post_meta($order_id, 'categoria_torneo', true) || 
                                   get_post_meta($order_id, 'local_torneo', true) || 
                                   get_post_meta($order_id, 'nombre_equipo', true);
                
                if (!$has_custom_fields) {
                    // Agregar campos de prueba a órdenes existentes
                    $test_data = array(
                        'categoria_torneo' => 'BULLSHOOTER',
                        'local_torneo' => 'LOCAL PRUEBA',
                        'nombre_equipo' => 'EQUIPO ' . $order_id,
                        'jugador_1' => 'Jugador 1 - Orden ' . $order_id,
                        'jugador_2' => 'Jugador 2 - Orden ' . $order_id,
                        'jugador_3' => '',
                        'jugador_4' => '',
                        'suplentes' => 'Suplente ' . $order_id
                    );
                    
                    foreach ($test_data as $key => $value) {
                        update_post_meta($order_id, $key, $value);
                    }
                    
                    $updated_count++;
                }
            }
        }
        
        wp_reset_postdata();
        
        wp_send_json_success(array(
            'message' => "Se actualizaron {$updated_count} órdenes existentes con campos personalizados",
            'count' => $updated_count
        ));
    }
    
    public function debug_database() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        global $wpdb;
        
        // Información básica de WordPress
        $wp_info = array(
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'mysql_version' => $wpdb->get_var("SELECT VERSION()"),
            'table_prefix' => $wpdb->prefix
        );
        
        // Verificar si WooCommerce está activo
        $wc_info = array(
            'wc_active' => class_exists('WooCommerce'),
            'wc_version' => class_exists('WooCommerce') ? WC()->version : 'No disponible'
        );
        
        // Verificar tablas de WordPress
        $posts_table = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}posts'");
        $postmeta_table = $wpdb->get_results("SHOW TABLES LIKE '{$wpdb->prefix}postmeta'");
        
        // Contar posts por tipo
        $post_types = $wpdb->get_results("
            SELECT post_type, COUNT(*) as count 
            FROM {$wpdb->prefix}posts 
            WHERE post_status != 'trash' 
            GROUP BY post_type 
            ORDER BY count DESC
        ");
        
        // Buscar órdenes específicamente
        $shop_orders = $wpdb->get_results("
            SELECT ID, post_title, post_status, post_date 
            FROM {$wpdb->prefix}posts 
            WHERE post_type IN ('shop_order', 'shop_order_placehold') 
            ORDER BY ID DESC 
            LIMIT 10
        ");
        
        // Verificar si hay órdenes con diferentes estados
        $order_statuses = $wpdb->get_results("
            SELECT post_status, COUNT(*) as count 
            FROM {$wpdb->prefix}posts 
            WHERE post_type IN ('shop_order', 'shop_order_placehold') 
            GROUP BY post_status
        ");
        
        // Verificar meta campos relacionados con órdenes
        $order_meta = $wpdb->get_results("
            SELECT pm.meta_key, COUNT(*) as count 
            FROM {$wpdb->prefix}postmeta pm
            INNER JOIN {$wpdb->prefix}posts p ON pm.post_id = p.ID
            WHERE p.post_type IN ('shop_order', 'shop_order_placehold')
            AND pm.meta_key IN ('categoria_torneo', 'local_torneo', 'nombre_equipo', 'jugador_1')
            GROUP BY pm.meta_key
        ");
        
        wp_send_json_success(array(
            'wp_info' => $wp_info,
            'wc_info' => $wc_info,
            'tables' => array(
                'posts_table_exists' => !empty($posts_table),
                'postmeta_table_exists' => !empty($postmeta_table)
            ),
            'post_types' => $post_types,
            'shop_orders' => $shop_orders,
            'order_statuses' => $order_statuses,
            'order_meta' => $order_meta,
            'total_shop_orders' => count($shop_orders)
        ));
    }
    
    public function test_orders_query() {
        check_ajax_referer('linmania_nonce', 'nonce');
        
        // Probar diferentes consultas para encontrar las órdenes
        $test_queries = array();
        
        // Consulta 1: shop_order_placehold con any
        $query1 = new WP_Query(array(
            'post_type' => 'shop_order_placehold',
            'post_status' => 'any',
            'posts_per_page' => 5,
            'fields' => 'ids'
        ));
        $test_queries['shop_order_placehold_any'] = array(
            'found_posts' => $query1->found_posts,
            'post_count' => $query1->post_count,
            'posts' => $query1->posts
        );
        
        // Consulta 2: shop_order con any
        $query2 = new WP_Query(array(
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'posts_per_page' => 5,
            'fields' => 'ids'
        ));
        $test_queries['shop_order_any'] = array(
            'found_posts' => $query2->found_posts,
            'post_count' => $query2->post_count,
            'posts' => $query2->posts
        );
        
        // Consulta 3: Ambos tipos con any
        $query3 = new WP_Query(array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => 'any',
            'posts_per_page' => 5,
            'fields' => 'ids'
        ));
        $test_queries['both_types_any'] = array(
            'found_posts' => $query3->found_posts,
            'post_count' => $query3->post_count,
            'posts' => $query3->posts
        );
        
        // Consulta 4: Con estados específicos
        $query4 = new WP_Query(array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => array('wc-completed', 'wc-processing', 'wc-pending', 'publish', 'private'),
            'posts_per_page' => 5,
            'fields' => 'ids'
        ));
        $test_queries['both_types_specific_status'] = array(
            'found_posts' => $query4->found_posts,
            'post_count' => $query4->post_count,
            'posts' => $query4->posts
        );
        
        // Consulta 5: get_posts directo
        $posts_direct = get_posts(array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => 'any',
            'numberposts' => 5,
            'fields' => 'ids'
        ));
        $test_queries['get_posts_direct'] = array(
            'count' => count($posts_direct),
            'posts' => $posts_direct
        );
        
        wp_send_json_success(array(
            'test_queries' => $test_queries,
            'message' => 'Pruebas de consulta completadas'
        ));
    }
}

// Initialize the plugin
new LinmaniaBlogInscripciones();
