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
		array( 'av_manage_site_content' )
	);

	$admin_only = array_merge(
		av_post_type_caps( 'av_quotes' ),
		array( 'av_export_quotes', 'av_manage_prices', 'av_manage_site_system' )
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
	update_option( 'av_caps_version', AV_CORE_VERSION, false );
}

add_action(
	'init',
	static function () {
		if ( get_option( 'av_caps_version' ) !== AV_CORE_VERSION ) {
			av_grant_capabilities();
		}
	},
	20
);
