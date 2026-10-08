<?php
/**
 * Template part para mostrar el acordeón y su JSON-LD
 
 El Frontend: El Archivo Template (template-parts/accordion.php)
Este es el archivo que llamarás dentro de tu tema utilizando get_template_part( 'template-parts/accordion' );.
 */

$accordion_data = get_post_meta( get_the_ID(), '_sw_accordion_data', true );

// Si no hay datos, terminamos la ejecución tempranamente (Early return)
if ( empty( $accordion_data ) || ! is_array( $accordion_data ) ) {
    return;
}

// Variables para construir el JSON-LD de SEO
$schema_entities = [];

?>

<div class="sw-semantic-accordion-wrapper">
    <?php foreach ( $accordion_data as $item ) : 
        if ( empty( $item['title'] ) && empty( $item['desc'] ) ) continue;
        
        // Poblamos el array para el JSON-LD
        $schema_entities[] = [
            '@type'          => 'Question',
            'name'           => wp_strip_all_tags( $item['title'] ),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => wp_strip_all_tags( $item['desc'] ) // Strip tags para un JSON limpio
            ]
        ];
    ?>
        
        <!-- Elemento HTML Nativo -->
        <details class="sw-accordion-item">
            <summary class="sw-accordion-summary">
                <span class="sw-accordion-title"><?php echo esc_html( $item['title'] ); ?></span>
                <!-- Icono SVG en línea (Chevron). Ligero, sin dependencias externas. -->
                <svg class="sw-accordion-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </summary>
            
            <div class="sw-accordion-content">
                <?php 
                // wpautop() es la magia aquí: convierte los \n en <p> y <br>, respetando los "escalones" exactos del usuario.
                echo wpautop( esc_html( $item['desc'] ) ); 
                ?>
            </div>
        </details>

    <?php endforeach; ?>
</div>

<?php 
// Renderizado del JSON-LD dinámico
if ( ! empty( $schema_entities ) ) : 
    $schema_data = [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $schema_entities
    ];
?>
<script type="application/ld+json">
    <?php echo wp_json_encode( $schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ); ?>
</script>
<?php endif; ?>