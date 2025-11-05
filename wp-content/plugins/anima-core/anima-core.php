<?php
/**
 * Plugin Name: Anima Core
 * Description: Funcionalidades núcleo para el sitio Web Anima (CPTs, taxonomías, campos, shortcode, API REST).
 * Author: Tu Nombre
 * Version: 1.0
 * Text Domain: anima-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Salir si se accede directamente al archivo
}

// --- 1. REGISTRO DE CUSTOM POST TYPES ---

function anima_register_post_types() {
    // CPT: Curso
    $labels_curso = array(
        'name'               => __( 'Cursos', 'anima-core' ),
        'singular_name'      => __( 'Curso', 'anima-core' ),
        'add_new'            => __( 'Añadir Nuevo', 'anima-core' ),
        'add_new_item'       => __( 'Añadir Nuevo Curso', 'anima-core' ),
        'edit_item'          => __( 'Editar Curso', 'anima-core' ),
        'new_item'           => __( 'Nuevo Curso', 'anima-core' ),
        'view_item'          => __( 'Ver Curso', 'anima-core' ),
        'view_items'         => __( 'Ver Cursos', 'anima-core' ),
        'search_items'       => __( 'Buscar Cursos', 'anima-core' ),
        'not_found'          => __( 'No se encontraron cursos.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay cursos en la papelera.', 'anima-core' ),
        'all_items'          => __( 'Todos los Cursos', 'anima-core' ),
        'archives'           => __( 'Archivo de Cursos', 'anima-core' ),
        'attributes'         => __( 'Atributos de Curso', 'anima-core' ),
    );
    $args_curso = array(
        'labels'             => $labels_curso,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_position'      => 5,
        'menu_icon'          => 'dashicons-welcome-learn-more', // icono de libro/educación
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'curso' ),
        'show_in_rest'       => true,   // habilitado para Gutenberg y REST API
        'taxonomies'         => array(),// taxonomías se añaden por separado abajo
        'publicly_queryable' => true,
        'capability_type'    => 'post',
        // Integración GraphQL (si plugin WPGraphQL activo):
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'Curso',
        'graphql_plural_name'=> 'Cursos',
    );
    register_post_type( 'curso', $args_curso );

    // CPT: Avatar
    $labels_avatar = array(
        'name'               => __( 'Avatares', 'anima-core' ),
        'singular_name'      => __( 'Avatar', 'anima-core' ),
        'add_new_item'       => __( 'Añadir Nuevo Avatar', 'anima-core' ),
        'edit_item'          => __( 'Editar Avatar', 'anima-core' ),
        'new_item'           => __( 'Nuevo Avatar', 'anima-core' ),
        'view_item'          => __( 'Ver Avatar', 'anima-core' ),
        'view_items'         => __( 'Ver Avatares', 'anima-core' ),
        'search_items'       => __( 'Buscar Avatares', 'anima-core' ),
        'not_found'          => __( 'No se encontraron avatares.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay avatares en la papelera.', 'anima-core' ),
        'all_items'          => __( 'Todos los Avatares', 'anima-core' ),
        'archives'           => __( 'Archivo de Avatares', 'anima-core' ),
    );
    $args_avatar = array(
        'labels'             => $labels_avatar,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_position'      => 6,
        'menu_icon'          => 'dashicons-admin-users', // icono de usuario (para avatar)
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'avatar' ),
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'capability_type'    => 'post',
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'Avatar',
        'graphql_plural_name'=> 'Avatars',
    );
    register_post_type( 'avatar', $args_avatar );

    // CPT: Proyecto
    $labels_proyecto = array(
        'name'               => __( 'Proyectos', 'anima-core' ),
        'singular_name'      => __( 'Proyecto', 'anima-core' ),
        'add_new_item'       => __( 'Añadir Nuevo Proyecto', 'anima-core' ),
        'edit_item'          => __( 'Editar Proyecto', 'anima-core' ),
        'new_item'           => __( 'Nuevo Proyecto', 'anima-core' ),
        'view_item'          => __( 'Ver Proyecto', 'anima-core' ),
        'view_items'         => __( 'Ver Proyectos', 'anima-core' ),
        'search_items'       => __( 'Buscar Proyectos', 'anima-core' ),
        'not_found'          => __( 'No se encontraron proyectos.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay proyectos en la papelera.', 'anima-core' ),
        'all_items'          => __( 'Todos los Proyectos', 'anima-core' ),
        'archives'           => __( 'Archivo de Proyectos', 'anima-core' ),
    );
    $args_proyecto = array(
        'labels'             => $labels_proyecto,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_position'      => 7,
        'menu_icon'          => 'dashicons-portfolio', // icono de portafolio
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'proyecto' ),
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'capability_type'    => 'post',
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'Proyecto',
        'graphql_plural_name'=> 'Proyectos',
    );
    register_post_type( 'proyecto', $args_proyecto );

    // CPT: Experiencia
    $labels_experiencia = array(
        'name'               => __( 'Experiencias', 'anima-core' ),
        'singular_name'      => __( 'Experiencia', 'anima-core' ),
        'add_new_item'       => __( 'Añadir Nueva Experiencia', 'anima-core' ),
        'edit_item'          => __( 'Editar Experiencia', 'anima-core' ),
        'new_item'           => __( 'Nueva Experiencia', 'anima-core' ),
        'view_item'          => __( 'Ver Experiencia', 'anima-core' ),
        'view_items'         => __( 'Ver Experiencias', 'anima-core' ),
        'search_items'       => __( 'Buscar Experiencias', 'anima-core' ),
        'not_found'          => __( 'No se encontraron experiencias.', 'anima-core' ),
        'not_found_in_trash' => __( 'No hay experiencias en la papelera.', 'anima-core' ),
        'all_items'          => __( 'Todas las Experiencias', 'anima-core' ),
        'archives'           => __( 'Archivo de Experiencias', 'anima-core' ),
    );
    $args_experiencia = array(
        'labels'             => $labels_experiencia,
        'public'             => true,
        'show_in_menu'       => true,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-vr', // icono de gafas VR (asumiendo existe, sino un icono genérico)
        'supports'           => array( 'title', 'editor', 'excerpt', 'thumbnail', 'comments' ),
        'has_archive'        => true,
        'rewrite'            => array( 'slug' => 'experiencia' ),
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'capability_type'    => 'post',
        'show_in_graphql'    => true,
        'graphql_single_name'=> 'Experiencia',
        'graphql_plural_name'=> 'Experiencias',
    );
    register_post_type( 'experiencia', $args_experiencia );
}
add_action( 'init', 'anima_register_post_types' );

// --- 2. REGISTRO DE TAXONOMÍAS PERSONALIZADAS ---

function anima_register_taxonomies() {
    // Taxonomía: Nivel (no jerárquica, similar a tags)
    $labels_nivel = array(
        'name'          => __( 'Niveles', 'anima-core' ),
        'singular_name' => __( 'Nivel', 'anima-core' ),
        'search_items'  => __( 'Buscar Niveles', 'anima-core' ),
        'all_items'     => __( 'Todos los Niveles', 'anima-core' ),
        'edit_item'     => __( 'Editar Nivel', 'anima-core' ),
        'update_item'   => __( 'Actualizar Nivel', 'anima-core' ),
        'add_new_item'  => __( 'Añadir Nuevo Nivel', 'anima-core' ),
        'new_item_name' => __( 'Nuevo Nombre de Nivel', 'anima-core' ),
        'menu_name'     => __( 'Niveles', 'anima-core' ),
    );
    $args_nivel = array(
        'labels'            => $labels_nivel,
        'public'            => true,
        'hierarchical'      => false, // false = similar a etiquetas
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'nivel' ),
        'show_in_graphql'   => true,
        'graphql_single_name' => 'Nivel',
        'graphql_plural_name' => 'Niveles',
    );
    // Asignar 'nivel' principalmente a Cursos (y experiencias, opcionalmente)
    register_taxonomy( 'nivel', array( 'curso', 'experiencia' ), $args_nivel );

    // Taxonomía: Tecnología (no jerárquica, tipo etiquetas)
    $labels_tecnologia = array(
        'name'          => __( 'Tecnologías', 'anima-core' ),
        'singular_name' => __( 'Tecnología', 'anima-core' ),
        'search_items'  => __( 'Buscar Tecnologías', 'anima-core' ),
        'all_items'     => __( 'Todas las Tecnologías', 'anima-core' ),
        'edit_item'     => __( 'Editar Tecnología', 'anima-core' ),
        'update_item'   => __( 'Actualizar Tecnología', 'anima-core' ),
        'add_new_item'  => __( 'Añadir Nueva Tecnología', 'anima-core' ),
        'new_item_name' => __( 'Nuevo Nombre de Tecnología', 'anima-core' ),
        'menu_name'     => __( 'Tecnologías', 'anima-core' ),
    );
    $args_tecnologia = array(
        'labels'            => $labels_tecnologia,
        'public'            => true,
        'hierarchical'      => false,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'tecnologia' ),
        'show_in_graphql'   => true,
        'graphql_single_name' => 'Tecnologia',
        'graphql_plural_name' => 'Tecnologias',
    );
    // Asignar 'tecnologia' a todos los CPT para clasificar por herramientas/plataformas usadas
    register_taxonomy( 'tecnologia', array( 'curso', 'avatar', 'proyecto', 'experiencia' ), $args_tecnologia );

    // Taxonomía: Modalidad (no jerárquica)
    $labels_modalidad = array(
        'name'          => __( 'Modalidades', 'anima-core' ),
        'singular_name' => __( 'Modalidad', 'anima-core' ),
        'search_items'  => __( 'Buscar Modalidades', 'anima-core' ),
        'all_items'     => __( 'Todas las Modalidades', 'anima-core' ),
        'edit_item'     => __( 'Editar Modalidad', 'anima-core' ),
        'update_item'   => __( 'Actualizar Modalidad', 'anima-core' ),
        'add_new_item'  => __( 'Añadir Nueva Modalidad', 'anima-core' ),
        'new_item_name' => __( 'Nuevo Nombre de Modalidad', 'anima-core' ),
        'menu_name'     => __( 'Modalidades', 'anima-core' ),
    );
    $args_modalidad = array(
        'labels'            => $labels_modalidad,
        'public'            => true,
        'hierarchical'      => false,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'modalidad' ),
        'show_in_graphql'   => true,
        'graphql_single_name' => 'Modalidad',
        'graphql_plural_name' => 'Modalidades',
    );
    // Asignar 'modalidad' a Curso y Experiencia (por ejemplo: online/presencial o VR/AR)
    register_taxonomy( 'modalidad', array( 'curso', 'experiencia' ), $args_modalidad );
}
add_action( 'init', 'anima_register_taxonomies' );

// --- 3. META BOXES Y CAMPOS PERSONALIZADOS ---

// Añadir Meta Box para detalles adicionales en CPT
function anima_add_meta_boxes() {
    $screens = array( 'curso', 'avatar', 'proyecto', 'experiencia' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'anima_detalles_meta',              // ID único
            'Detalles adicionales',            // Título visible del meta box
            'anima_render_meta_box',           // Callback que muestra los campos
            $screen,                           // Pantallas donde aparece (nuestros CPT)
            'normal',                          // Contexto (normal)
            'high'                             // Prioridad
        );
    }
}
add_action( 'add_meta_boxes', 'anima_add_meta_boxes' );

// Renderizar el contenido del Meta Box
function anima_render_meta_box( $post ) {
    // Usar nonce para verificación
    wp_nonce_field( 'anima_save_meta', 'anima_meta_nonce' );

    // Obtener valores actuales (si existen) de cada campo
    $instructores = get_post_meta( $post->ID, 'anima_instructores', true );
    $duracion     = get_post_meta( $post->ID, 'anima_duracion', true );
    $dificultad   = get_post_meta( $post->ID, 'anima_dificultad', true );
    $kpis         = get_post_meta( $post->ID, 'anima_kpis', true );
    $url_demo     = get_post_meta( $post->ID, 'anima_url_demo', true );

    // Campos de entrada del formulario (HTML)
    ?>
    <p><label for="anima_instructores"><strong>Instructores:</strong></label><br/>
    <input type="text" id="anima_instructores" name="anima_instructores" value="<?php echo esc_attr($instructores); ?>" style="width:100%;" placeholder="Ej: Juan Pérez, Ana Gómez"/></p>

    <p><label for="anima_duracion"><strong>Duración:</strong></label><br/>
    <input type="text" id="anima_duracion" name="anima_duracion" value="<?php echo esc_attr($duracion); ?>" style="width:100%;" placeholder="Ej: 5 semanas, 20 horas"/></p>

    <p><label for="anima_dificultad"><strong>Dificultad:</strong></label><br/>
    <input type="text" id="anima_dificultad" name="anima_dificultad" value="<?php echo esc_attr($dificultad); ?>" style="width:100%;" placeholder="Ej: Básico, Intermedio, Avanzado"/></p>

    <p><label for="anima_kpis"><strong>KPIs / Datos clave:</strong></label><br/>
    <textarea id="anima_kpis" name="anima_kpis" style="width:100%;" rows="3" placeholder="Ej: 1200 alumnos formados&#10;95% tasa de finalización"><?php echo esc_textarea($kpis); ?></textarea></p>

    <p><label for="anima_url_demo"><strong>URL demo 3D / Enlace:</strong></label><br/>
    <input type="url" id="anima_url_demo" name="anima_url_demo" value="<?php echo esc_attr($url_demo); ?>" style="width:100%;" placeholder="Ej: https://midominio.com/demo/experiencia"/></p>

    <p><em>Complete estos campos para añadir información extra que se mostrará en la página del contenido.</em></p>
    <?php
}

// Guardar los campos meta al guardar el post
function anima_save_post_meta( $post_id ) {
    // Verificar el nonce para seguridad
    if ( ! isset($_POST['anima_meta_nonce']) || ! wp_verify_nonce( $_POST['anima_meta_nonce'], 'anima_save_meta' ) ) {
        return;
    }
    // Evitar auto guardados y revisiones
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;

    // Verificar permisos del usuario (que pueda editar el post)
    if ( isset($_POST['post_type']) && ! current_user_can( 'edit_' . $_POST['post_type'], $post_id ) ) {
        return;
    }

    // Sanitizar y guardar cada campo si está presente en la petición
    $fields = array('anima_instructores', 'anima_duracion', 'anima_dificultad', 'anima_kpis', 'anima_url_demo');
    foreach ( $fields as $field ) {
        if ( isset( $_POST[$field] ) ) {
            // Sanitizar según el tipo de campo
            $value = $_POST[$field];
            if ( $field == 'anima_url_demo' ) {
                $value = esc_url_raw( $value );
            } else {
                $value = sanitize_textarea_field( $value );
            }
            update_post_meta( $post_id, $field, $value );
        }
    }
}
add_action( 'save_post', 'anima_save_post_meta' );

// --- 4. SHORTCODE [anima_gallery] ---

function anima_gallery_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'type'  => 'curso',   // tipo de CPT a mostrar
        'limit' => 6,         // número de ítems a mostrar
    ), $atts, 'anima_gallery' );

    $post_type = sanitize_text_field( $atts['type'] );
    $limit = intval( $atts['limit'] );

    // Query para obtener posts del tipo especificado
    $query = new WP_Query( array(
        'post_type'      => $post_type,
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
    ) );

    if ( ! $query->have_posts() ) {
        return '<div class="anima-gallery no-items">No hay elementos disponibles.</div>';
    }

    // Construir HTML de la galería
    $output = '<div class="anima-gallery">';
    while( $query->have_posts() ) {
        $query->the_post();
        $output .= '<div class="anima-item">';
        // Imagen destacada si existe
        if ( has_post_thumbnail() ) {
            $output .= '<a href="' . get_permalink() . '">' . get_the_post_thumbnail( get_the_ID(), 'thumbnail' ) . '</a>';
        }
        // Título
        $output .= '<h4><a href="' . get_permalink() . '">' . get_the_title() . '</a></h4>';
        $output .= '</div>';
    }
    wp_reset_postdata();
    $output .= '</div>';

    return $output;
}
add_shortcode( 'anima_gallery', 'anima_gallery_shortcode' );

// --- 5. API REST Personalizada (Contacto) ---

// Registrar la ruta REST al inicializar API
function anima_register_api_routes() {
    register_rest_route( 'anima/v1', '/contacto', array(
        'methods'             => 'POST',
        'callback'            => 'anima_handle_contact_form',
        'permission_callback' => '__return_true', // Público; en prod, agregar validación (nonce, captcha, etc.)
    ) );
}
add_action( 'rest_api_init', 'anima_register_api_routes' );

// Callback para manejar envío de formulario de contacto
function anima_handle_contact_form( $request ) {
    // Obtener parámetros de la petición
    $name    = sanitize_text_field( $request->get_param('nombre') );
    $email   = sanitize_email( $request->get_param('email') );
    $message = sanitize_textarea_field( $request->get_param('consulta') );

    if ( empty($name) || empty($email) || empty($message) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'error'   => 'Por favor, complete todos los campos.',
        ), 400 );
    }
    if ( ! is_email( $email ) ) {
        return new WP_REST_Response( array(
            'success' => false,
            'error'   => 'El email proporcionado no es válido.',
        ), 400 );
    }

    // Enviar email (placeholder: se envía al administrador del sitio)
    $admin_email = get_option( 'admin_email' );
    $subject = 'Nuevo mensaje de contacto de ' . get_bloginfo('name');
    $body = "Has recibido un nuevo mensaje de contacto a través de la web:\n\n";
    $body .= "Nombre: $name\n";
    $body .= "Email: $email\n";
    $body .= "Consulta:\n$message\n";
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    wp_mail( $admin_email, $subject, $body, $headers );

    // Respuesta de éxito
    return new WP_REST_Response( array(
        'success' => true,
        'message' => 'Gracias, su mensaje ha sido enviado correctamente.'
    ), 200 );
}

// --- 6. INTEGRACIONES FUTURAS (Hooks placeholder) ---

// TODO: Integración futura con BuddyPress (por ejemplo, enlazar CPT a actividades o perfiles de usuarios).
// Esto podría implicar usar hooks de BuddyPress para registrar tipos de actividad personalizados cuando se publica un curso, etc.

// TODO: Integración futura con WPDiscuz: Al tener comentarios habilitados en CPT, WPDiscuz reemplazará el formulario de comentarios estándar. 
// Si necesitáramos customizaciones, podríamos usar sus hooks/filtros.

// TODO: Integración con WPGraphQL: Ya marcamos show_in_graphql en CPT/tax. Podríamos añadir registro de campos meta en el esquema GraphQL usando register_graphql_field si se instala WPGraphQL, etc.

// --- 7. IMPORTADOR / CONTENIDO DEMO (opcional) ---

// TODO: Implementar una función para generar contenido de demostración (cursos, proyectos, avatares, experiencias de ejemplo) 
// quizás usando datos predefinidos o desde un archivo XML. 
// Esto se podría ejecutar en la activación del plugin con register_activation_hook, aunque se haría opcional para no llenar de datos si no se requiere.
