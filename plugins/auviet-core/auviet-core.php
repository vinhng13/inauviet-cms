<?php
/**
 * Plugin Name:       Âu Việt Core
 * Description:       Chức năng riêng của website In Âu Việt: loại nội dung, phân quyền, bảng giá, yêu cầu báo giá.
 * Version:           0.4.0
 * Requires at least: 6.6
 * Requires PHP:      8.4
 * Author:            In Quảng Cáo Âu Việt
 * Text Domain:       auviet-core
 */

defined( 'ABSPATH' ) || exit;

define( 'AV_CORE_VERSION', '0.4.0' );
define( 'AV_CORE_FILE', __FILE__ );
define( 'AV_CORE_DIR', plugin_dir_path( __FILE__ ) );

require_once AV_CORE_DIR . 'includes/helpers.php';
require_once AV_CORE_DIR . 'includes/post-types.php';
require_once AV_CORE_DIR . 'includes/capabilities.php';
require_once AV_CORE_DIR . 'includes/activity.php';
require_once AV_CORE_DIR . 'includes/roles.php';
require_once AV_CORE_DIR . 'includes/security.php';
require_once AV_CORE_DIR . 'includes/environment.php';
require_once AV_CORE_DIR . 'includes/install.php';
require_once AV_CORE_DIR . 'includes/settings.php';
require_once AV_CORE_DIR . 'includes/products.php';
require_once AV_CORE_DIR . 'includes/products-admin.php';
require_once AV_CORE_DIR . 'includes/products-front.php';
require_once AV_CORE_DIR . 'includes/legacy.php';
require_once AV_CORE_DIR . 'includes/contact.php';
require_once AV_CORE_DIR . 'includes/about.php';
require_once AV_CORE_DIR . 'includes/design.php';
require_once AV_CORE_DIR . 'includes/blocks.php';
require_once AV_CORE_DIR . 'includes/quote-form.php';
require_once AV_CORE_DIR . 'includes/quotes-admin.php';
require_once AV_CORE_DIR . 'includes/frontend.php';

register_activation_hook(
	__FILE__,
	static function () {
		av_register_content_types();
		av_grant_capabilities();
		av_install_tables();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function () {
		flush_rewrite_rules();
	}
);
