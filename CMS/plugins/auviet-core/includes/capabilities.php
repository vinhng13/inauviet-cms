<?php
/**
 * Phân quyền (mục 4 Tài liệu mô tả chức năng, mục 7 Tài liệu mô tả công nghệ).
 * Hai vai trò: administrator (Quản trị viên) và editor (Biên tập viên).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Bộ quyền nguyên thuỷ cho một loại nội dung dùng capability_type riêng.
 */
function av_post_type_caps( string $plural ): array {
	return array(
		"edit_{$plural}",
		"edit_others_{$plural}",
		"edit_private_{$plural}",
		"edit_published_{$plural}",
		"publish_{$plural}",
		"read_private_{$plural}",
		"delete_{$plural}",
		"delete_others_{$plural}",
		"delete_private_{$plural}",
		"delete_published_{$plural}",
	);
}

/**
 * Ma trận quyền: vai trò => danh sách capability.
 */
function av_capability_map(): array {
	$content = array_merge(
		av_post_type_caps( 'av_products' ),
		av_post_type_caps( 'av_materials' ),
		av_post_type_caps( 'av_projects' ),
		av_post_type_caps( 'av_faqs' ),
		// Quyền "Thêm" tách riêng khỏi "Xem" (create_*), quyền danh mục, chủ đề bài viết tách khỏi bài viết.
		array( 'create_posts', 'create_pages', 'create_av_products', 'create_av_materials', 'create_av_projects', 'create_av_faqs' ),
		array( 'av_edit_post_terms', 'av_delete_post_terms' ),
		array( 'av_manage_site_content', 'av_manage_seo' ),
		// QT-08 SEO (Rank Math): Biên tập viên sửa title, meta, chuyển hướng, xem lỗi 404.
		array( 'rank_math_onpage_general', 'rank_math_onpage_advanced', 'rank_math_onpage_snippet', 'rank_math_onpage_social', 'rank_math_redirections', 'rank_math_404_monitor' )
	);

	$admin_only = array_merge(
		av_post_type_caps( 'av_quotes' ),
		array( 'av_export_quotes', 'av_view_prices', 'av_manage_prices', 'av_manage_site_system', 'av_view_activity_log' )
	);

	return array(
		'administrator' => array_merge( $content, $admin_only ),
		'editor'        => $content,
	);
}

/**
 * Gán quyền cho vai trò. Gọi khi kích hoạt plugin và khi phiên bản quyền thay đổi.
 */
function av_grant_capabilities(): void {
	foreach ( av_capability_map() as $role_name => $caps ) {
		$role = get_role( $role_name );
		if ( ! $role ) {
			continue;
		}
		foreach ( $caps as $cap ) {
			$role->add_cap( $cap );
		}
	}
	// Nhóm tự tạo từ trước: giữ nguyên ý nghĩa quyền cũ khi tách thành quyền nhỏ hơn.
	foreach ( wp_roles()->role_objects as $slug => $role ) {
		if ( ! str_starts_with( $slug, 'av_role_' ) ) {
			continue;
		}
		foreach ( array( 'posts', 'pages', 'av_products', 'av_materials', 'av_projects', 'av_faqs' ) as $plural ) {
			if ( $role->has_cap( "edit_others_{$plural}" ) ) {
				$role->add_cap( "create_{$plural}" );
			}
		}
		if ( $role->has_cap( 'manage_categories' ) ) {
			$role->add_cap( 'av_edit_post_terms' );
			$role->add_cap( 'av_delete_post_terms' );
		}
		if ( $role->has_cap( 'av_manage_prices' ) ) {
			$role->add_cap( 'av_view_prices' );
		}
	}
	update_option( 'av_caps_version', AV_CORE_VERSION . '+caps3', false );
}

add_action(
	'init',
	static function () {
		if ( get_option( 'av_caps_version' ) !== AV_CORE_VERSION . '+caps3' ) {
			av_grant_capabilities();
		}
	},
	20
);

// QT-08: quyền quản lý chuyển hướng của plugin Redirection theo quyền SEO riêng (Quản trị viên, Biên tập viên).
add_filter( 'redirection_role', static fn() => 'av_manage_seo' );

/**
 * Quyền "Thêm" riêng cho từng loại nội dung (create_*): mặc định WordPress gộp chung với quyền sửa.
 */
add_filter(
	'register_post_type_args',
	static function ( array $args, string $type ): array {
		$plural = array(
			'post'        => 'posts',
			'page'        => 'pages',
			'av_product'  => 'av_products',
			'av_material' => 'av_materials',
			'av_project'  => 'av_projects',
			'av_faq'      => 'av_faqs',
		);
		if ( isset( $plural[ $type ] ) ) {
			$args['capabilities']                 = (array) ( $args['capabilities'] ?? array() );
			$args['capabilities']['create_posts'] = 'create_' . $plural[ $type ];
		}
		return $args;
	},
	10,
	2
);

/**
 * Danh mục, chủ đề bài viết: xem theo quyền xem bài, thêm/sửa và xoá là hai quyền riêng.
 */
add_filter(
	'register_taxonomy_args',
	static function ( array $args, string $tax ): array {
		if ( in_array( $tax, array( 'category', 'post_tag' ), true ) ) {
			$args['capabilities'] = array(
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'av_edit_post_terms',
				'delete_terms' => 'av_delete_post_terms',
				'assign_terms' => 'edit_posts',
			);
		}
		return $args;
	},
	5,
	2
);
