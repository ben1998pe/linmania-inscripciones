<?php
/**
 * Export functions for Linmania Blog e Inscripciones
 */

if (!defined('ABSPATH')) {
    exit;
}

class LinmaniaExport {
    
    public static function export_to_excel($data, $filename = 'inscripciones') {
        // Set headers for Excel download with proper encoding
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Add BOM for UTF-8 to ensure proper encoding in Excel
        echo "\xEF\xBB\xBF";
        
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
            echo '<td>' . htmlspecialchars($inscription['ID'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['date'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['categoria'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['local'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['equipo'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['jugador1'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['telefono1'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['jugador2'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['telefono2'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['jugador3'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['telefono3'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['jugador4'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['telefono4'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '<td>' . htmlspecialchars($inscription['suplentes'], ENT_QUOTES, 'UTF-8') . '</td>';
            echo '</tr>';
        }
        
        echo '</table>';
        exit;
    }
    
    public static function export_to_pdf($data, $filename = 'inscripciones') {
        // Check if DomPDF is available
        if (!class_exists('Dompdf\Dompdf')) {
            wp_die('DomPDF no está disponible. Por favor, ejecuta "composer install" en el directorio del plugin.');
        }
        
        // Generate HTML for PDF
        $html = self::generate_pdf_html($data);
        
        // Create full HTML document
        $full_html = '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Reporte de Inscripciones - ' . date('Y-m-d') . '</title>
            <style>
                body { 
                    font-family: Arial, sans-serif; 
                    font-size: 10px; 
                    margin: 15px;
                    line-height: 1.3;
                }
                .header { 
                    text-align: center; 
                    margin-bottom: 25px; 
                    border-bottom: 2px solid #333;
                    padding-bottom: 10px;
                }
                .header h1 { 
                    margin: 0; 
                    font-size: 20px; 
                    color: #333;
                }
                .header p { 
                    margin: 3px 0 0 0; 
                    font-size: 10px; 
                    color: #666;
                }
                table { 
                    width: 100%; 
                    border-collapse: collapse; 
                    margin-top: 15px;
                    font-size: 8px;
                }
                th, td { 
                    border: 1px solid #333; 
                    padding: 4px 3px; 
                    text-align: left; 
                    vertical-align: top;
                }
                th { 
                    background-color: #f5f5f5; 
                    font-weight: bold; 
                    font-size: 8px;
                }
                .footer { 
                    margin-top: 20px; 
                    font-size: 8px; 
                    color: #666;
                    text-align: center;
                    border-top: 1px solid #ccc;
                    padding-top: 8px;
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Reporte de Inscripciones</h1>
                <p>Generado el: ' . date('d/m/Y H:i:s') . '</p>
                <p>Total de registros: ' . count($data) . '</p>
            </div>
            
            ' . $html . '
            
            <div class="footer">
                <p>Linmania Blog e Inscripciones - Desarrollado por Benjamin Oscco Arias</p>
                <p>Página generada el ' . date('d/m/Y H:i:s') . '</p>
            </div>
        </body>
        </html>';
        
        // Create DomPDF instance
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->loadHtml($full_html);
        
        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');
        
        // Render the HTML as PDF
        $dompdf->render();
        
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.pdf"');
        header('Cache-Control: max-age=0');
        
        // Output the generated PDF
        echo $dompdf->output();
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
            $html .= '<td>' . htmlspecialchars($inscription['ID'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['date'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['categoria'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['local'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['equipo'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['jugador1'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['telefono1'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['jugador2'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['telefono2'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['jugador3'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['telefono3'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['jugador4'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['telefono4'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '<td>' . htmlspecialchars($inscription['suplentes'], ENT_QUOTES, 'UTF-8') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        return $html;
    }
    
    public static function get_inscriptions_data($filters = array()) {
        $args = array(
            'post_type' => 'shop_order_placehold',
            'post_status' => 'any',
            'posts_per_page' => -1, // Get all records for export
            'meta_query' => array()
        );
        
        // No aplicar filtros aquí - se harán después de obtener los datos
        
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
                $categoria = !empty($product_categories) ? implode(', ', array_unique($product_categories)) : 'Sin categoría';
                
                $inscriptions[] = array(
                    'ID' => $order_id,
                    'date' => $formatted_date,
                    'categoria' => $categoria,
                    'local' => $order->get_meta('local') ?: 'Sin local',
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
            }
        }
        
        wp_reset_postdata();
        
        // Aplicar filtros después de obtener los datos (igual que en la tabla principal)
        $filtered_inscriptions = $inscriptions;
        
        // Filtro de categoría
        if (!empty($filters['categoria']) && $filters['categoria'] !== '' && $filters['categoria'] !== 'Nothing selected' && $filters['categoria'] !== 'Todas las categorías') {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                if (stripos($inscription['categoria'], $filters['categoria']) !== false) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        // Filtro de local
        if (!empty($filters['local']) && $filters['local'] !== '' && $filters['local'] !== 'Nothing selected' && $filters['local'] !== 'Todos los locales') {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                if (stripos($inscription['local'], $filters['local']) !== false) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        // Búsqueda general en equipo, jugadores y suplentes
        if (!empty($filters['search'])) {
            $temp_filtered = array();
            foreach ($filtered_inscriptions as $inscription) {
                $search_found = false;
                
                // Buscar en nombre del equipo
                if (stripos($inscription['equipo'], $filters['search']) !== false) {
                    $search_found = true;
                }
                
                // Buscar en nombres de jugadores
                if (!$search_found) {
                    for ($i = 1; $i <= 4; $i++) {
                        $jugador_key = 'jugador' . $i;
                        if (isset($inscription[$jugador_key]) && stripos($inscription[$jugador_key], $filters['search']) !== false) {
                            $search_found = true;
                            break;
                        }
                    }
                }
                
                // Buscar en suplentes
                if (!$search_found && stripos($inscription['suplentes'], $filters['search']) !== false) {
                    $search_found = true;
                }
                
                if ($search_found) {
                    $temp_filtered[] = $inscription;
                }
            }
            $filtered_inscriptions = $temp_filtered;
        }
        
        return $filtered_inscriptions;
    }
}
