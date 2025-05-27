<?php
/*
Plugin Name: Api Sections
Description: Servicios referente al contenido de las secciones
Version: 1.0
Author: Leonardo Melo
*/

add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v1', 'pages', [
        'methods'  => 'GET',
        'callback' => 'get_pages_w',
        'permission_callback' => '__return_true',
    ]);
});


add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v1', 'sections', [
        'methods'  => 'GET',
        'callback' => 'get_sections',
        'permission_callback' => '__return_true',
    ]);
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', 'comentarios', [
        'methods'  => 'GET',
        'callback' => 'get_comment_c',
        'permission_callback' => '__return_true',
    ]);
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', 'centenario', [
        'methods'  => 'GET',
        'callback' => 'get_centenario_c',
        'permission_callback' => '__return_true',
    ]);
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'custom/v1', 'centenario', [
        'methods'  => 'GET',
        'callback' => 'get_centenario_c2',
        'permission_callback' => '__return_true',
    ]);
});

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/search', array(
        'methods' => 'GET',
        'callback' => 'custom_search_endpoint',
        'permission_callback' => '__return_true'
    ));
});

function custom_search_endpoint($request) {
    $parameters = $request->get_query_params();
    $search_term = isset($parameters['s']) ? sanitize_text_field($parameters['s']) : '';
    $post_types = isset($parameters['post_types']) ? explode(',', $parameters['post_types']) : array('post', 'page');
    $per_page = isset($parameters['per_page']) ? (int)$parameters['per_page'] : 20;
    $page = isset($parameters['page']) ? (int)$parameters['page'] : 1;

    if (empty($search_term)) {
        return new WP_Error('no_search_term', 'Debes proporcionar un término de búsqueda', array('status' => 400));
    }

    $args = array(
        's' => $search_term,
        'post_type' => $post_types,
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish'
    );

    $query = new WP_Query($args);
    $results = array();
    

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_type = get_post_type();
             // Inicializar datos de categoría
            $category_info = array(
                'name' => 'sin categoría',
                'slug' => ''
            );
            
            if ($post_type === 'post') {
                $categories = get_the_category();
                if (!empty($categories)) {
                    // Buscar categoría padre
                    foreach ($categories as $cat) {
                        if ($cat->parent == 0) {
                            $category_info['name'] = $cat->name;
                            $category_info['slug'] = $cat->slug;
                            break;
                        }
                    }
                    // Si no encontró padre, usar la primera categoría
                    if ($category_info['name'] === 'sin categoría') {
                        $category_info['name'] = $categories[0]->name;
                        $category_info['slug'] = $categories[0]->slug;
                    }
                }
            } elseif (taxonomy_exists($post_type . '_category')) {
                // Para CPTs con taxonomía específica
                $terms = get_the_terms(get_the_ID(), $post_type . '_category');
                if (!empty($terms)) {
                    $category_info['name'] = $terms[0]->name;
                    $category_info['slug'] = $terms[0]->slug;
                }
            }
            
            $result = array(
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'slug' => get_post_field('post_name'),
                'content' => get_the_content(),
                'excerpt' => get_the_excerpt(),
                'category' => $category_info,
                'type' => $post_type,
                'url' => get_permalink(),
                'date' => get_the_date('Y-m-d H:i:s')
            );
            
            // Añadir campos específicos según el tipo de contenido
            if ($post_type === 'page') {
                $result['template'] = get_page_template_slug();
            }
            
            // Puedes añadir más campos personalizados aquí
            
            $results[] = $result;
        }
    }

    wp_reset_postdata();

    $obj = [];
    $sidebar = get_relation_menu_sidebar();
    foreach($results as $item){
        $breadcrumb = '';
        $flag = true;
        $data = [];
        $data['title'] = $item['title'];
        $data['description'] = $item['excerpt'];
        $breadcrumb= '';
        $breadcrumb = null;
        if($item['type'] == 'page'){
            foreach ($sidebar['data'] as $menu) {
                if (!empty($menu['items']) && find_breadcrumb_recursive2($menu['items'], $item['slug'], $breadcrumb)) {
                    break; // Si encontramos, salimos del bucle
                }              
            }
            if(!empty($breadcrumb)){
                $breadcrumb = '/'.implode('/', $breadcrumb).'/'.$item['slug'];
            }
            if($item['slug'] == mb_strtolower('empresas', 'UTF-8')){
                $breadcrumb = '/empresas';
            }
            if($item['slug'] == mb_strtolower('personas', 'UTF-8')){
                $breadcrumb = '/personas';
            }
            if(empty($breadcrumb)){
                $breadcrumb = '/'.$item['slug'];
            }
            $breadcrumb = replace_character_special($breadcrumb);
            $breadcrumb = preg_replace('/\s+/', '-', mb_strtolower($breadcrumb, 'UTF-8'));
            $data['url'] = $breadcrumb;

        }else{
             $data['url'] = '/'.'conoce/'.$item['category']['slug'].'/'.$item['slug'];
        }
        if($flag){
            $obj[] = $data;
        }

    }

    $section_post_head = get_sections_head('todos');
    $posts = [];
    $items_content = [];
    $items_data = [];
    $item_data['padding'] = false;
    $item_data['background_image'] = '';
    $item_data['background_gradient'] = '';
    $dataO = [];
    $dataO['acf_fc_layout'] = 'searchResult';
    $dataO['result_search'] = $obj;
    $item_data['item_content'] = $dataO;
    $items_content[] = $item_data;

    if(!empty( $section_post_head['footerA'] )){
        $items_data = [];
        $item_data['padding'] = false;
        $item_data['background_image'] = '';
        $item_data['background_gradient'] = '';
        $footer = [];
        $item_data['section_field'] = $section_post_head['footerA'];
        $data = [];
        foreach ($section_post_head['footerB'] as $item) {
            $data[] = $item;
        }
        $item_data['item_content'] = $data;
        $items_content[] = $item_data;
    }

    $posts['item_content'] = $items_content;

    $response = new WP_REST_Response([
        'message' => 'OK',
        'data' => $posts
    ], 200);

    $response->set_headers(array(
        'X-WP-Total' => $query->found_posts,
        'X-WP-TotalPages' => $query->max_num_pages
    ));

    return $response;
}

