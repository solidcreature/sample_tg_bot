<?php

//Новый тип записи -- Участник
// Register Custom Post Type - Участник
function tg_person_post_type() {

	$labels = array(
		'name'                  => _x( 'Участники', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Участник', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Участники', 'text_domain' ),
		'name_admin_bar'        => __( 'Участники', 'text_domain' ),
		'archives'              => __( 'Архив участников', 'text_domain' ),
		'attributes'            => __( 'Атрибуты участника', 'text_domain' ),
		'parent_item_colon'     => __( 'Родительский элемент', 'text_domain' ),
		'all_items'             => __( 'Все участники', 'text_domain' ),
		'add_new_item'          => __( 'Добавить нового участника', 'text_domain' ),
		'add_new'               => __( 'Добавить нового', 'text_domain' ),
		'new_item'              => __( 'Новый участник', 'text_domain' ),
		'edit_item'             => __( 'Редактировать участника', 'text_domain' ),
		'update_item'           => __( 'Обновить участника', 'text_domain' ),
		'view_item'             => __( 'Посмотреть участника', 'text_domain' ),
		'view_items'            => __( 'Посмотреть участников', 'text_domain' ),
		'search_items'          => __( 'Искать участника', 'text_domain' ),
		'not_found'             => __( 'Не найдены', 'text_domain' ),
		'not_found_in_trash'    => __( 'Не найдены в удаленных', 'text_domain' ),
		'featured_image'        => __( 'Фотография участника', 'text_domain' ),
		'set_featured_image'    => __( 'Задать фотографию', 'text_domain' ),
		'remove_featured_image' => __( 'Удалить фотографию', 'text_domain' ),
		'use_featured_image'    => __( 'Использовать', 'text_domain' ),
		'insert_into_item'      => __( 'Использовать для участника', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Загружено для участника', 'text_domain' ),
		'items_list'            => __( 'Список участников', 'text_domain' ),
		'items_list_navigation' => __( 'Навигация по участникам', 'text_domain' ),
		'filter_items_list'     => __( 'Отсортировать список участников', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Участник', 'text_domain' ),
		'description'           => __( 'Post Type Description', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail' ),
		'taxonomies'            => array( 'status' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( 'tg_person', $args );

}
add_action( 'init', 'tg_person_post_type', 0 );
