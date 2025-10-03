<?php
/**
 * Script de Verificación de Órdenes
 * 
 * Este script verifica todas las órdenes de WooCommerce y muestra:
 * - Qué tipo de post son
 * - Qué campos personalizados tienen
 * - Por qué algunas no aparecen en la tabla
 * 
 * INSTRUCCIONES:
 * 1. Accede a este archivo desde el navegador:
 *    http://localhost/linmania/wp-content/plugins/linmania-blog-inscripciones/verificar-ordenes.php
 * 2. Revisa la información de cada orden
 * 3. Identifica cuáles no tienen los campos necesarios
 */

// Cargar WordPress
require_once(__DIR__ . '/../../../../wp-load.php');

// Verificar que es un administrador
if (!current_user_can('manage_options')) {
    die('Acceso denegado. Debes ser administrador.');
}

// Verificar que WooCommerce esté activo
if (!class_exists('WooCommerce')) {
    die('WooCommerce no está activo.');
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Verificación de Órdenes - Linmania</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #0073aa;
            border-bottom: 2px solid #0073aa;
            padding-bottom: 10px;
        }
        .stats {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .order {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            background: #fafafa;
        }
        .order.complete {
            border-left: 4px solid #4caf50;
        }
        .order.incomplete {
            border-left: 4px solid #f44336;
        }
        .order h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .meta-fields {
            background: white;
            padding: 10px;
            border-radius: 3px;
            margin-top: 10px;
        }
        .meta-field {
            padding: 5px 0;
            font-size: 14px;
        }
        .meta-field .key {
            font-weight: bold;
            color: #0073aa;
            display: inline-block;
            width: 200px;
        }
        .meta-field .value {
            color: #555;
        }
        .missing {
            color: #f44336;
            font-style: italic;
        }
        .present {
            color: #4caf50;
        }
        .status {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.complete {
            background: #4caf50;
            color: white;
        }
        .status.incomplete {
            background: #f44336;
            color: white;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background: #d4edda;
            border-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación de Órdenes de WooCommerce</h1>
        
        <?php
        // Obtener TODAS las órdenes sin filtros
        $args = array(
            'post_type' => array('shop_order', 'shop_order_placehold'),
            'post_status' => 'any',
            'posts_per_page' => -1,
            'orderby' => 'ID',
            'order' => 'DESC'
        );
        
        $query = new WP_Query($args);
        
        // Estadísticas
        $total_ordenes = $query->found_posts;
        $ordenes_completas = 0;
        $ordenes_incompletas = 0;
        
        // Campos requeridos
        $campos_requeridos = array(
            'nombre_equipo',
            'local',
            'nombre_jugador_1',
            'telefono_jugador_1'
        );
        
        $ordenes_info = array();
        
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $order_id = get_the_ID();
                $order = wc_get_order($order_id);
                
                if (!$order) continue;
                
                // Verificar campos
                $campos_presentes = array();
                $campos_faltantes = array();
                
                foreach ($campos_requeridos as $campo) {
                    $valor = $order->get_meta($campo);
                    if (!empty($valor)) {
                        $campos_presentes[] = $campo;
                    } else {
                        $campos_faltantes[] = $campo;
                    }
                }
                
                $tiene_todos_campos = empty($campos_faltantes);
                
                if ($tiene_todos_campos) {
                    $ordenes_completas++;
                } else {
                    $ordenes_incompletas++;
                }
                
                // Obtener categorías del producto
                $categorias = array();
                $items = $order->get_items();
                foreach ($items as $item) {
                    $product = $item->get_product();
                    if ($product) {
                        $product_cats = wp_get_post_terms($product->get_id(), 'product_cat');
                        foreach ($product_cats as $cat) {
                            $categorias[] = $cat->name;
                        }
                    }
                }
                
                $ordenes_info[] = array(
                    'order' => $order,
                    'tiene_todos_campos' => $tiene_todos_campos,
                    'campos_presentes' => $campos_presentes,
                    'campos_faltantes' => $campos_faltantes,
                    'categorias' => $categorias
                );
            }
            wp_reset_postdata();
        }
        ?>
        
        <div class="stats">
            <h2>📊 Estadísticas</h2>
            <p><strong>Total de Órdenes:</strong> <?php echo $total_ordenes; ?></p>
            <p><strong>Órdenes con todos los campos:</strong> <span class="present"><?php echo $ordenes_completas; ?></span></p>
            <p><strong>Órdenes sin campos completos:</strong> <span class="missing"><?php echo $ordenes_incompletas; ?></span></p>
        </div>
        
        <?php if ($ordenes_incompletas > 0): ?>
        <div class="alert">
            <strong>⚠️ Problema Identificado:</strong><br>
            Hay <?php echo $ordenes_incompletas; ?> orden(es) que NO tienen todos los campos personalizados necesarios.
            Esto explica por qué no aparecen en la tabla de inscripciones.
        </div>
        <?php else: ?>
        <div class="alert success">
            <strong>✅ Todo Correcto:</strong><br>
            Todas las órdenes tienen los campos necesarios.
        </div>
        <?php endif; ?>
        
        <h2>📋 Detalle de Órdenes</h2>
        
        <?php foreach ($ordenes_info as $info): ?>
            <?php 
            $order = $info['order'];
            $order_id = $order->get_id();
            $date = $order->get_date_created();
            ?>
            <div class="order <?php echo $info['tiene_todos_campos'] ? 'complete' : 'incomplete'; ?>">
                <h3>
                    Orden #<?php echo $order_id; ?>
                    <span class="status <?php echo $info['tiene_todos_campos'] ? 'complete' : 'incomplete'; ?>">
                        <?php echo $info['tiene_todos_campos'] ? 'COMPLETA' : 'INCOMPLETA'; ?>
                    </span>
                </h3>
                
                <p>
                    <strong>Fecha:</strong> <?php echo $date ? $date->date('d/m/Y H:i') : 'N/A'; ?><br>
                    <strong>Estado:</strong> <?php echo $order->get_status(); ?><br>
                    <strong>Tipo de Post:</strong> <?php echo get_post_type($order_id); ?><br>
                    <strong>Categorías:</strong> <?php echo !empty($info['categorias']) ? implode(', ', $info['categorias']) : '<span class="missing">Sin categoría</span>'; ?>
                </p>
                
                <div class="meta-fields">
                    <strong>Campos Personalizados:</strong>
                    
                    <div class="meta-field">
                        <span class="key">nombre_equipo:</span>
                        <span class="value <?php echo in_array('nombre_equipo', $info['campos_presentes']) ? 'present' : 'missing'; ?>">
                            <?php 
                            $valor = $order->get_meta('nombre_equipo');
                            echo !empty($valor) ? $valor : '❌ NO CONFIGURADO';
                            ?>
                        </span>
                    </div>
                    
                    <div class="meta-field">
                        <span class="key">local:</span>
                        <span class="value <?php echo in_array('local', $info['campos_presentes']) ? 'present' : 'missing'; ?>">
                            <?php 
                            $valor = $order->get_meta('local');
                            echo !empty($valor) ? $valor : '❌ NO CONFIGURADO';
                            ?>
                        </span>
                    </div>
                    
                    <div class="meta-field">
                        <span class="key">nombre_jugador_1:</span>
                        <span class="value <?php echo in_array('nombre_jugador_1', $info['campos_presentes']) ? 'present' : 'missing'; ?>">
                            <?php 
                            $valor = $order->get_meta('nombre_jugador_1');
                            echo !empty($valor) ? $valor : '❌ NO CONFIGURADO';
                            ?>
                        </span>
                    </div>
                    
                    <div class="meta-field">
                        <span class="key">telefono_jugador_1:</span>
                        <span class="value <?php echo in_array('telefono_jugador_1', $info['campos_presentes']) ? 'present' : 'missing'; ?>">
                            <?php 
                            $valor = $order->get_meta('telefono_jugador_1');
                            echo !empty($valor) ? $valor : '❌ NO CONFIGURADO';
                            ?>
                        </span>
                    </div>
                    
                    <div class="meta-field">
                        <span class="key">nombre_jugador_2:</span>
                        <span class="value">
                            <?php 
                            $valor = $order->get_meta('nombre_jugador_2');
                            echo !empty($valor) ? $valor : '(Vacío)';
                            ?>
                        </span>
                    </div>
                    
                    <div class="meta-field">
                        <span class="key">suplentes:</span>
                        <span class="value">
                            <?php 
                            $valor = $order->get_meta('suplentes');
                            echo !empty($valor) ? $valor : '(Vacío)';
                            ?>
                        </span>
                    </div>
                </div>
                
                <?php if (!$info['tiene_todos_campos']): ?>
                <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 3px;">
                    <strong>⚠️ Campos faltantes:</strong> 
                    <?php echo implode(', ', $info['campos_faltantes']); ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div style="margin-top: 30px; padding: 20px; background: #e3f2fd; border-radius: 5px;">
            <h3>💡 Recomendaciones</h3>
            <ul>
                <li>Las órdenes marcadas como <strong>INCOMPLETAS</strong> no aparecen en la tabla porque les faltan campos obligatorios</li>
                <li>Para que aparezcan, debes agregar los campos faltantes manualmente en cada orden</li>
                <li>Los campos obligatorios son: <code>nombre_equipo</code>, <code>local</code>, <code>nombre_jugador_1</code>, <code>telefono_jugador_1</code></li>
                <li>Puedes editarlos en: <strong>WooCommerce → Pedidos → [Editar Orden] → Custom Fields</strong></li>
            </ul>
        </div>
        
        <p style="margin-top: 20px; text-align: center; color: #666;">
            <a href="<?php echo admin_url('admin.php?page=linmania-inscripciones'); ?>">← Volver a Inscripciones</a>
        </p>
    </div>
</body>
</html>