function get_relation_menu_sidebar() {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v1/menu/sidebar-personas';

    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);


    return $data;
}

function get_all_slugs_endpoint($request) {
    $parameters = $request->get_query_params();
    $post_types = isset($parameters['post_types']) ? explode(',', $parameters['post_types']) : array('page', 'post');
    $per_page = isset($parameters['per_page']) ? (int)$parameters['per_page'] : -1;
    $page = isset($parameters['page']) ? (int)$parameters['page'] : 1;

    $args = array(
        'post_type' => $post_types,
        'posts_per_page' => $per_page,
        'paged' => $page,
        'post_status' => 'publish',
        'fields' => 'ids',
    );

    $query = new WP_Query($args);
    $pages = array();
    $posts = array();

    if ($query->have_posts()) {
        foreach ($query->posts as $post_id) {
            $post_type = get_post_type($post_id);
            $post_slug = get_post_field('post_name', $post_id);
            
            // Inicializamos la variable para category_slug que usaremos en processed_url
            $category_slug = '';
            
            if ($post_type === 'post') {
                $categories = get_the_category($post_id);
                if (!empty($categories)) {
                    // Buscamos categoría padre o usamos la primera
                    foreach ($categories as $cat) {
                        if ($cat->parent == 0) {
                            $category_slug = $cat->slug;
                            break;
                        }
                    }
                    if (empty($category_slug)) {
                        $category_slug = $categories[0]->slug;
                    }
                }
            } elseif (taxonomy_exists($post_type . '_category')) {
                $terms = get_the_terms($post_id, $post_type . '_category');
                if (!empty($terms)) {
                    $category_slug = $terms[0]->slug;
                }
            }
            
            $result = array(
                'id' => $post_id,
                'title' => get_the_title($post_id),
                'slug' => $post_slug,
                'type' => $post_type,
                'url' => get_permalink($post_id),
                'processed_url' => '', // Lo calcularemos después
                '_category_slug' => $category_slug // Campo temporal para el cálculo
            );
            
            if ($post_type === 'page') {
                $pages[] = $result;
            } else {
                $posts[] = $result;
            }
        }
    }

    wp_reset_postdata();

    // Procesamos las URLs y ordenamos
    $sidebar = get_relation_menu_sidebar();
    
    // Procesar páginas primero
    $pages_with_url = array();
    $pages_without_url = array();
    
    foreach($pages as $item) {
        $breadcrumb = null;
        $data = $item;
        
        foreach ($sidebar['data'] as $menu) {
            if (!empty($menu['items']) && find_breadcrumb_recursive2($menu['items'], $item['slug'], $breadcrumb)) {
                break;
            }              
        }
        
        if(!empty($breadcrumb)) {
            $breadcrumb = '/'.implode('/', $breadcrumb).'/'.$item['slug'];
        }
        
        if($item['slug'] == mb_strtolower('empresas', 'UTF-8')) {
            $breadcrumb = '/empresas';
        }
        
        if($item['slug'] == mb_strtolower('personas', 'UTF-8')) {
            $breadcrumb = '/personas';
        }
        if(empty($breadcrumb)){
                $breadcrumb = '/'.$item['slug'];
            }
            $breadcrumb = replace_character_special($breadcrumb);
        
        $data['processed_url'] = !empty($breadcrumb) ? 
            preg_replace('/\s+/', '-', mb_strtolower($breadcrumb, 'UTF-8')) : '';
        
        if (!empty($data['processed_url'])) {
            $pages_with_url[] = $data;
        } else {
            $pages_without_url[] = $data;
        }
    }
    
    // Procesar posts (usando _category_slug temporal)
    $processed_posts = array();
    foreach($posts as $item) {
        $data = $item;
        $data['processed_url'] = '/'.'conoce/'.$item['_category_slug'].'/'.$item['slug'];
        $processed_posts[] = $data;
    }
    $field = 'todos';
    $cat_all = get_cat_all($field);
    $field = 'aprende-con-mercantil';
    $cat_all_learn = get_cat_all($field);
    $cat_home = [];
    $dataH = [];
    $dataH['title'] = 'Conoce';
    $dataH['slug'] = 'todos';
    $dataH['type'] =  'category-post';
    $dataH['processed_url'] =  '/conoce/todos';
    $cat_home[] = $dataH;
    foreach($cat_all as $itemH){
        $dataH = [];
        $dataH['title'] = $itemH['name'];
        $dataH['slug'] = $itemH['real-slug'];
        $dataH['type'] =  'category-post';
        $dataH['processed_url'] =  $itemH['slug'];
        $cat_home[] = $dataH;
    }
    foreach($cat_all_learn as $itemH){
        $dataH = [];
        $dataH['title'] = $itemH['name'];
        $dataH['slug'] = $itemH['real-slug'];
        $dataH['type'] =  'category-post';
        $dataH['processed_url'] =  $itemH['slug'];
        $cat_home[] = $dataH;
    }

    // Combinar resultados en el orden solicitado
    $final_results = array_merge(
        $pages_with_url,
        $pages_without_url,
        $processed_posts,
        $cat_home
    );

    // Limpiar la respuesta final (eliminar campos temporales)
    $clean_results = array();
    foreach ($final_results as $item) {
        $clean_results[] = array(
            'title' => $item['title'],
            'slug' => $item['slug'],
            'type' => $item['type'],
            'path' => !empty($item['processed_url']) ? $item['processed_url'] : $item['url']
        );
    }

    $response = new WP_REST_Response([
        'message' => 'OK',
        'data' => $clean_results
    ], 200);

    $response->set_headers(array(
        'X-WP-Total' => $query->found_posts,
        'X-WP-TotalPages' => $query->max_num_pages
    ));

    return $response;
}

