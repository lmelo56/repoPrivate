<?php
/*
Plugin Name: Api Conoce
Description: Servicios 
Version: 1.0
Author: Leonardo Melo
*/

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-with-url-normal', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 6;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query con meta_query para el campo ACF "url"
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'url',
                        'value' => '',
                        'compare' => '!='
                    ],
                    [
                        'key' => 'type_video',
                        'value' => 'normal',
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);

                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf,
                    'url'     => $acf_fields['url'] ?? '' // Asegurarse de incluir el campo url
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        },
        'permission_callback' => '__return_true'
    ]);
});
add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-with-url-short', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 6;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query con meta_query para el campo ACF "url"
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'url',
                        'value' => '',
                        'compare' => '!='
                    ],
                    [
                        'key' => 'type_video',
                        'value' => 'short',
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);

                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf,
                    'url'     => $acf_fields['url'] ?? '' // Asegurarse de incluir el campo url
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/child-categories-by-parent-slug', [
        'methods' => 'GET',
        'callback' => function($request) {
            // 1. Definir el slug de la categoría padre
            $parent_slug = 'aprende-con-mercantil';
            
            // 2. Obtener la categoría padre por slug
            $parent_category = get_term_by('slug', $parent_slug, 'category');
            
            if (!$parent_category || is_wp_error($parent_category)) {
                return new WP_Error('parent_not_found', 'Categoría padre no encontrada', ['status' => 404]);
            }

            // 3. Obtener todas las categorías hijas
            $child_categories = get_terms([
                'taxonomy' => 'category',
                'parent' => $parent_category->term_id,
                'hide_empty' => false, // Cambia a true si no quieres categorías vacías
            ]);

            if (is_wp_error($child_categories)) {
                return new WP_Error('error', 'Error al obtener categorías hijas', ['status' => 500]);
            }

            // 4. Función para obtener datos completos de una categoría (similar al endpoint anterior)
            $get_category_data = function($term_id) {
                $category = get_term($term_id, 'category');
                if (!$category || is_wp_error($category)) {
                    return null;
                }

                $data = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                    'taxonomy' => $category->taxonomy,
                    'url' => get_term_link($category)
                ];

                // Obtener campos ACF
                if (function_exists('get_fields')) {
                    $acf_data = get_fields('category_' . $category->term_id) ?: [];
                    
                    // Procesar imágenes en ACF
                    foreach (['image', 'thumbnail', 'banner', 'imagen_categoria'] as $image_field) {
                        if (isset($acf_data[$image_field])) {
                            if (is_array($acf_data[$image_field]) && isset($acf_data[$image_field]['url'])) {
                                $acf_data[$image_field] = $acf_data[$image_field]['url'];
                            } elseif (is_object($acf_data[$image_field]) && isset($acf_data[$image_field]->url)) {
                                $acf_data[$image_field] = $acf_data[$image_field]->url;
                            }
                        }
                    }
                    
                    $data['acf'] = $acf_data;
                }

                return $data;
            };

            // 5. Procesar todas las categorías hijas
            $response = [
                'parent_category' => $get_category_data($parent_category->term_id),
                'child_categories' => array_map(function($category) use ($get_category_data) {
                    return $get_category_data($category->term_id);
                }, $child_categories)
            ];

            return $response;
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/child-categories-by-parent-slug-param', [
        'methods' => 'GET',
        'callback' => function($request) {
            // 1. Definir el slug de la categoría padre
            $parent_slug = $request->get_param('slug');
            
            // 2. Obtener la categoría padre por slug
            $parent_category = get_term_by('slug', $parent_slug, 'category');
            
            if (!$parent_category || is_wp_error($parent_category)) {
                return new WP_Error('parent_not_found', 'Categoría padre no encontrada', ['status' => 404]);
            }

            // 3. Obtener todas las categorías hijas
            $child_categories = get_terms([
                'taxonomy' => 'category',
                'parent' => $parent_category->term_id,
                'hide_empty' => false, // Cambia a true si no quieres categorías vacías
            ]);

            if (is_wp_error($child_categories)) {
                return new WP_Error('error', 'Error al obtener categorías hijas', ['status' => 500]);
            }

            // 4. Función para obtener datos completos de una categoría (similar al endpoint anterior)
            $get_category_data = function($term_id) {
                $category = get_term($term_id, 'category');
                if (!$category || is_wp_error($category)) {
                    return null;
                }

                $data = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                    'taxonomy' => $category->taxonomy,
                    'url' => get_term_link($category)
                ];

                // Obtener campos ACF
                if (function_exists('get_fields')) {
                    $acf_data = get_fields('category_' . $category->term_id) ?: [];
                    
                    // Procesar imágenes en ACF
                    foreach (['image', 'thumbnail', 'banner', 'imagen_categoria'] as $image_field) {
                        if (isset($acf_data[$image_field])) {
                            if (is_array($acf_data[$image_field]) && isset($acf_data[$image_field]['url'])) {
                                $acf_data[$image_field] = $acf_data[$image_field]['url'];
                            } elseif (is_object($acf_data[$image_field]) && isset($acf_data[$image_field]->url)) {
                                $acf_data[$image_field] = $acf_data[$image_field]->url;
                            }
                        }
                    }
                    
                    $data['acf'] = $acf_data;
                }

                return $data;
            };

            // 5. Procesar todas las categorías hijas
            $response = [
                'parent_category' => $get_category_data($parent_category->term_id),
                'child_categories' => array_map(function($category) use ($get_category_data) {
                    return $get_category_data($category->term_id);
                }, $child_categories)
            ];

            return $response;
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/category-by-slug', [
        'methods' => 'GET',
        'callback' => function($request) {
            // 1. Validar y sanitizar el slug
            $category_slug = sanitize_text_field($request->get_param('slug'));
            if (empty($category_slug)) {
                return new WP_Error('no_slug', 'Debe proporcionar un slug de categoría', ['status' => 400]);
            }

            // 2. Obtener la categoría por slug
            $category = get_term_by('slug', $category_slug, 'category');
            
            if (!$category || is_wp_error($category)) {
                return new WP_Error('not_found', 'Categoría no encontrada', ['status' => 404]);
            }

            // 3. Función para obtener datos completos de una categoría
            $get_category_data = function($term_id) {
                $category = get_term($term_id, 'category');
                if (!$category || is_wp_error($category)) {
                    return null;
                }

                $data = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'description' => $category->description,
                    'count' => $category->count,
                    'taxonomy' => $category->taxonomy,
                    'url' => get_term_link($category)
                ];

                // Obtener campos ACF
                if (function_exists('get_fields')) {
                    $acf_data = get_fields('category_' . $category->term_id) ?: [];
                    
                    // Procesar imágenes en ACF
                    foreach (['image', 'thumbnail', 'banner', 'imagen_categoria'] as $image_field) {
                        if (isset($acf_data[$image_field])) {
                            if (is_array($acf_data[$image_field]) && isset($acf_data[$image_field]['url'])) {
                                $acf_data[$image_field] = $acf_data[$image_field]['url'];
                            } elseif (is_object($acf_data[$image_field]) && isset($acf_data[$image_field]->url)) {
                                $acf_data[$image_field] = $acf_data[$image_field]->url;
                            }
                        }
                    }
                    
                    $data['acf'] = $acf_data;
                }

                return $data;
            };

            // 4. Obtener datos de la categoría actual
            $category_data = $get_category_data($category->term_id);

            // 5. Obtener datos de la categoría padre si existe
            if ($category->parent > 0) {
                $category_data['parent'] = $get_category_data($category->parent);
            } else {
                $category_data['parent'] = null;
            }

            // 6. Función para procesar campos ACF de posts relacionados
            $process_relation_acf = function($post) {
                if (!is_object($post) || !isset($post->ID)) {
                    return $post;
                }
                
                $post_data = (array)$post;
                $post_data['acf'] = function_exists('get_fields') ? get_fields($post->ID) : [];
                
                // Procesar imágenes en ACF
                if (is_array($post_data['acf'])) {
                    foreach ($post_data['acf'] as $key => $value) {
                        if (strpos($key, 'image') !== false || strpos($key, 'Image') !== false) {
                            if (is_array($value) && isset($value['url'])) {
                                $post_data['acf'][$key] = $value['url'];
                            } elseif (is_object($value) && isset($value->url)) {
                                $post_data['acf'][$key] = $value->url;
                            }
                        }
                    }
                }
                
                return $post_data;
            };

            // 7. Procesar relación si existe en ACF
            if (isset($category_data['acf']['relation']) && !empty($category_data['acf']['relation'])) {
                $relation_data = is_array($category_data['acf']['relation']) ? $category_data['acf']['relation'] : [$category_data['acf']['relation']];
                $category_data['acf']['relation'] = array_map($process_relation_acf, $relation_data);
            }

            return $category_data;
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/post-by-slug', [
        'methods' => 'GET',
        'callback' => function($request) {
            // 1. Validar slug
            $post_slug = $request->get_param('slug');
            if (empty($post_slug)) {
                return new WP_Error('no_slug', 'Slug requerido', ['status' => 400]);
            }

            // 2. Buscar post principal
            $post = get_page_by_path($post_slug, OBJECT, 'post');
            if (!$post) {
                return new WP_Error('not_found', 'Post no encontrado', ['status' => 404]);
            }

            // 3. Verificar si ACF está activo
            if (!function_exists('get_fields')) {
                return new WP_Error('acf_missing', 'ACF no está instalado', ['status' => 500]);
            }

            // 4. Obtener campos ACF del post principal
            $acf_fields = get_fields($post->ID);
            $categories = get_the_category($post->ID);
            $category_name = !empty($categories) ? $categories[0]->name : 'sin-categoria';
            $category_slug = !empty($categories) ? $categories[0]->slug : 'sin-categoria';
            
            // 5. Función para procesar imágenes
            $process_image = function($image) {
                if (is_array($image) && isset($image['url'])) {
                    return $image['url'];
                }
                if (is_object($image) && isset($image->url)) {
                    return $image->url;
                }
                return is_string($image) ? $image : '';
            };

            // 6. Función para obtener datos de post con ACF
            $get_post_data = function($post_id) use ($process_image) {
                $post = get_post($post_id);
                if (!$post) return null;
                
                $acf_data = get_fields($post_id);
                
                // Procesar imágenes en ACF
                if (is_array($acf_data)) {
                    foreach ($acf_data as $key => $value) {
                        if (strpos($key, 'image') !== false || strpos($key, 'Image') !== false) {
                            $acf_data[$key] = $process_image($value);
                        }
                    }
                }
                
                return [
                    'id' => $post->ID,
                    'title' => get_the_title($post),
                    'content' => apply_filters('the_content', $post->post_content),
                    'excerpt' => wp_strip_all_tags(get_the_excerpt($post)),
                    'slug' => $post->post_name,
                    'acf' => $acf_data ?: new stdClass()
                ];
            };

            // 7. Procesar RelationTip si existe
            if (isset($acf_fields['RelationTip']) && !empty($acf_fields['RelationTip'])) {
                $relation_tips = [];
                $related_posts = is_array($acf_fields['RelationTip']) ? $acf_fields['RelationTip'] : [$acf_fields['RelationTip']];
                
                foreach ($related_posts as $related_post) {
                    if (is_object($related_post) && isset($related_post->ID)) {
                        $relation_tips[] = $get_post_data($related_post->ID);
                    } elseif (is_numeric($related_post)) {
                        $relation_tips[] = $get_post_data($related_post);
                    }
                }
                
                $acf_fields['RelationTip'] = $relation_tips;
            }

            // 8. Preparar respuesta principal
            $response = $get_post_data($post->ID);
            
            // 9. Asegurar que los campos ACF principales están incluidos
            $response['acf'] = $acf_fields ?: new stdClass();

            $response['categoriesName'] = $category_name;
            $response['categoriesSlug'] = $category_slug;
            
            // 10. Procesar imágenes específicas en ACF principal
            if (is_array($acf_fields)) {
                foreach (['image', 'BackgroundImage'] as $image_field) {
                    if (isset($acf_fields[$image_field])) {
                        $response['acf'][$image_field] = $process_image($acf_fields[$image_field]);
                    }
                }
            }

            return $response;
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/category-info', [
        'methods'  => 'GET',
        'callback' => function($request) {
            // 1. Obtener y validar el slug
            $base_url = get_site_url(); // o home_url();
            $slug = sanitize_text_field($request->get_param('slug'));
            if (empty($slug)) {
                return new WP_Error('invalid_slug', 'Debe proporcionar un slug de categoría válido', ['status' => 400]);
            }
            $yoast_data =  get_category_id($category_param,$base_url);

            return $yoast_data;
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-suggest', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 2;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query con meta_query para el campo ACF "suggest"
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'suggest',
                        'value' => true,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);
                
                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-article', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 6;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query con meta_query para el campo ACF "suggest"
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'featured',
                        'value' => true,
                        'compare' => '='
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);

                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-con-tag', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 1;
            
            // Procesar parámetros
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            $tag = $request->get_param('tag');
            
            // Validaciones
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            if (empty($tag)) {
                return new WP_Error('no_tag', 'No se proporcionó un tag válido', ['status' => 400]);
            }
            
            // Configurar query
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'field'    => is_numeric($tag) ? 'term_id' : 'slug',
                        'terms'    => $tag,
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);
                
                // Obtener categorías del post con sus padres
                $categories = get_the_category($post_id);
                $category_hierarchy = [];
                
                foreach ($categories as $category) {
                    $current_category = $category;
                    $hierarchy = [];
                    
                    // Recorrer hacia arriba en la jerarquía
                    while ($current_category) {
                        $hierarchy[] = [
                            'id' => $current_category->term_id,
                            'name' => $current_category->name,
                            'slug' => $current_category->slug,
                            'parent' => $current_category->parent
                        ];
                        
                        // Obtener la categoría padre
                        $current_category = $current_category->parent ? get_term($current_category->parent, 'category') : null;
                    }
                    
                    // Invertir para tener el orden desde el padre más alto hasta la categoría actual
                    $category_hierarchy[] = array_reverse($hierarchy);
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'acf'     => $filtered_acf,
                    'tags'    => wp_get_post_tags($post_id, ['fields' => 'names']),
                    'categories' => $category_hierarchy
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-sin-tags', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 6;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);
                
                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        }
    ]);
});

