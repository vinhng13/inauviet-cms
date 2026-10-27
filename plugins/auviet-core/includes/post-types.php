<?php
/**
 * Loại nội dung và phân loại (mục 5 Tài liệu mô tả công nghệ).
 * URL theo mục 3 Tài liệu mô tả chức năng.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Nhãn tiếng Việt cho một loại nội dung.
 */
function av_cpt_labels( string $name, string $singular ): array {
	return array(
		'name'               => $name,
		'singular_name'      => $singular,
		'menu_name'          => $name,
		'add_new'            => 'Thêm mới',
		'add_new_item'       => 'Thêm ' . mb_strtolower( $singular ),
		'edit_item'          => 'Sửa ' . mb_strtolower( $singular ),
		'new_item'           => $singular . ' mới',
		'view_item'          => 'Xem ' . mb_strtolower( $singular ),
		'search_items'       => 'Tìm ' . mb_strtolower( $singular ),
		'not_found'          => 'Chưa có ' . mb_strtolower( $singular ),
		'not_found_in_trash' => 'Thùng rác trống',
		'all_items'          => 'Tất cả',
	);
}

function av_register_content_types(): void {
	$common = array(
		'show_in_rest' => true,
		'map_meta_cap' => true,
		'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
	);

	// SP-02, QT-03: Sản phẩm — /san-pham/<ten-san-pham>/
	register_post_type(
		'av_product',
		$common + array(
			'labels'          => av_cpt_labels( 'Sản phẩm', 'Sản phẩm' ),
			'public'          => true,
			'has_archive'     => 'san-pham',
			'rewrite'         => array( 'slug' => 'san-pham', 'with_front' => false ),
			'menu_icon'       => 'dashicons-products',
			'menu_position'   => 20,
			'capability_type' => array( 'av_product', 'av_products' ),
		)
	);

	// SP-05: Thư viện chất liệu — /chat-lieu/
	register_post_type(
		'av_material',
		$common + array(
			'labels'          => av_cpt_labels( 'Chất liệu', 'Chất liệu' ),
			'public'          => true,
			'has_archive'     => 'chat-lieu',
			'rewrite'         => array( 'slug' => 'chat-lieu', 'with_front' => false ),
			'menu_icon'       => 'dashicons-art',
			'menu_position'   => 21,
			'capability_type' => array( 'av_material', 'av_materials' ),
		)
	);

	// LP-05, QT-06: Mẫu đã làm — /mau-da-lam/
	register_post_type(
		'av_project',
		$common + array(
			'labels'          => av_cpt_labels( 'Mẫu đã làm', 'Mẫu' ),
			'public'          => true,
			'has_archive'     => 'mau-da-lam',
			'rewrite'         => array( 'slug' => 'mau-da-lam', 'with_front' => false ),
			'menu_icon'       => 'dashicons-format-gallery',
			'menu_position'   => 22,
			'capability_type' => array( 'av_project', 'av_projects' ),
		)
	);

	// TT-04: Hỏi đáp — /hoi-dap/
	register_post_type(
		'av_faq',
		array(
			'labels'          => av_cpt_labels( 'Hỏi đáp', 'Câu hỏi' ),
			'public'          => true,
			'has_archive'     => 'hoi-dap',
			'rewrite'         => array( 'slug' => 'hoi-dap', 'with_front' => false ),
			'menu_icon'       => 'dashicons-editor-help',
			'menu_position'   => 23,
			'show_in_rest'    => true,
			'map_meta_cap'    => true,
			'supports'        => array( 'title', 'editor', 'revisions' ),
			'capability_type' => array( 'av_faq', 'av_faqs' ),
		)
	);

	// QT-07: Yêu cầu báo giá — không công khai, chỉ Quản trị viên
	register_post_type(
		'av_quote',
		array(
			'labels'          => av_cpt_labels( 'Yêu cầu báo giá', 'Yêu cầu' ),
			'public'          => false,
			'show_ui'         => true,
			'show_in_rest'    => false,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 3,
			'map_meta_cap'    => true,
			'supports'        => array( 'title' ),
			'capability_type' => array( 'av_quote', 'av_quotes' ),
		)
	);

	// SP-01: Nhóm sản phẩm — /danh-muc/<nhom>/
	register_taxonomy(
		'av_group',
		array( 'av_product' ),
		array(
			'labels'            => array(
				'name'          => 'Nhóm sản phẩm',
				'singular_name' => 'Nhóm sản phẩm',
				'add_new_item'  => 'Thêm nhóm',
				'edit_item'     => 'Sửa nhóm',
				'all_items'     => 'Tất cả nhóm',
				'parent_item'   => 'Nhóm cha',
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'danh-muc', 'with_front' => false, 'hierarchical' => true ),
			'capabilities'      => av_taxonomy_caps( 'av_products' ),
		)
	);

	// LP-05: Ngành của dự án (dùng để lọc mẫu đã làm)
	register_taxonomy(
		'av_industry',
		array( 'av_project' ),
		array(
			'labels'            => array( 'name' => 'Ngành', 'singular_name' => 'Ngành' ),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'nganh', 'with_front' => false ),
			'capabilities'      => av_taxonomy_caps( 'av_projects' ),
		)
	);

	// TT-04: Nhóm câu hỏi
	register_taxonomy(
		'av_faq_group',
		array( 'av_faq' ),
		array(
			'labels'            => array( 'name' => 'Nhóm câu hỏi', 'singular_name' => 'Nhóm câu hỏi' ),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'capabilities'      => av_taxonomy_caps( 'av_faqs' ),
		)
	);
}
add_action( 'init', 'av_register_content_types' );

/**
 * Quyền quản lý phân loại đi theo quyền sửa loại nội dung tương ứng.
 */
function av_taxonomy_caps( string $plural ): array {
	return array(
		'manage_terms' => 'edit_' . $plural,
		'edit_terms'   => 'edit_' . $plural,
		'delete_terms' => 'edit_' . $plural,
		'assign_terms' => 'edit_' . $plural,
	);
}