// Registrar el endpoint
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/all-slugs', array(
        'methods' => 'GET',
        'callback' => 'get_all_slugs_endpoint',
        'permission_callback' => '__return_true'
    ));
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v3', '/centenario', [
        'methods' => 'GET',
        'callback' => function($request) {
            // Configurar parámetros de búsqueda
            $params = $request->get_params();
            $args = [
                'post_type'      => 'centenario',
                'posts_per_page' => isset($params['per_page']) ? (int)$params['per_page'] : 200,
                'post_status'    => 'publish',
                'orderby'        => 'date', // Siempre ordenar por fecha de publicación
                'order'          => 'ASC'  // Orden descendente (más reciente primero)
            ];

            // Filtrar por categoría si se especifica
            if (isset($params['category'])) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => sanitize_text_field($params['category'])
                    ]
                ];
            }

            // Resto del código permanece igual...
            $query = new WP_Query($args);
            $data = [];

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();
                    $post_id = get_the_ID();
                    
                    // Obtener campos ACF del post principal
                    $acf_data = function_exists('get_fields') ? get_fields($post_id) : [];
                    if (!$acf_data) $acf_data = [];
                    
                    // Imagen principal
                    $main_image = '';
                    if (!empty($acf_data['list_image']) && is_array($acf_data['list_image'])) {
                        $main_image = $acf_data['list_image'][0]['image']['url'] ?? '';
                    }

                    // Procesar relaciones
                    $related_data = [];
                    if (!empty($acf_data['relation'])) {
                        $relations = is_array($acf_data['relation']) ? $acf_data['relation'] : [$acf_data['relation']];
                        
                        foreach ($relations as $related_post) {
                            $related_id = is_object($related_post) ? $related_post->ID : $related_post;
                            
                            // Verificar que el post relacionado existe
                            if (!get_post_status($related_id)) continue;
                            
                            // Obtener ACF del post relacionado
                            $related_acf = function_exists('get_fields') ? get_fields($related_id) : [];
                            if (!$related_acf) $related_acf = [];
                            
                            // Estructura básica del post relacionado
                            $related_data[] = $related_acf;
                        }
                    }
                    // Estructurar la respuesta
                    $data[] = [
                        'year'          => $acf_data['year'] ?? '',
                        'image'         => $main_image,
                         'date'          => get_the_date('Y-m-d H:i:s'),
                        'item_content'  => [
                            'image'    => $main_image,
                            'description'   => $acf_data['description'] ?? '',
                            'comments'     => $related_data
                        ]
                    ];
                }
                wp_reset_postdata();
            }

            $hitos = [];
            $hitos = $data;
             // Ordenar los hitos por año ascendente
        usort($hitos, function($a, $b) {
            return (int)$a['year'] <=> (int)$b['year'];
        });
        
        // Agregar campo 'number' que se reinicia por año
        $currentYear = null;
        $counter = 0;
        
        foreach ($hitos as &$hito) {
            if ($hito['year'] !== $currentYear) {
                $currentYear = $hito['year'];
                $counter = 1;
            } else {
                $counter++;
            }
            
            $hito['number'] = $counter;
        }
        unset($hito);
        
        // Ahora agrupamos por año
        $groupedByYear = [];
        foreach ($hitos as $hito) {
            $year = $hito['year'];
            
            // Eliminamos el campo year del hito individual (ya está en el grupo)
            unset($hito['year']);
            
            if (!isset($groupedByYear[$year])) {
                $groupedByYear[$year] = [
                    'year' => (int)$year,
                    'content' => []
                ];
            }
            
            $groupedByYear[$year]['content'][] = $hito;
        }
        
        // Convertimos el array asociativo a indexado
        $groupedData = array_values($groupedByYear);

            return new WP_REST_Response([
                'success'   => true,
                'data'      => $groupedData
            ], 200);
        },
        'permission_callback' => '__return_true'
    ]);
});

function get_centenario_c2($request) {
    // 1. Obtener datos iniciales
    $centenarioResponse = get_centenario_all();
    
    // Depuración: Ver qué contiene la respuesta inicial
    error_log('Respuesta inicial: ' . print_r($centenarioResponse, true));

    if (empty($centenarioResponse) || !is_array($centenarioResponse)) {
        error_log('Datos vacíos o no es array');
        return new WP_REST_Response([
            'message' => 'No se encontraron datos',
            'data' => []
        ], 404);
    }

    // 2. Procesar hitos
    $hitos = [];
    foreach ($centenarioResponse as $item) {
        // Validar estructura del item
        if (!isset($item['year'], $item['list_image'], $item['description'], $item['relation'])) {
            error_log('Item con estructura incorrecta: ' . print_r($item, true));
            continue;
        }

        try {
            $comments = is_array($item['relation']) ? implode(', ', $item['relation']) : '';
            
            $hito = [
                'year' => substr($item['year'], 0, 4),
                'image' => $item['list_image'][0]['image'] ?? '',
                'item_content' => [
                    'image' => $item['list_image'][0]['image'] ?? '',
                    'description' => $item['description'],
                    'comments' => ''
                ]
            ];
            
            $hitos[] = $hito;
        } catch (Exception $e) {
            error_log('Error procesando item: ' . $e->getMessage());
            continue;
        }
    }

    // Depuración: Ver hitos antes de ordenar
    error_log('Hitos antes de ordenar: ' . print_r($hitos, true));

    // 3. Ordenar hitos por año
    usort($hitos, function($a, $b) {
        return (int)$a['year'] <=> (int)$b['year'];
    });

    // 4. Agrupar por año
    $groupedData = [];
    foreach ($hitos as $hito) {
        $year = (int)$hito['year'];
        
        if (!isset($groupedData[$year])) {
            $groupedData[$year] = [
                'year' => $year,
                'content' => []
            ];
        }
        
        // Eliminar year del hito individual
        unset($hito['year']);
        
        // Agregar número secuencial
        $hito['number'] = count($groupedData[$year]['content']) + 1;
        $groupedData[$year]['content'][] = $hito;
    }

    // Depuración: Ver datos finales
    error_log('Datos finales: ' . print_r($groupedData, true));

    // 5. Retornar respuesta
    return new WP_REST_Response([
        'message' => 'OK',
        'data' => array_values($groupedData)
    ], 200);
}