add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/posts-sin-tags-video', [
        'methods'  => 'GET',
        'callback' => function($request) {
            $page = max(1, (int) $request->get_param('page') ?: 1);
            $per_page = 6;
            
            // Procesar parámetros de categorías
            $cat_slugs_str = $request->get_param('slug') ?: '';
            $cat_slugs = array_filter(explode(',', $cat_slugs_str));
            
            if (empty($cat_slugs)) {
                return new WP_Error('no_categories', 'No se proporcionaron categorías válidas', ['status' => 400]);
            }
            
            // Configurar query
            $query_args = [
                'tax_query' => [
                    'relation' => 'AND',
                    [
                        'taxonomy' => 'post_tag',
                        'operator' => 'NOT EXISTS'
                    ],
                    [
                        'taxonomy' => 'category',
                        'field'    => 'slug',
                        'terms'    => $cat_slugs,
                    ]
                ],
                'meta_query' => [
                    [
                        'key' => 'url',
                        'value' => '',
                        'compare' => '!='
                    ]
                ],
                'posts_per_page' => $per_page,
                'paged'          => $page,
            ];

            $query = new WP_Query($query_args);
            $posts = [];

            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $acf_fields = get_fields($post_id);
                
                // Obtener categorías del post
                $categories = get_the_category($post_id);
                $category_names = [];
                $category_slugs = [];
                
                foreach ($categories as $category) {
                    $category_names[] = $category->name;
                    $category_slugs[] = $category->slug;
                }
                
                // Función helper para extraer URL de imagen
                $get_image_url = function($image_field) {
                    if (empty($image_field)) return '';
                    
                    if (is_array($image_field) && isset($image_field['url'])) {
                        return $image_field['url'];
                    } elseif (is_string($image_field)) {
                        return $image_field;
                    } elseif (is_object($image_field) && isset($image_field->url)) {
                        return $image_field->url;
                    }
                    
                    return '';
                };
                
                // Procesar campos de imagen
                $background_image = $get_image_url($acf_fields['BackgroundImage'] ?? '');
                $image = $get_image_url($acf_fields['image'] ?? '');
                
                // Filtrar campos ACF
                $filtered_acf = [];
                if (is_array($acf_fields)) {
                    foreach ($acf_fields as $key => $value) {
                        if ($key === 'BackgroundImage') {
                            $filtered_acf[$key] = $background_image;
                        } elseif ($key === 'image') {
                            $filtered_acf[$key] = $image;
                        } else {
                            $filtered_acf[$key] = $value;
                        }
                    }
                }

                $posts[] = [
                    'id'      => $post_id,
                    'title'   => get_the_title(),
                    'content' => get_the_content(),
                    'excerpt' => get_the_excerpt(),
                    'slug'    => get_post_field('post_name'),
                    'categories' => [
                        'names' => $category_names,
                        'slugs' => $category_slugs
                    ],
                    'acf'     => $filtered_acf
                ];
            }

            wp_reset_postdata();

            return [
                'page' => $page,
                'total_pages' => $query->max_num_pages,
                'posts' => $posts
            ];
        }
    ]);
});


