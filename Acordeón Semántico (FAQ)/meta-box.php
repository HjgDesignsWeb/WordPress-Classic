<?php
/**
 * Registra el Meta Box para el Acordeón Semántico en Entradas y Páginas
 */
add_action( 'add_meta_boxes', 'sw_register_accordion_meta_box' );
function sw_register_accordion_meta_box() {
    $screens = [ 'post', 'page' ];
    foreach ( $screens as $screen ) {
        add_meta_box(
            'sw_semantic_accordion',
            'Acordeón Semántico (FAQ)',
            'sw_render_accordion_meta_box',
            $screen,
            'normal',
            'high'
        );
    }
}

/**
 * Renderiza los campos en el administrador
 */
function sw_render_accordion_meta_box( $post ) {
    // Añadimos un nonce para seguridad
    wp_nonce_field( 'sw_accordion_save_data', 'sw_accordion_meta_box_nonce' );

    // Recuperamos los datos existentes
    $accordion_data = get_post_meta( $post->ID, '_sw_accordion_data', true );
    if ( ! is_array( $accordion_data ) ) {
        $accordion_data = [];
    }

    echo '<div style="display: flex; flex-direction: column; gap: 20px;">';
    
    // Generamos dinámicamente los 5 bloques mediante un bucle (código DRY)
    for ( $i = 0; $i < 5; $i++ ) {
        $title = isset( $accordion_data[$i]['title'] ) ? $accordion_data[$i]['title'] : '';
        $desc  = isset( $accordion_data[$i]['desc'] ) ? $accordion_data[$i]['desc'] : '';
        
        $num = $i + 1;
        echo '<div style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd;">';
        echo '<h4 style="margin-top:0;">Elemento ' . $num . '</h4>';
        
        // Campo Título
        echo '<p><label for="sw_acc_title_' . $i . '"><strong>Título:</strong></label><br>';
        echo '<input type="text" id="sw_acc_title_' . $i . '" name="sw_accordion[' . $i . '][title]" value="' . esc_attr( $title ) . '" style="width: 100%;" /></p>';
        
        // Campo Descripción (Textarea para respetar los saltos de línea)
        echo '<p><label for="sw_acc_desc_' . $i . '"><strong>Descripción:</strong></label><br>';
        echo '<textarea id="sw_acc_desc_' . $i . '" name="sw_accordion[' . $i . '][desc]" rows="4" style="width: 100%;">' . esc_textarea( $desc ) . '</textarea></p>';
        echo '<p class="description">Los saltos de línea y párrafos vacíos se respetarán en el frontend.</p>';
        echo '</div>';
    }
    
    echo '</div>';
}

/**
 * Guarda los datos del Meta Box
 */
add_action( 'save_post', 'sw_save_accordion_meta_box_data' );
function sw_save_accordion_meta_box_data( $post_id ) {
    // Verificaciones de seguridad
    if ( ! isset( $_POST['sw_accordion_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['sw_accordion_meta_box_nonce'], 'sw_accordion_save_data' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $sanitized_data = [];

    if ( isset( $_POST['sw_accordion'] ) && is_array( $_POST['sw_accordion'] ) ) {
        foreach ( $_POST['sw_accordion'] as $item ) {
            $title = sanitize_text_field( $item['title'] );
            // sanitize_textarea_field es clave aquí: limpia el input pero MANTIENE los \n (saltos de línea)
            $desc  = sanitize_textarea_field( $item['desc'] ); 
            
            // Solo guardamos el elemento si al menos el título o la descripción tienen contenido
            if ( ! empty( $title ) || ! empty( $desc ) ) {
                $sanitized_data[] = [
                    'title' => $title,
                    'desc'  => $desc
                ];
            }
        }
    }

    // Single Source of Truth: Guardamos todo como un único array
    update_post_meta( $post_id, '_sw_accordion_data', $sanitized_data );
}