function get_centenario_c($request) {
    $centenarioResponse = get_centenario_all();
    
    if(!empty($centenarioResponse)) {
        $hitos = [];
        
        // Primero procesamos todos los hitos como antes
        foreach($centenarioResponse as $item) {
            $obj = [];
            $obj['year'] = substr($item['year'], 0, 4);
            $obj['image'] = $item['list_image'][0]['image'];
            
            $data = [];
            $data['image'] = $item['list_image'][0]['image'];
            $data['description'] = $item['description'];           
            $comments = '';
            
            foreach($item['relation'] as $idx) {
                $comments .= $idx . ', ';
            }
            $comments = rtrim($comments, ', ');
            $data['comments'] = get_comments_by_idx($comments);
            
            $obj['item_content'] = $data;
            $hitos[] = $obj;
        }
        
        // Ordenar los hitos por año ascendente
        usort($hitos, function($a, $b) {
            return (int)$a['year'] <=> (int)$b['year'];
        });
        
        // Agregar campo 'number' que se reinicia por año
        $currentYear = null;
        $counter = 0;
        
        foreach ($hitos as &$hito) {
            if ($hito['year'] !== $currentYear) {
                $currentYear = $hito['year'];
                $counter = 1;
            } else {
                $counter++;
            }
            
            $hito['number'] = $counter;
        }
        unset($hito);
        
        // Ahora agrupamos por año
        $groupedByYear = [];
        foreach ($hitos as $hito) {
            $year = $hito['year'];
            
            // Eliminamos el campo year del hito individual (ya está en el grupo)
            unset($hito['year']);
            
            if (!isset($groupedByYear[$year])) {
                $groupedByYear[$year] = [
                    'year' => (int)$year, // Convertimos a int como en tu ejemplo
                    'content' => []
                ];
            }
            
            $groupedByYear[$year]['content'][] = $hito;
        }
        
        // Convertimos el array asociativo a indexado
        $groupedData = array_values($groupedByYear);
        
        return new WP_REST_Response([
            'message' => 'OK', // Cambiado de 'status' a 'message' como en tu ejemplo
            'data' => $groupedData
        ], 200);
    }
    
    return new WP_REST_Response([
        'message' => 'No data found',
        'data' => []
    ], 400);
}

// El callback donde se maneja la solicitud
function get_comment_c($request) {
    $comments = get_comments_all();
    if(empty($comments)) {
        $comments = null;
    }

    return new WP_REST_Response( [
        'status' => 200,
        'data' => $comments
    ], 200 );
}

// Función para obtener comentarios 
function get_comments_all() {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/comentario';
    
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    $data_acf = [];

    foreach ($data as $item) {  // Fixed the loop syntax
        if (isset($item['acf'])) {
            $data_acf[] = $item['acf'];
        }
    }
  
    return $data_acf;
}


// Función para obtener comentarios 
function get_comments_by_idx($idx) {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/comentario?include=' . $idx;
    
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    $data_acf = [];

    foreach ($data as $item) {  // Fixed the loop syntax
        if (isset($item['acf'])) {
            $data_acf[] = $item['acf'];
        }
    }
  
    return $data_acf;
}

// Función para obtener hitos centenario 
function get_centenario_all() {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/centenario?per_page=100';
    
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body, true );
    $data_acf = [];

    foreach ($data as $item) {  // Fixed the loop syntax
        if (isset($item['acf'])) {
            $data_acf[] = $item['acf'];
        }
    }
  
    return $data_acf;
}

// El callback donde se maneja la solicitud
function get_pages_w($request) {
    $param_slug = $request->get_param( 'slug' );

    if ( empty( $param_slug ) ) {
        return new WP_REST_Response(response_error("No se encontró el parámetro slug"), 400);
    }
    
    $pages = get_pages_by_slug($param_slug);
    $breadcrumb = '';
    $breadcrumbJ = '';
    if(!empty($pages)){
        $breadcrumbJ = get_relation_menu($param_slug);
    }
    if(!empty($breadcrumbJ)){
        $breadcrumb = '/'.implode('/', $breadcrumbJ).'/'.$param_slug;
    }
    if($param_slug == mb_strtolower('empresas', 'UTF-8')){
        $breadcrumb = '/empresas';
    }
    if($param_slug == mb_strtolower('personas', 'UTF-8')){
        $breadcrumb = '/personas';
    }
    if(empty($breadcrumbJ)){
        $breadcrumb = '/'.$slug;
    }
    $breadcrumb = preg_replace('/\s+/', '-', mb_strtolower($breadcrumb, 'UTF-8'));
    $pages[0]['acf']['breadscrumb'] = $breadcrumb; 

    return new WP_REST_Response($pages, 200 );
}