add_action( 'rest_api_init', function () {
        register_rest_route( 'wp/v1', 'posts', [
        'methods'  => 'GET',
        'callback' => 'get_knowledge',
        'permission_callback' => '__return_true',
    ]);
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', 'more-posts', [
    'methods'  => 'GET',
    'callback' => 'get_more_post',
    'permission_callback' => '__return_true',
    ]);
});

add_action( 'rest_api_init', function () {
    register_rest_route( 'wp/v1', 'detail-post', [
    'methods'  => 'GET',
    'callback' => 'get_post_detail',
    'permission_callback' => '__return_true',
    ]);
});


// El callback donde se maneja la solicitud
function get_post_detail( $request ) {
    $slug_param = $request->get_param( 'slug' );

    if ( empty( $slug_param) ) {
        return new WP_REST_Response(response_error("No se encontró el parámetro id"), 400);
    }
    
    $detail = get_posts_detail($slug_param);
   
    $items_content = [];
    $posts = [];
    

    if(!empty($posts_list)){
        $dataList = [];
    }
    $posts['title'] = $detail['categoriesName'];

    $post_categories = get_cat_learn();

    $checkLearn = false;
    if(get_validate_learn($detail['categoriesSlug'],$post_categories))
        $checkLearn = true;

    $cat_suggest = [];
    $slug = 'todos';
    $cat_know = get_cat_all($slug);
    $i = 1;
    foreach($cat_know as $item){
        if($i < 4){
            if($item['name'] != 'Aprende con Mercantil'){
                $cat_suggest[] = $item;
                $i = $i + 1;
            }
        }
    }
    $model_cat = [];
    $model_cat['acf_fc_layout'] = 'ListCategories';
    $model_cat['title'] = 'Sigue leyendo sobre';
    $model_cat['list_categories'] = $cat_suggest;

    $base_url = get_site_url(); 
    $posts_list = get_posts_by_lists($detail['categoriesName'],$base_url);
    if(!$checkLearn){

        if(!empty($detail['title'])){
                $item = [];
                $item['acf_fc_layout'] = 'TipHeader';
                $item['title'] = $detail['categoriesName'];
                $item['breadscrumb'] = 'Conoce / '. $detail['categoriesName'];
                $item['text'] = $detail['title'];
                if(!empty($detail['acf']['BackgroundImage'])){
                    $item['background'] = $detail['acf']['BackgroundImage'];
                }
                $items_data = [];
                $item_data['padding'] = false;
                $item_data['background_image'] = '';
                $item_data['background_gradient'] = '';
                $obj = [];
                $obj[] = $item;
                $item_data['item_content'] = $obj;
                $items_content[] = $item_data;
        }

        $items_data = [];
        $item_data['padding'] = true;
        $item_data['background_image'] = '';
        $item_data['background_gradient'] = 'linear-gradient(180deg, rgba(255,255,255,1) 15%, rgba(230,239,247,1) 34%);';
        $obj = [];
            

        if(!empty($detail['excerpt'])){
            $item = [];
            $item['acf_fc_layout'] = 'DetailSubtitle';
            $item['description'] = $detail['excerpt'];
            $obj[] = $item;
        }

        if(!empty($detail['acf']['image'])){
            $item = [];
            $item['acf_fc_layout'] = 'DetailMainImage';
            $item['image'] = $detail['acf']['image'];
            $obj[] = $item;
        }
        if(!empty($detail['content'])){
            $item = [];
            $item['acf_fc_layout'] = 'Wysiwyg';
            $item['editor'] = $detail['content'];
            $obj[] = $item;
            $obj[] = $model_cat;
        }

        $item_data['item_content'] = $obj;
        $items_content[] = $item_data;

        $items_data = [];
        $item_data['padding'] = true;
        $item_data['background_image'] = '';
        $item_data['background_gradient'] = '';
        $obj = [];
        if(!empty($detail['acf']['RelationTip'])){
            $item = [];
            $item['acf_fc_layout'] = 'CardTip';
            $item['title'] = $detail['acf']['RelationTip'][0]['title'];
            $item['head'] = 'Conoce / '.$detail['categoriesName'];
            if(!empty($detail['acf']['RelationTip'][0]['acf'])){
                $item['background'] = $detail['acf']['RelationTip'][0]['acf']['BackgroundImage'];
            }
            $item['description'] = $detail['acf']['RelationTip'][0]['content'];
            $item['wrapper'] = true;
            $obj[] = $item;
            $item_data['item_content'] = $obj;
            $items_content[] = $item_data;
        }

         if(!empty($posts_list)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'ListPost';
            $dataList['title'] = '';
            $newObj = [];
            $idx = 1;
            foreach( $posts_list['item-content'] as $item_card){
                if($idx < 3){
                    $newObj[] = $item_card;
                    $idx = $idx + 1;
                }
            }
            $dataList['list_card'] = $newObj;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }

        $section_post_head = get_sections_head();
        if(!empty( $section_post_head['footerA'] )){
            $footer = [];
            $data = [];
            foreach ($section_post_head['footerB'] as $item) {
                $data[] = $item;
            }
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $item_data['section_field'] = $section_post_head['footerA'];
            $item_data['item_content'] = $data;
            $items_content[] = $item_data;
        }

    }else{

        if(!empty($detail['acf']['url'])){
                $item = [];
                if($detail['acf']['type_video'] == 'normal'){
                    $item['acf_fc_layout'] = 'HeaderVideoNormal';
                }else{
                    $item['acf_fc_layout'] = 'HeaderVideoShort';
                }              
                if(!empty($detail['acf']['image'])){
                    $item['image'] = $detail['acf']['image'];
                }
                $item['title'] = $detail['categoriesName'];
                $item['breadscrumb'] = 'Conoce / '. $detail['categoriesName'];
                $item['url'] = $detail['acf']['url'];
                $items_data = [];
                $item_data['padding'] = false;
                $item_data['background_image'] = '';
                $item_data['background_gradient'] = '';
                $obj = [];
                $obj[] = $item;
                $item_data['item_content'] = $obj;
                $items_content[] = $item_data;
        }

        if(!empty($detail['content'])){
            $item = [];
            $item['acf_fc_layout'] = 'Wysiwyg';
            $item['editor'] = $detail['content'];
            $items_data = [];
            $item_data['padding'] = true;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $obj = [];
            $obj[] = $item;
            $obj[] = $model_cat;
            $item_data['item_content'] = $obj;
            $items_content[] = $item_data;
        }
        $section_post_head = get_sections_head();
        if(!empty( $section_post_head['footerA'] )){
            $footer = [];
            $data = [];
            foreach ($section_post_head['footerB'] as $item) {
                $data[] = $item;
            }
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $item_data['section_field'] = $section_post_head['footerA'];
            $item_data['item_content'] = $data;
            $items_content[] = $item_data;
        }
    }

    
    



    $posts['item_content'] = $items_content;

    return new WP_REST_Response( [
        'status' => 200,
        'data' =>  $posts
    ], 200 );
}

