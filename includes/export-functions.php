<?php
/**
 * Export functions for Linmania Blog e Inscripciones
 */

if (!defined('ABSPATH')) {
    exit;
}

class LinmaniaExport {
    
    public static function export_to_excel($data, $filename = 'inscripciones') {
        // Set headers for Excel download
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Start output
        echo '<table border="1">';
        echo '<tr>';
        echo '<th>ID ORDEN</th>';
        echo '<th>FECHA</th>';
        echo '<th>CATEGORÍAS</th>';
        echo '<th>LOCAL</th>';
        echo '<th>EQUIPO</th>';
        echo '<th>JUGADOR 1</th>';
        echo '<th>TEL JUGADOR 1</th>';
        echo '<th>JUGADOR 2</th>';
        echo '<th>TEL JUGADOR 2</th>';
        echo '<th>JUGADOR 3</th>';
        echo '<th>TEL JUGADOR 3</th>';
        echo '<th>JUGADOR 4</th>';
        echo '<th>TEL JUGADOR 4</th>';
        echo '<th>SUPLENTES</th>';
        echo '</tr>';
        
        foreach ($data as $inscription) {
            echo '<tr>';
            echo '<td>' . esc_html($inscription['ID']) . '</td>';
            echo '<td>' . esc_html($inscription['date']) . '</td>';
            echo '<td>' . esc_html($inscription['categoria']) . '</td>';
            echo '<td>' . esc_html($inscription['local']) . '</td>';
            echo '<td>' . esc_html($inscription['equipo']) . '</td>';
            echo '<td>' . esc_html($inscription['jugador1']) . '</td>';
            echo '<td>' . esc_html($inscription['telefono1']) . '</td>';
            echo '<td>' . esc_html($inscription['jugador2']) . '</td>';
            echo '<td>' . esc_html($inscription['telefono2']) . '</td>';
            echo '<td>' . esc_html($inscription['jugador3']) . '</td>';
            echo '<td>' . esc_html($inscription['telefono3']) . '</td>';
            echo '<td>' . esc_html($inscription['jugador4']) . '</td>';
            echo '<td>' . esc_html($inscription['telefono4']) . '</td>';
            echo '<td>' . esc_html($inscription['suplentes']) . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    public static function export_to_pdf($data, $filename = 'inscripciones') {
        // Simple PDF generation using HTML to PDF conversion
        $html = self::generate_pdf_html($data);
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.pdf"');
        header('Cache-Control: max-age=0');
        
        // For now, we'll output HTML that can be printed as PDF
        // In a production environment, you'd want to use a proper PDF library like TCPDF or mPDF
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Inscripciones - ' . date('Y-m-d') . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; font-weight: bold; }
                .header { text-align: center; margin-bottom: 20px; }
                .footer { margin-top: 20px; font-size: 10px; color: #666; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Inscripciones</h1>
                <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
            </div>
            ' . $html . '
            <div class="footer">
                <p>Linmania Blog e Inscripciones - Desarrollado por Benjamin Oscco Arias</p>
            </div>
        </body>
        </html>';
        
        exit;
    }
    
    private static function generate_pdf_html($data) {
        $html = '<table>';
        $html .= '<tr>';
        $html .= '<th>ID ORDEN</th>';
        $html .= '<th>FECHA</th>';
        $html .= '<th>CATEGORÍAS</th>';
        $html .= '<th>LOCAL</th>';
        $html .= '<th>EQUIPO</th>';
        $html .= '<th>JUGADOR 1</th>';
        $html .= '<th>TEL JUGADOR 1</th>';
        $html .= '<th>JUGADOR 2</th>';
        $html .= '<th>TEL JUGADOR 2</th>';
        $html .= '<th>JUGADOR 3</th>';
        $html .= '<th>TEL JUGADOR 3</th>';
        $html .= '<th>JUGADOR 4</th>';
        $html .= '<th>TEL JUGADOR 4</th>';
        $html .= '<th>SUPLENTES</th>';
        $html .= '</tr>';
        
        foreach ($data as $inscription) {
            $html .= '<tr>';
            $html .= '<td>' . esc_html($inscription['ID']) . '</td>';
            $html .= '<td>' . esc_html($inscription['date']) . '</td>';
            $html .= '<td>' . esc_html($inscription['categoria']) . '</td>';
            $html .= '<td>' . esc_html($inscription['local']) . '</td>';
            $html .= '<td>' . esc_html($inscription['equipo']) . '</td>';
            $html .= '<td>' . esc_html($inscription['jugador1']) . '</td>';
            $html .= '<td>' . esc_html($inscription['telefono1']) . '</td>';
            $html .= '<td>' . esc_html($inscription['jugador2']) . '</td>';
            $html .= '<td>' . esc_html($inscription['telefono2']) . '</td>';
            $html .= '<td>' . esc_html($inscription['jugador3']) . '</td>';
            $html .= '<td>' . esc_html($inscription['telefono3']) . '</td>';
            $html .= '<td>' . esc_html($inscription['jugador4']) . '</td>';
            $html .= '<td>' . esc_html($inscription['telefono4']) . '</td>';
            $html .= '<td>' . esc_html($inscription['suplentes']) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        return $html;
    }
    
    public static function get_inscriptions_data($filters = array()) {
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => array('wc-completed', 'wc-processing', 'wc-pending'),
            'posts_per_page' => -1, // Get all records for export
            'meta_query' => array()
        );
        
        // Apply filters
        if (!empty($filters['categoria'])) {
            $args['meta_query'][] = array(
                'key' => 'categoria_torneo',
                'value' => $filters['categoria'],
                'compare' => 'LIKE'
            );
        }
        
        if (!empty($filters['local'])) {
            $args['meta_query'][] = array(
                'key' => 'local_torneo',
                'value' => $filters['local'],
                'compare' => 'LIKE'
            );
        }
        
        if (!empty($filters['equipo'])) {
            $args['meta_query'][] = array(
                'key' => 'nombre_equipo',
                'value' => $filters['equipo'],
                'compare' => 'LIKE'
            );
        }
        
        if (!empty($filters['search'])) {
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key' => 'nombre_equipo',
                    'value' => $filters['search'],
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'local_torneo',
                    'value' => $filters['search'],
                    'compare' => 'LIKE'
                )
            );
        }
        
        // Apply sorting
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'ID':
                    $args['orderby'] = 'ID';
                    break;
                case 'date':
                    $args['orderby'] = 'date';
                    break;
                case 'categoria':
                    $args['orderby'] = 'meta_value';
                    $args['meta_key'] = 'categoria_torneo';
                    break;
                case 'local':
                    $args['orderby'] = 'meta_value';
                    $args['meta_key'] = 'local_torneo';
                    break;
                case 'equipo':
                    $args['orderby'] = 'meta_value';
                    $args['meta_key'] = 'nombre_equipo';
                    break;
            }
        }
        
        if (!empty($filters['sort_order'])) {
            $args['order'] = $filters['sort_order'];
        }
        
        $query = new WP_Query($args);
        $inscriptions = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                $order = wc_get_order($order_id);
                
                if (!$order) continue;
                
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
                $categoria = !empty($product_categories) ? implode(', ', array_unique($product_categories)) : 'Sin categoría';
                
                $inscriptions[] = array(
                    'ID' => $order_id,
                    'date' => $order->get_date_created()->date('d/m/Y h:i A'),
                    'categoria' => $categoria,
                    'local' => $order->get_meta('local'),
                    'equipo' => $order->get_meta('nombre_equipo'),
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
            }
        }
        
        wp_reset_postdata();
        return $inscriptions;
    }
}