function get_normal_breadScrumb($slug){
    $breadcrumb = '';
    $breadcrumbJ = '';
    if(!empty($slug)){
        $breadcrumbJ = get_relation_menu($slug);
    }
    if(!empty($breadcrumbJ)){
        $breadcrumb = '/'.implode('/', $breadcrumbJ).'/'.$slug;
    }
    if($slug == mb_strtolower('empresas', 'UTF-8')){
        $breadcrumb = '/empresas';
    }
    if($slug == mb_strtolower('personas', 'UTF-8')){
        $breadcrumb = '/personas';
    }
    if(empty($breadcrumbJ)){
        $breadcrumb = '/'.$slug;
    }
    $breadcrumb = replace_character_special($breadcrumb);
    $breadcrumb = preg_replace('/\s+/', '-', mb_strtolower($breadcrumb, 'UTF-8'));
    return $breadcrumb;
}



// El callback donde se maneja la solicitud
function get_sections( $request ) {
    $section_id_param = $request->get_param( 'id' );

    if ( empty( $section_id_param ) ) {
        return new WP_REST_Response(response_error("No se encontró el parámetro id"), 400);
    }
    
    $section_ids_array = explode(',', $section_id_param);
    $sections_by_list_id = get_sections_by_list_id($section_ids_array);
    $sections = [];

    foreach ($section_ids_array as $section_id) { 
        foreach ($sections_by_list_id as $section) {
            if ($section['id'] == $section_id) {    
                // Validar y obtener formularios desde Forminator
                if (isset($section['acf']['item_content']) && is_array($section['acf']['item_content'])) {
                    $has_form_relations = false;
                    $dynamic_forms = [];

                    foreach ($section['acf']['item_content'] as $item_index => $item) {

                        /*if (isset($item['items']) && is_array($item['items'])) {
                            $card_items_content = [];

                            foreach ($item['items'] as $card_item_id) {
                                // Obtener el contenido de la entrada referente al ID
                                $card_item_content = get_post($card_item_id['action'][0]);
                                
                                if ($card_item_content) {
                                    $data = [];
                                    $data['type'] = 'page';
                                    $data['slug'] =  $card_item_content->post_name;
                                        // Crear un array con los campos no vacíos
                                        $card_item_data = [
                                            'icon' => $card_item_id['icon'],
                                            'alt_icon' => $card_item_id['alt_icon'],
                                            'title' => $card_item_id['title'],
                                            'action' => $data, // Incluir el slug
                                        ];
                                        // Agregar el contenido al array
                                        $card_items_content[] = $card_item_data;
                                    
                                }
                            }

                            // Reemplazar card_item con el contenido obtenido
                            $section['acf']['item_content'][$item_index]['items'] = $card_items_content;
                        }*/

                        // Verificar si existe el parámetro card_item y es una lista de IDs
                        if (isset($item['card_item']) && is_array($item['card_item'])) {
                            $card_items_content = [];

                            foreach ($item['card_item'] as $card_item_id) {
                                // Obtener el contenido de la entrada referente al ID
                                $card_item_content = get_post($card_item_id);

                                if ($card_item_content) {
                                    // Obtener los campos ACF de la entrada
                                    $acf_fields = get_fields($card_item_id);

                                    // Verificar si existe item_content dentro de ACF
                                    if ($acf_fields && isset($acf_fields['item_content']) && is_array($acf_fields['item_content'])) {
                                        // Obtener el primer elemento de item_content (asumiendo que solo hay uno)
                                        $item_content = $acf_fields['item_content'][0];

                                        // Crear un array con los campos no vacíos
                                        $card_item_data = [
                                            'slug' => $card_item_content->post_name, // Incluir el slug
                                        ];

                                        // Agregar solo los campos no vacíos
                                        if (!empty($item_content['title'])) {
                                            $card_item_data['title'] = $item_content['title'];
                                        }
                                        if (!empty($item_content['description'])) {
                                            $card_item_data['description'] = $item_content['description'];
                                        }
                                        if (!empty($item_content['url'])) {
                                            $card_item_data['url'] = $item_content['url'];
                                        }
                                        if (!empty($item_content['icon'])) {
                                            $card_item_data['icon'] = $item_content['icon'];
                                        }
                                        if (!empty($item_content['image'])) {
                                            $card_item_data['image'] = $item_content['image'];
                                        }

                                        // Agregar el contenido al array
                                        $card_items_content[] = $card_item_data;
                                    }
                                }
                            }

                            // Reemplazar card_item con el contenido obtenido
                            $section['acf']['item_content'][$item_index]['card_item'] = $card_items_content;
                        }

                         // Verificar si existe el parámetro list_button 
                         if (isset($item['list_button']) && is_array($item['list_button'])) {
                            $items_content = [];
                            foreach ($item['list_button'] as $button) {
                                if (isset($button['action'][0]) && is_array($button['action'][0])) {
                                   if($button['action'][0]['acf_fc_layout'] == 'POST'){
                                        if (isset($button['action'][0]['url']) && is_array($button['action'][0]['url'])) {
                                            $post_id = $button['action'][0]['url'][0]; // Obtener el ID del post
                                            $post = get_post($post_id); // Obtener el post
                            
                                            if ($post) {
                                                // Verificar el tipo de post
                                                $post_type = $post->post_type;
                                                
                                                if ($post_type == 'post') {
                                                    // Es una entrada (post)
                                                    $categories = get_the_category($post_id);
                                                    $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                                    $button['action'][0]['type'] = 'post';
                                                    $button['action'][0]['url'] = 'conoce'.$category_slug.'/'.$post->post_name;
                                                } elseif ($post_type == 'page') {
                                                    // Es una página (page)
                                                    $button['action'][0]['type'] = 'page';
                                                    $breadcrumb = get_normal_breadScrumb($post->post_name);
                                                    $button['action'][0]['url'] = $breadcrumb;
                                                }else{
                                                    $button['action'][0]['url'] = '';
                                                }
                                                
                                                // Reemplazar el array 'url' con el slug
                                               
                                            } else {
                                                // Si el post no existe, dejar el campo 'url' vacío
                                                $button['action'][0]['url'] = '';
                                            }
                                        }
                                    } 
                                }
                                $items_content[] = $button;
                            }
                            $section['acf']['item_content'][$item_index]['list_button'] = $items_content;
                        }

                        // Verificar si existe el parámetro list_card 
                        if (isset($item['list_card']) && is_array($item['list_card'])) {
                            $items_content = [];
                            $items_content2 = [];
                            foreach ($item['list_card'] as $button) {
                                
                                if(!empty($button['card_type'])){
                                    foreach ($button['card_type'] as $obj) {
                                        if($obj['acf_fc_layout'] == 'CardsMediaContent'){
                                            if (isset($obj['action'][0]) && !is_array($obj['action'][0])) {
                                                // Aquí asumimos que action[0] contiene el ID del post
                                                $post_id = $obj['action'][0]; // Obtener el ID del post
                                                $post = get_post($post_id); // Obtener el post
                                                
                                                if ($post) {
                                                    $data = [];
                                                    $data2 = [];
                                                    $post_type = $post->post_type;
                                                    if ($post_type == 'post') {
                                                        // Obtener la primera categoría del post
                                                        $categories = get_the_category($post_id);
                                                        $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                                        $data['type'] = 'post';
                                                        $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }elseif ($post_type == 'page'){
                                                        $breadcrumb = get_normal_breadScrumb($post->post_name);
                                                        $data['type'] = 'page';
                                                        $data['slug'] = $breadcrumb;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }else{
                                                        $obj['action'] = ''; 
                                                    }
                                                    
                                                }   else {
                                                    // Si el post no existe, dejar vacío
                                                    $obj['action'] = '';
                                                }
                                            } elseif (is_array($obj['action'][0])) {
                                                // Si ya es un array (por si acaso)
                                                $post_id = $obj['action'][0][0]; // Obtener el ID del post
                                                $post = get_post($post_id); // Obtener el post
                                                
                                               if ($post) {
                                                    $data = [];
                                                    $data2 = [];
                                                    $post_type = $post->post_type;
                                                    if ($post_type == 'post') {
                                                        // Obtener la primera categoría del post
                                                        $categories = get_the_category($post_id);
                                                        $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                                        $data['type'] = 'post';
                                                        $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }elseif ($post_type == 'page'){
                                                        $breadcrumb = get_normal_breadScrumb($post->post_name);
                                                        $data['type'] = 'page';
                                                        $data['slug'] = $breadcrumb;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }else{
                                                        $obj['action'] = ''; 
                                                    }
                                                    
                                                } else {
                                                    // Si el post no existe, dejar vacío
                                                    $obj['action'] = '';
                                                }
                                            }
                                        }
                                        if($obj['acf_fc_layout'] == 'CardsContent' || $obj['acf_fc_layout'] == 'CardsContentVideo' || $obj['acf_fc_layout'] == 'CardsContentHorizontal'){
                                            if (isset($obj['action'][0]) && !is_array($obj['action'][0])) {
                                                // Aquí asumimos que action[0] contiene el ID del post
                                                $post_id = $obj['action'][0]; // Obtener el ID del post
                                                $post = get_post($post_id); // Obtener el post
                                                
                                               if ($post) {
                                                    $data = [];
                                                    $data2 = [];
                                                    $post_type = $post->post_type;
                                                    if ($post_type == 'post') {
                                                        // Obtener la primera categoría del post
                                                        $categories = get_the_category($post_id);
                                                        $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                                        $data['type'] = 'post';
                                                        $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }elseif ($post_type == 'page'){
                                                        $breadcrumb = get_normal_breadScrumb($post->post_name);
                                                        $data['type'] = 'page';
                                                        $data['slug'] = $breadcrumb;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }else{
                                                        $obj['action'] = ''; 
                                                    }
                                                    
                                                }   else {
                                                    // Si el post no existe, dejar vacío
                                                    $obj['action'] = '';
                                                }
                                            } elseif (is_array($obj['action'][0])) {
                                                // Si ya es un array (por si acaso)
                                                $post_id = $obj['action'][0][0]; // Obtener el ID del post
                                                $post = get_post($post_id); // Obtener el post
                                                
                                                if ($post) {
                                                    $data = [];
                                                    $data2 = [];
                                                    $post_type = $post->post_type;
                                                    if ($post_type == 'post') {
                                                        // Obtener la primera categoría del post
                                                        $categories = get_the_category($post_id);
                                                        $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                                        $data['type'] = 'post';
                                                        $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }elseif ($post_type == 'page'){
                                                        $breadcrumb = get_normal_breadScrumb($post->post_name);
                                                        $data['type'] = 'page';
                                                        $data['slug'] = $breadcrumb;
                                                        $data2[] = $data;
                                                        $obj['action'] = $data2;
                                                    }else{
                                                        $obj['action'] = ''; 
                                                    }
                                                    
                                                } else {
                                                    // Si el post no existe, dejar vacío
                                                    $obj['action'] = '';
                                                }
                                            }
                                        }
                                        $items_content2[] = $obj;
                                    }
                                }
                                $button['card_type'] = $items_content2;
                                $items_content[] = $button;
                            }
                            $section['acf']['item_content'][$item_index]['list_card'] = $items_content; // Corregí esto, estaba list_button
                        }

                        if (isset($item['chips_list']) && is_array($item['chips_list'])) {
                            $items_content = [];
                            $items_content2 = [];
                            foreach ($item['chips_list'] as $obj) {
                                
                                if (isset($obj['action'][0]) && !is_array($obj['action'][0])) {
                                    // Aquí asumimos que action[0] contiene el ID del post
                                    $post_id = $obj['action'][0]; // Obtener el ID del post
                                    $post = get_post($post_id); // Obtener el post
                                    
                                   if ($post) {
                                        $data = [];
                                        $data2 = [];
                                        $post_type = $post->post_type;
                                        if ($post_type == 'post') {
                                            // Obtener la primera categoría del post
                                            $categories = get_the_category($post_id);
                                            $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                            $data['type'] = 'post';
                                            $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                            $data2[] = $data;
                                            $obj['action'] = $data2;
                                        }elseif ($post_type == 'page'){
                                            $breadcrumb = get_normal_breadScrumb($post->post_name);
                                            $data['type'] = 'page';
                                            $data['slug'] = $breadcrumb;
                                            $data2[] = $data;
                                            $obj['action'] = $data2;
                                        }else{
                                            $obj['action'] = ''; 
                                        }
                                        
                                    } else {
                                        // Si el post no existe, dejar vacío
                                        $obj['action'] = '';
                                    }
                                } elseif (is_array($obj['action'][0])) {
                                    // Si ya es un array (por si acaso)
                                    $post_id = $obj['action'][0][0]; // Obtener el ID del post
                                    $post = get_post($post_id); // Obtener el post
                                    
                                    if ($post) {
                                        $data = [];
                                        $data2 = [];
                                        $post_type = $post->post_type;
                                        if ($post_type == 'post') {
                                            // Obtener la primera categoría del post
                                            $categories = get_the_category($post_id);
                                            $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                            $data['type'] = 'post';
                                            $data['slug'] = 'conoce/' . $category_slug . '/' . $post->post_name;
                                            $data2[] = $data;
                                            $obj['action'] = $data2;
                                        }elseif ($post_type == 'page'){
                                            $breadcrumb = get_normal_breadScrumb($post->post_name);
                                            $data['type'] = 'page';
                                            $data['slug'] = $breadcrumb;
                                            $data2[] = $data;
                                            $obj['action'] = $data2;
                                        }else{
                                            $obj['action'] = ''; 
                                        }
                                        
                                    } else {
                                        // Si el post no existe, dejar vacío
                                        $obj['action'] = '';
                                    }
                                }
                                $items_content[] = $obj;
                            }
                            $section['acf']['item_content'][$item_index]['chips_list'] = $items_content; // Corregí esto, estaba list_button
                        }

                        if (isset($item['posts']) && is_array($item['posts'])) {
                            $items_content = [];
                            
                            $post_id = $item['posts'][0]; // Obtener el ID del post
                                $posts = get_postss_by_list_id($post_id); // Obtener el post
                    
                                    if ($posts) {
                                        foreach ($posts as $post ) {
                                        // Reemplazar el array 'url' con el slug
                                        $button['title'] = $post['title'];
                                        $button['acf'] = $post['acf'];
                                        $button['yoast_head_json'] = $post['yoast_head_json'];                                     
                                        }
                                        $items_content[] = $button;
                                    } else {
                                        // Si el post no existe, dejar el campo 'url' vacío
                                        $button = '';
                                        $items_content[] = $button;
                                    }
                               
                            
                            $section['acf']['item_content'][$item_index]['posts'] = $items_content;
                        }
                        
                         // Verificar si existe el parámetro list_button 
                         if (isset($item['ListDropdownRequirements']) && is_array($item['ListDropdownRequirements'])) {
                            $items_content = [];
                            $numb = 1;
                            foreach ($item['ListDropdownRequirements'] as $dropdown) {
                                $dropdown['number'] = $numb;
                                $numb = $numb +1;
                                $items_content[] = $dropdown;
                            }
                            $section['acf']['item_content'][$item_index]['ListDropdownRequirements'] = $items_content;
                        }
                        
                         // ID's de componente de Mapa
                         if (isset($item['ListOffice']) && is_array($item['ListOffice'])) {
                            $items_content = [];
                            $id = 1;
                            foreach ($item['ListOffice'] as $office) {
                                $office['id'] = $id;
                                $id = $id +1;
                                $officeOrder = [
                                    'id' => $office['id'],
                                    'name' => $office['name'],
                                    'address' => $office['address'],
                                    'latitude' => $office['latitude'],
                                    'longitude' => $office['longitude'],
                                    'state' => $office['state'],
                                    'municipality' => $office['municipality'],
                                    'phone' => $office['phone'],
                                    'atm_number' => $office['atm_number']
                                ];
                                $items_content[] = $officeOrder;
                            }
                            $section['acf']['item_content'][$item_index]['ListOffice'] = $items_content;
                        }
                        
                         // Verificar si existe el parámetro list_button 
                         if (isset($item['calendar']) && is_array($item['calendar'])) {
                            $items_content = [];
                            $numb = 1;
                            foreach ($item['calendar'] as $calendar) {
                                $date = $calendar['date'];
                                $formattedDate = substr($date, 0, 4) . "," . substr($date, 4, 2) . "," . substr($date, 6, 2);
                                $calendar['date'] = $formattedDate;
                                $items_content[] = $calendar;
                            }
                            $section['acf']['item_content'][$item_index]['calendar'] = $items_content;
                        }

                        // Verificar si existe el parámetro card_item y es una lista de IDs
                        if (isset($item['quest_item']) && is_array($item['quest_item'])) {
                            $card_items_content = [];

                            foreach ($item['quest_item'] as $card_item_id) {
                                // Obtener el contenido de la entrada referente al ID
                                $card_item_content = get_post($card_item_id);

                                if ($card_item_content) {
                                    // Obtener los campos ACF de la entrada
                                    $acf_fields = get_fields($card_item_id);

                                    // Verificar si existe item_content dentro de ACF
                                    if ($acf_fields && isset($acf_fields['item_content']) && is_array($acf_fields['item_content'])) {
                                        // Obtener el primer elemento de item_content (asumiendo que solo hay uno)
                                        $item_content = $acf_fields['item_content'][0];

                                        // Crear un array con los campos no vacíos
                                        $card_item_data = [
                                            'slug' => $card_item_content->post_name, // Incluir el slug
                                        ];

                                        // Agregar solo los campos no vacíos
                                        if (!empty($item_content['title'])) {
                                            $card_item_data['title'] = $item_content['title'];
                                        }
                                        if (!empty($item_content['description'])) {
                                            $card_item_data['description'] = $item_content['description'];
                                        }
                                        if (!empty($item_content['url'])) {
                                            $card_item_data['url'] = $item_content['url'];
                                        }
                                        if (!empty($item_content['icon'])) {
                                            $card_item_data['icon'] = $item_content['icon'];
                                        }
                                        if (!empty($item_content['image'])) {
                                            $card_item_data['image'] = $item_content['image'];
                                        }

                                        // Agregar el contenido al array
                                        $card_items_content[] = $card_item_data;
                                    }
                                }
                            }

                            // Reemplazar card_item con el contenido obtenido
                            $section['acf']['item_content'][$item_index]['quest_item'] = $card_items_content;
                        }

                       // Verificar si existe el parámetro list_question
                        if (isset($item['list_questions']) && is_array($item['list_questions'])) {
                            $items_content = [];
                            foreach ($item['list_questions'] as $question) {
                                if (isset($question['relation']) && !empty($question['relation'])) {
                                    // Obtener el ID del post (puede ser directamente el ID o estar en un array)
                                    $post_id = is_array($question['relation']) ? $question['relation'][0] : $question['relation'];
                                    
                                    // Obtener el post
                                    $post = get_post($post_id);

                                   if ($post) {
                                        $post_type = $post->post_type;
                                        if ($post_type == 'post') {
                                            // Obtener la primera categoría del post
                                            $categories = get_the_category($post_id);
                                            $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
                                            $question['relation']  = 'conoce/' . $category_slug . '/' . $post->post_name;
                                        }elseif ($post_type == 'page'){
                                            $breadcrumb = get_normal_breadScrumb($post->post_name);
                                            $question['relation']  =  $breadcrumb;
                                        }else{
                                            $obj['relation'] = ''; 
                                        }
                                        
                                    } else {
                                        // Si el post no existe, dejar vacío
                                        $question['relation'] = '';
                                    }
                                }
                                $items_content[] = $question;
                            }
                            $section['acf']['item_content'][$item_index]['list_questions'] = $items_content;
                        }

                        if ($item['acf_fc_layout'] === 'form_relations' && isset($item['dynamic_form']) && is_array($item['dynamic_form'])) {
                            $has_form_relations = true;

                            foreach ($item['dynamic_form'] as $form_index => $form_id) {
                                $form_wrappers = get_forminator_form_wrappers_by_id($form_id);
                                
                                if ($form_wrappers) {
                                    $dynamic_forms = array_merge($dynamic_forms, $form_wrappers);
                                }
                            }
                        }
                    }

                    if ($has_form_relations) {
                        $section['acf']['item_content'][$item_index]['dynamic_form'] = $dynamic_forms;
                    }
                }
                array_push($sections, $section['acf']);
                break; 
            }
        }
    }

    return new WP_REST_Response( [
        'status' => 200,
        'data' => $sections
    ], 200 );
}