// El callback donde se maneja la solicitud
function get_more_post( $request ) {
    $category_param = $request->get_param( 'slug' );
    $page_param = $request->get_param( 'page' );

    if ( empty( $category_param) ) {
        return new WP_REST_Response(response_error("No se encontró el parámetro id"), 400);
    }
    
    

    $posts_list = get_posts_more_lists($category_param,$page_param);
   
    $items_content = [];
    $posts = [];


    if(!empty($posts_list)){
        $dataList = [];
        $dataList['acf_fc_layout'] = 'ListPost';
        $dataList['title'] = 'Todos los artículos';
        $dataList['page'] = $posts_list['page'];
        $dataList['total_pages'] = $posts_list['total_pages'];
        if($posts_list['page'] < $posts_list['total_pages']){
            $dataList['show_buttom'] = true;
        }else{
            $dataList['show_buttom'] = false;
        }
        $dataList['list_card'] = $posts_list['item-content'];

        $items_content[] = $dataList;
    }
 
    
    $posts['item_content'] = $items_content;




    return new WP_REST_Response( [
        'status' => 200,
        'data' =>  $posts
    ], 200 );
}

// El callback donde se maneja la solicitud
function get_knowledge( $request ) {
    $category_param = $request->get_param( 'slug' );
    $base_url = get_site_url(); // o home_url();
    if ( empty( $category_param ) ) {
        return new WP_REST_Response(response_error("No se encontró el parámetro id"), 400);
    }

    //$category_idx = get_category_id($category_param,$base_url);
    $category_idx = [];
    $category_idx['title'] ='Conoce';
    $category_idx['yoast_head'] = '';
    $category_idx['yoast_head_json'] =[];

    $section_post_head = get_sections_head($category_param);

    $post_categories = get_cat_learn();

    $checkLearn = false;
    if(get_validate_learn($category_param,$post_categories))
        $checkLearn = true;

    $posts_list = get_posts_by_lists($category_param,$base_url);
    if(!$checkLearn){
        $posts_list_feature = get_posts_feature($category_param,$base_url);

        $section_subCat_Learn = get_subCat_learn();
    }else{
        $post_video_normal = get_video_learn_normal($category_param);
        $post_video_short = get_video_learn_short($category_param);
    }

    $category_head = get_term_by('slug', $category_param, 'category');
    $name_head = '';
    if ($category_head) {
        $name_head = $category_head->name;
    } 

    $items_content = [];
    $posts = [];
    $posts['title'] = $category_idx['title'];
    $posts['yoast_head'] = $category_idx['yoast_head'];
    $posts['yoast_head_json'] = $category_idx['yoast_head_json'];
    
    if(!empty($section_post_head['headerA'])) {
        $items_data = [];
        $item_data['padding'] = false;
        $item_data['background_image'] = '';
        $item_data['background_gradient'] = '';
        $items_data2 = [];
        
        foreach ($section_post_head['headerA'] as $item) {
            // Modificar el título aquí
            if (isset($item['header_type'][0]['information']['title'])) {
                $item['header_type'][0]['information']['title'] = $name_head;
            }
            $items_data2[] = $item;
        }
        
        $item_data['item_content'] = $items_data2;
        $items_content[] = $item_data;
    }

    if($checkLearn){
        if(!empty($post_categories)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'ListCategoriesLearn';
            $dataList['title'] = 'Categorías';

            $dataList['list_categories'] = $post_categories;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    }

    if($checkLearn){
        if(!empty($post_video_normal)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'PrincipalVideo';
            $dataList['title'] = 'Videos recientes';

            $dataList['list_videos_principal'] = $post_video_normal;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    }

    if($checkLearn){
        if(!empty($post_video_short)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'ShortVideo';
            $dataList['title'] = 'Shorts';

            $dataList['list_videos_short'] = $post_video_short;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    }


    if(!$checkLearn){
        if(!empty($posts_list_feature)){
            $items_data = [];
            $item_data['padding'] = true;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'PrincipalArticle';
            $dataList['title'] = 'Artículos Destacados';
            $data = [];
            foreach ($posts_list_feature['item-content1'] as $item) {
                $data[] = $item;
                $i = $i+1;
            }
            
            $dataList['list_feature'] = $data;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    }
    
        if(!empty($posts_list)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'ListPost';
            $dataList['title'] = 'Todos los artículos';
            $dataList['page'] = $posts_list['page'];
            $dataList['total_pages'] = $posts_list['total_pages'];
            if($posts_list['page'] < $posts_list['total_pages']){
                $dataList['show_buttom'] = true;
            }else{
                $dataList['show_buttom'] = false;
            }
            $dataList['list_card'] = $posts_list['item-content'];
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    
    

    if(!$checkLearn){
        if(!empty($section_subCat_Learn)){
            $items_data = [];
            $item_data['padding'] = false;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = 'linear-gradient(180deg, rgba(255,255,255,1) 15%, rgba(230,239,247,1) 34%);';
            $items_data2 = [];
            foreach ($section_subCat_Learn['item-content'] as $item) {
                $items_data2[] = $item;
            }
            $item_data['item_content'] = $items_data2;
            $items_content[] = $item_data;
        }
    }
    
    if(!$checkLearn){
        if(!empty($posts_list_feature)){
            $items_data = [];
            $item_data['padding'] = true;
            $item_data['background_image'] = '';
            $item_data['background_gradient'] = '';
            $dataList = [];
            $dataList['acf_fc_layout'] = 'SuggestArticle';
            $dataList['title'] = 'Sugeridos Para Ti';
            $data = [];
            foreach ($posts_list_feature['item-content2'] as $item) {
                $data[] = $item;
                $i = $i+1;
            }
            $dataList['list_suggest'] = $data;
            $item = [];
            $item[] = $dataList;
            $item_data['item_content'] = $item;
            $items_content[] = $item_data;
        }
    }

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




    return new WP_REST_Response( [
        'status' => 200,
        'data' =>  $posts
    ], 200 );
}

function get_validate_learn($slug,$post_categories){
    $check = false;
    if($slug == 'aprende-con-mercantil'){
        return true;
    }
    foreach($post_categories as $item){
        if($item['slug'] == '/'.'conoce/'.$slug){
            $check = true;
        }
    }
    return $check;
}

function get_category_id($category,$base_url){
    $id="";
    $dataCat = '';
    $seo = "";
    $seoJson = "";
    $postName = "";
    $category_ids = array();

    if ($category == "conoce") {
        // 1. Obtener categoría padre
        $parent_response = wp_remote_get($base_url . '/wp-json/wp/v2/categories?slug=' . $category);
    
        $parent_cat = json_decode(wp_remote_retrieve_body($parent_response), true);
    
        // Verificar si $parent_cat tiene datos
        if (empty($parent_cat)) {
            error_log("No se encontró la categoría padre: " . $category);
            return;
        }
    
        $seo = $parent_cat[0]['yoast_head'] ?? ''; // Usamos el operador null coalescente por seguridad
        $seoJson = $parent_cat[0]['yoast_head_json'] ?? ''; // Usamos el operador null coalescente por seguridad
        $postName  = $parent_cat[0]['name'] ?? '';
        // 2. Obtener subcategorías
        $subcats_response = wp_remote_get($base_url . '/wp-json/wp/v2/categories?parent=' . $parent_cat[0]['id']);
    
        if (is_wp_error($subcats_response)) {
            error_log("Error al obtener subcategorías: " . $subcats_response->get_error_message());
            return;
        }
    
        $subcats = json_decode(wp_remote_retrieve_body($subcats_response), true);
    
        // 3. Extraer IDs de subcategorías
        $category_ids = [];
        if (!empty($subcats)) {
            foreach ($subcats as $subcat) {
                if (isset($subcat['id'])) {
                    $category_ids[] = $subcat['id'];
                }
            }
        }
    
        // 4. Generar string de IDs separados por comas
        $id = implode(',', $category_ids);
    }else{
        $url = $base_url . '/wp-json/wp/v2/categories?slug='.$category;
        $cat = wp_remote_get( $url );
        if ( is_wp_error( $cat ) ) {
            return false;
        }
        $bodyCat = wp_remote_retrieve_body( $cat ); 
        $dataCat = json_decode( $bodyCat, true );
        $id = $dataCat['0']['id'];
        $seo = $dataCat['0']['yoast_head'];
    }

    $data = [];
    $data['title'] = $postName;
    $data['yoast_head'] = $seo;
    $data['yoast_head_json'] = $seoJson;
    return $data;
}


function get_posts_feature( $id,$base_url ) {
   

    $url = $base_url . '/wp-json/custom/v1/posts-article?slug='. $id. '&page=1';
    $response = wp_remote_get( $url );

    $url2 = $base_url . '/wp-json/custom/v1/posts-suggest?slug='.$id. '&page=1';
    $response2 = wp_remote_get( $url2 );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    $bodyDecode = json_decode( $body, true );

    $body2 = wp_remote_retrieve_body( $response2 );
    $bodyDecode2 = json_decode( $body2, true );


    $items_content1 = [];
    $items_content2 = [];
    foreach ($bodyDecode['posts'] as $postData) {

            $varData = [];
            $varData['acf_fc_layout'] = 'CardArticle';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['background'] = $postData['acf']['image'];
            $items_content1[] = $varData;
    }
    foreach ($bodyDecode2['posts'] as $postData) {
            $varData = [];
            $varData['acf_fc_layout'] = 'CardGeneral';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['background'] = $postData['acf']['image'];
            $items_content2[] = $varData;
    }

    $data = [];
    $data['item-content1'] = $items_content1;
    $data['item-content2'] = $items_content2;
  
    return $data;
}



function get_posts_by_lists( $id,$base_url ) {

    $url = $base_url . '/wp-json/custom/v1/posts-sin-tags?slug='.$id.'&page=1';
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $url2 = $base_url . '/wp-json/custom/v1/posts-con-tag?slug='.$id.'&tag=29&page=1';
    $response2 = wp_remote_get( $url2 );
    $body2 = wp_remote_retrieve_body( $response2 );
    $bodyDecode2 = json_decode( $body2, true );

    $body = wp_remote_retrieve_body( $response );
    $bodyDecode = json_decode( $body, true );
    $items_content = [];
    $tip = false;
    $data = [];
    $data['page'] = $bodyDecode['page'];
    $data['total_pages'] = $bodyDecode['total_pages'];

    if(!empty($bodyDecode2)){
        if(!empty($bodyDecode2['posts'])){
            $tip = true;
        }
    }
    $i = 1;
    if($tip == true){
        foreach ($bodyDecode['posts'] as $postData) {
            if($i == 5){
                
                $varDataTip = [];
                $varDataTip['acf_fc_layout'] = 'CardTip';
                $varDataTip['slug'] = $bodyDecode2['posts']['0']['slug'];
                $head = $bodyDecode2['posts']['0']['categories'][0][0]['name'];
                    $y=0;
                    foreach ($bodyDecode2['posts']['0']['categories'][0] as $item) {
                        if($y > 0){
                            $head = $head.  ' / ' . $item['name'];
                        }
                        $y = $y + 1;
                    }
                $varDataTip['head'] = $head;
                $varDataTip['title'] = $bodyDecode2['posts']['0']['title'];
                $varDataTip['description'] = $bodyDecode2['posts']['0']['content'];
                $varDataTip['background'] = $bodyDecode2['posts']['0']['acf']['BackgroundImage'];
                $items_content[] = $varDataTip;
            }
            $varData = [];
            $varData['acf_fc_layout'] = 'CardGeneral';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['image'] = $postData['acf']['image'];
            $items_content[] = $varData;
            $i = $i + 1;
    }
    }else{
        foreach ($bodyDecode['posts'] as $postData) {
            $varData = [];
            $varData['acf_fc_layout'] = 'CardGeneral';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['image'] = $postData['acf']['image'];
            $items_content[] = $varData;
        }

    }

    $data['item-content'] = $items_content;
  
    return $data;
}

function get_posts_by_listsV( $id,$base_url ) {

    $url = $base_url . '/wp-json/custom/v1/posts-sin-tags-video?slug='.$id.'&page=1';
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $url2 = $base_url . '/wp-json/custom/v1/posts-con-tag?slug='.$id.'&tag=29&page=1';
    $response2 = wp_remote_get( $url2 );
    $body2 = wp_remote_retrieve_body( $response2 );
    $bodyDecode2 = json_decode( $body2, true );

    $body = wp_remote_retrieve_body( $response );
    $bodyDecode = json_decode( $body, true );
    $items_content = [];
    $tip = false;
    $data = [];
    $data['page'] = $bodyDecode['page'];
    $data['total_pages'] = $bodyDecode['total_pages'];

    if(!empty($bodyDecode2)){
        if(!empty($bodyDecode2['posts'])){
            $tip = true;
        }
    }
    $i = 1;
    if($tip == true){
        foreach ($bodyDecode['posts'] as $postData) {
            if($i == 5){
                
                $varDataTip = [];
                $varDataTip['acf_fc_layout'] = 'CardTip';
                $varDataTip['slug'] = $bodyDecode2['posts']['0']['slug'];
                $head = $bodyDecode2['posts']['0']['categories'][0][0]['name'];
                    $y=0;
                    foreach ($bodyDecode2['posts']['0']['categories'][0] as $item) {
                        if($y > 0){
                            $head = $head.  ' / ' . $item['name'];
                        }
                        $y = $y + 1;
                    }
                $varDataTip['head'] = $head;
                $varDataTip['title'] = $bodyDecode2['posts']['0']['title'];
                $varDataTip['description'] = $bodyDecode2['posts']['0']['content'];
                $varDataTip['background'] = $bodyDecode2['posts']['0']['acf']['BackgroundImage'];
                $items_content[] = $varDataTip;
            }
            $varData = [];
            $varData['acf_fc_layout'] = 'CardGeneral';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['image'] = $postData['acf']['image'];
            $items_content[] = $varData;
            $i = $i + 1;
    }
    }else{
        foreach ($bodyDecode['posts'] as $postData) {
            $varData = [];
            $varData['acf_fc_layout'] = 'CardGeneral';
            $obj = preg_replace('/\s+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8')));
            $varData['slug'] = $obj ;
            $varData['category'] = $postData['categories']['names'][0];
            $varData['title'] = $postData['title'];
            $excerpt = $postData['excerpt'];
            if (strlen($excerpt) > 90) {
                $excerpt = substr($excerpt, 0, 90) . '...';
            }
            $varData['description'] = $excerpt;
            $varData['image'] = $postData['acf']['image'];
            $items_content[] = $varData;
        }

    }

    $data['item-content'] = $items_content;
  
    return $data;
}


function get_sections_head() {
   
    $base_url = get_site_url(); // o home_url()

    $url = $base_url . '/wp-json/wp/v2/secciones?slug=conoce-home-encabezado,home-footer';
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
  
    $body = wp_remote_retrieve_body( $response );
  
    $bodyDecode = json_decode( $body, true );

    $data = [];

    $data['headerA'] = $bodyDecode['0']['acf']['item_content'];
    $data['footerA'] = $bodyDecode['1']['acf']['section_field'];
    $data['footerB'] = $bodyDecode['1']['acf']['item_content'];
  
    return $data;
}

function get_cat_all($slug) {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/child-categories-by-parent-slug-param?slug='.$slug;
    
    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $bodyDecode = json_decode($body, true);

    // Verifica si la decodificación fue exitosa y si existe child_categories
    if (!$bodyDecode || !isset($bodyDecode['child_categories'])) {
        return false;
    }
    
    $result = []; // Cambiado de $response a $result para consistencia
    foreach($bodyDecode['child_categories'] as $item) { // Corregido: usar $bodyDecode en lugar de $body
        // Verificar que existan los campos necesarios
        if (!isset($item['name'], $item['slug'])) {
            continue;
        }
        
        $result[] = [
            'name' => sanitize_text_field($item['name']),
            'slug' => '/'.'conoce/' . sanitize_title($item['slug']),
            'real-slug' => sanitize_title($item['slug'])
        ];
    }
  
    return $result; // Devuelve $result en lugar de $response
}

function get_cat_learn() {
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/child-categories-by-parent-slug';
    
    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $bodyDecode = json_decode($body, true);

    // Verifica si la decodificación fue exitosa y si existe child_categories
    if (!$bodyDecode || !isset($bodyDecode['child_categories'])) {
        return false;
    }
    
    $result = []; // Cambiado de $response a $result para consistencia
    foreach($bodyDecode['child_categories'] as $item) { // Corregido: usar $bodyDecode en lugar de $body
        // Verificar que existan los campos necesarios
        if (!isset($item['name'], $item['slug'])) {
            continue;
        }
        
        $result[] = [
            'name' => sanitize_text_field($item['name']),
            'slug' => '/'.'conoce/' . sanitize_title($item['slug']) // Eliminada concatenación innecesaria
        ];
    }
  
    return $result; // Devuelve $result en lugar de $response
}

function get_video_learn_normal($slug) {
     $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/posts-with-url-normal?slug='.$slug;
    
    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $bodyDecode = json_decode($body, true);

    // Verifica si la decodificación fue exitosa y si existe child_categories
    if (!$bodyDecode || !isset($bodyDecode['posts'])) {
        return false;
    }
    
    $result = []; // Cambiado de $response a $result para consistencia
    foreach($bodyDecode['posts'] as $item) { // Corregido: usar $bodyDecode en lugar de $body
        // Verificar que existan los campos necesarios
        $result[] = [
            'title' => sanitize_text_field($item['title']),
            'image' =>  sanitize_text_field($item['acf']['image']),
            'url' =>  sanitize_text_field($item['acf']['url']),
            'slug' =>  sanitize_text_field('/'.'conoce/'.$item['categories']['slugs'][0].'/'.$item['slug'])
        ];
    }
  
    return $result; // Devuelve $result en lugar de $response
}

function get_video_learn_short($slug) {
     $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/posts-with-url-short?slug='.$slug;
    
    $response = wp_remote_get($url);
  
    if (is_wp_error($response)) {
        return false;
    }
  
    $body = wp_remote_retrieve_body($response);
    $bodyDecode = json_decode($body, true);

    // Verifica si la decodificación fue exitosa y si existe child_categories
    if (!$bodyDecode || !isset($bodyDecode['posts'])) {
        return false;
    }
    
    $result = []; // Cambiado de $response a $result para consistencia
    foreach($bodyDecode['posts'] as $item) { // Corregido: usar $bodyDecode en lugar de $body
        // Verificar que existan los campos necesarios
        $result[] = [
            'title' => sanitize_text_field($item['title']),
            'image' =>  sanitize_text_field($item['acf']['image']),
            'url' =>  sanitize_text_field($item['acf']['url']),
            'slug' =>  sanitize_text_field('/'.'conoce/'.$item['categories']['slugs'][0].'/'.$item['slug'])
        ];
    }
  
    return $result; // Devuelve $result en lugar de $response
}

function get_subCat_learn() {
    $category = "aprende-con-mercantil";
    $data = ['item-content' => []];
    
    // 1. Obtener categoría padre usando funciones nativas de WordPress
    $parent_cat = get_category_by_slug($category);
    
    if (!$parent_cat) {
        error_log("No se encontró la categoría padre: " . $category);
        return $data;
    }
    
    // 2. Obtener subcategorías directamente
    $subcats = get_categories([
        'parent' => $parent_cat->term_id,
        'hide_empty' => false
    ]);
    
    if (is_wp_error($subcats)) {
        error_log("Error al obtener subcategorías: " . $subcats->get_error_message());
        return $data;
    }
    
    // 3. Procesar posts de cada subcategoría
    $catPost = [];
    foreach ($subcats as $subcat) {
        $posts = get_posts([
            'category' => $subcat->term_id,
            'posts_per_page' => 1,
            'post_status' => 'publish'
        ]);
        
        if (!empty($posts)) {
            $post = $posts[0];
            $acf_image = get_field('image', $post->ID);
            // Extraer solo la URL de la imagen
            $image_url = '';
            if (is_array($acf_image) && isset($acf_image['url'])) {
                $image_url = $acf_image['url'];
            } elseif (is_string($acf_image)) {
                $image_url = $acf_image;
            } elseif (is_object($acf_image) && isset($acf_image->url)) {
                $image_url = $acf_image->url;
            }
            
            $catPost[] = [
                'acf_fc_layout' => 'CardsMediaContent',
                'image'        => $image_url,
                'title'        => $post->post_title,
                'description'  => (mb_strlen($post->post_excerpt, 'UTF-8') > 140) 
                    ? mb_substr($post->post_excerpt, 0, 137, 'UTF-8') . '…' 
                    : $post->post_excerpt,
                'boolean'      => true,
                'action'       => [
                    [
                        'type' => 'post',
                        'url'  => 'conoce/aprende-con-mercantil/'.$post->post_name
                    ]
                ]
            ];
            
        }
    }

    $component = []; 
    $component['acf_fc_layout'] = 'Wysiwyg';
    $dataList['padding'] = false;
    $component['editor'] = '<h5 class="subtitle-headline-1" style="color: #000000; text-align: center;"><span style="color: #000000;" class="colorT1">Aprende con Mercantil</span></h5><h2 class="title-heading-2" style="color: #000000; text-align: center;"><span style="color: #004e9b;" class="colorT3">Cómo realizar operaciones</span><span style="color: #000000;"></span></h2>';
    $data['item-content'][] = $component;
    
    // Construir la respuesta
    if (!empty($catPost)) {
        $data['item-content'][] = [
            'acf_fc_layout' => 'ListCards',
            'padding' => false,
            'list_card' => [
                ['card_type' => $catPost]
            ]
        ];
    }
   
    $component = []; 
   $component['acf_fc_layout'] = 'Wysiwyg';
   $dataList['padding'] = false;
   $component['editor'] = '<p class="paragraph-body-1" style="color: #000000; text-align: center;"><span>Si necesitas ayuda personalizada, solicita ayuda a nuestra asistente virtual MIA.</span></p><hr style="border: none;" />';
   $items_content[] = $component;
   //$data['item-content'][] = $component;
    
    return $data;
}

function get_posts_more_lists($category,$page){
    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/posts-sin-tags?slug='.$category.'&page='.$page;
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }

    $url2 = $base_url . '/wp-json/custom/v1/posts-con-tag?slug='.$category.'&tag=29&page='.$page;
    $response2 = wp_remote_get( $url2 );
    $body2 = wp_remote_retrieve_body( $response2 );
    $bodyDecode2 = json_decode( $body2, true );

    $body = wp_remote_retrieve_body( $response );
    $bodyDecode = json_decode( $body, true );
    $items_content = [];
    $tip = false;
    $data = [];
    $data['page'] = $bodyDecode['page'];
    $data['total_pages'] = $bodyDecode['total_pages'];

    if(!empty($bodyDecode2)){
        if(!empty($bodyDecode2['posts'])){
            $tip = true;
        }
    }
    
    if ($page % 2 === 0) {
        $body = wp_remote_retrieve_body( $response );
        $bodyDecode = json_decode( $body, true );
        $items_content = [];
        $tip = false;
        $data = [];
        $data['page'] = $bodyDecode['page'];
        $data['total_pages'] = $bodyDecode['total_pages'];

        if(!empty($bodyDecode2)){
            if(!empty($bodyDecode2['posts'])){
                $tip = true;
            }
        }
        $i = 1;
        if($tip == true){
            foreach ($bodyDecode['posts'] as $postData) {
                if($i == 3){                  
                    $varDataTip = [];
                    $varDataTip['acf_fc_layout'] = 'CardTip';
                    $varDataTip['slug'] = $bodyDecode2['posts']['0']['slug'];
                    $varDataTip['title'] = $bodyDecode2['posts']['0']['title'];
                    $head = $bodyDecode2['posts']['0']['categories'][0][0]['name'];
                    $y=0;
                    foreach ($bodyDecode2['posts']['0']['categories'][0] as $item) {
                        if($y > 0){
                            $head = $head.  ' / ' . $item['name'];
                        }
                        $y = $y + 1;
                    }
                    $varDataTip['head'] = $head;
                    $varDataTip['description'] = $bodyDecode2['posts']['0']['content'];
                    $excerpt = $bodyDecode2['posts']['0']['content'];
                    if (strlen($excerpt) > 90) {
                        $excerpt = substr($excerpt, 0, 90) . '...';
                    }
                    $varData['description'] = $excerpt;
                    $varDataTip['background'] = $bodyDecode2['posts']['0']['acf']['BackgroundImage'];
                    $items_content[] = $varDataTip;
                }
                $varData = [];
                $varData['acf_fc_layout'] = 'CardGeneral';
                $obj = preg_replace('/\s+/', '-', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8'));
                $varData['slug'] = $obj ;
                $varData['category'] = $postData['categories']['names'][0];
                $varData['title'] = $postData['title'];
                $excerpt = $postData['excerpt'];
                if (strlen($excerpt) > 90) {
                    $excerpt = substr($excerpt, 0, 90) . '...';
                }
                $varData['description'] = $excerpt;
                $varData['image'] = $postData['acf']['image'];
                $items_content[] = $varData;
                $i = $i + 1;
        }
        }else{
            foreach ($bodyDecode['posts'] as $postData) {
                $varData = [];
                $varData['acf_fc_layout'] = 'CardGeneral';
                $obj = preg_replace('/\s+/', '-', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8'));
                $varData['slug'] = $obj ;
                $varData['category'] = $postData['categories']['names'][0];
                $varData['title'] = $postData['title'];
                $excerpt = $postData['excerpt'];
                if (strlen($excerpt) > 90) {
                    $excerpt = substr($excerpt, 0, 90) . '...';
                }
                $varData['description'] = $excerpt;
                $varData['image'] = $postData['acf']['image'];
                $items_content[] = $varData;
            }

        }
    }else{
        $body = wp_remote_retrieve_body( $response );
        $bodyDecode = json_decode( $body, true );
        $items_content = [];
        $tip = false;
        $data = [];
        $data['page'] = $bodyDecode['page'];
        $data['total_pages'] = $bodyDecode['total_pages'];

        if(!empty($bodyDecode2)){
            if(!empty($bodyDecode2['posts'])){
                $tip = true;
            }
        }
        $i = 1;
        if($tip == true){
            foreach ($bodyDecode['posts'] as $postData) {
                if($i == 5){
                    
                    $varDataTip = [];
                    $varDataTip['acf_fc_layout'] = 'CardTip';
                    $varDataTip['slug'] = $bodyDecode2['posts']['0']['slug'];
                    $varDataTip['title'] = $bodyDecode2['posts']['0']['title'];
                    $head = $bodyDecode2['posts']['0']['categories'][0][0]['name'];
                    $y=0;
                    foreach ($bodyDecode2['posts']['0']['categories'][0] as $item) {
                        if($y > 0){
                            $head = $head.  ' / ' . $item['name'];
                        }
                        $y = $y + 1;
                    }
                    $varDataTip['head'] = $head;
                    $excerpt = $bodyDecode2['posts']['0']['content'];
                    if (strlen($excerpt) > 90) {
                        $excerpt = substr($excerpt, 0, 90) . '...';
                    }
                    $varData['description'] = $excerpt;
                    $varDataTip['background'] = $bodyDecode2['posts']['0']['acf']['BackgroundImage'];
                    $items_content[] = $varDataTip;
                }
                $varData = [];
                $varData['acf_fc_layout'] = 'CardGeneral';
                $obj = preg_replace('/\s+/', '-', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8'));
                $varData['slug'] = $obj ;
                $varData['category'] = $postData['categories']['names'][0];
                $varData['title'] = $postData['title'];
                $excerpt = $postData['excerpt'];
                if (strlen($excerpt) > 90) {
                    $excerpt = substr($excerpt, 0, 90) . '...';
                }
                $varData['description'] = $excerpt;
                $varData['image'] = $postData['acf']['image'];
                $items_content[] = $varData;
                $i = $i + 1;
        }
        }else{
            foreach ($bodyDecode['posts'] as $postData) {
                $varData = [];
                $varData['acf_fc_layout'] = 'CardGeneral';
                $obj = preg_replace('/\s+/', '-', mb_strtolower('conoce/'.$postData['categories']['names'][0].'/'.$postData['slug'], 'UTF-8'));
                $varData['slug'] = $obj ;
                $varData['category'] = $postData['categories']['names'][0];
                $varData['title'] = $postData['title'];
                $excerpt = $postData['excerpt'];
                if (strlen($excerpt) > 90) {
                    $excerpt = substr($excerpt, 0, 90) . '...';
                }
                $varData['description'] = $excerpt;
                $varData['image'] = $postData['acf']['image'];
                $items_content[] = $varData;
            }

        }
    }

    $data['yoast_head'] = $seo;
    $data['item-content'] = $items_content;
  
    return $data;

}

function get_posts_detail($slug){

    $base_url = get_site_url(); // o home_url()
    $url = $base_url . '/wp-json/custom/v1/post-by-slug?slug='.$slug;
    $response = wp_remote_get( $url );
  
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $body = wp_remote_retrieve_body( $response );
    $bodyDecode = json_decode( $body, true );

    $items_content = $bodyDecode;

    return $items_content;


}

 ?>