function get_pages_by_slug($slug) {
 
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/pages?slug=' . $slug;

    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
  
    $data = json_decode( $body, true );
  
    return $data;
}

function get_relation_menu($slug) {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v1/menu/sidebar-personas';

    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Buscar en todos los menús principales
    $breadcrumb = null;
    foreach ($data['data'] as $menu) {
        if (!empty($menu['items']) && find_breadcrumb_recursive($menu['items'], $slug, $breadcrumb)) {
            break; // Si encontramos, salimos del bucle
        }
    }
    return $breadcrumb;
}

// Función recursiva mejorada para buscar el breadcrumb
function find_breadcrumb_recursive($items, $slug, &$result) {
    foreach ($items as $item) {
        // Verificar si es el item buscado
        if (isset($item['slug']) && $item['slug'] === $slug) {
            if (isset($item['breadScrumb'])) {
                $result = $item['breadScrumb'];
                return true; // Encontrado, detener búsqueda
            }
            return false;
        }
        
        // Buscar en subitems si existen
        if (!empty($item['items']) && find_breadcrumb_recursive($item['items'], $slug, $result)) {
            return true; // Propagamos el true si se encontró en subitems
        }
    }
    return false;
}



// Función para manejar errores de respuesta
function response_error($message) {
    return [
        'status' => 400,
        'message' => $message
    ];
}

// Función para obtener los wrappers de los formularios de Forminator por ID
function get_forminator_form_wrappers_by_id($id) {
    return Forminator_API::get_form_wrappers($id) ?: false;
}

// Función para obtener secciones por lista de IDs
function get_sections_by_list_id( $listId ) {
    $cadena_de_numeros = implode(',', $listId);

    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/secciones?include=' . $cadena_de_numeros;

    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
  
    $data = json_decode( $body, true );
  
    return $data;
}

function get_postss_by_list_id( $listId ) {
    $cadena_de_numeros = $listId;

    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/wp/v2/posts?include=' . $cadena_de_numeros;

    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
  
    $data = json_decode( $body, true );
  
    return $data;
}

 ?>