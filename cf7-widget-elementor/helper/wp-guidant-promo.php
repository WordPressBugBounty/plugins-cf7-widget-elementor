<?php
/**
 * WP Guidant promotional banner.
 *
 * Shown on the WordPress Dashboard and the Plugins page when WooCommerce is
 * installed and WP Guidant is not. The "X" icon hides it for 1 day and
 * "Never show again" hides it permanently.
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'VOID_CF7_GUIDANT_HIDE_UNTIL', 'void_cf7_guidant_promo_hide_until' );
define( 'VOID_CF7_GUIDANT_NEVER', 'void_cf7_guidant_promo_never' );

/**
 * Should the banner be displayed on the current request?
 */
function void_cf7_guidant_promo_should_display() {
    if ( ! current_user_can( 'install_plugins' ) ) {
        return false;
    }

    // Only on the Dashboard and the Plugins page.
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'plugins' ), true ) ) {
        return false;
    }

    // Dismissed for ever.
    if ( 'yes' === get_option( VOID_CF7_GUIDANT_NEVER ) ) {
        return false;
    }

    // Temporarily dismissed (1 day).
    if ( (int) get_option( VOID_CF7_GUIDANT_HIDE_UNTIL, 0 ) > time() ) {
        return false;
    }

    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // WooCommerce must be installed.
    $woo_installed = class_exists( 'WooCommerce' ) || file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
    if ( ! $woo_installed ) {
        return false;
    }

    // Do not promote WP Guidant if it (free or pro) is already installed.
    foreach ( get_plugins() as $file => $data ) {
        if ( 0 === strpos( $file, 'guidant/' ) || false !== stripos( $data['Name'], 'Guidant' ) ) {
            return false;
        }
    }

    return true;
}

/**
 * Render the banner.
 */
function void_cf7_guidant_promo_notice() {
    if ( ! void_cf7_guidant_promo_should_display() ) {
        return;
    }

    $install_url = admin_url( 'plugin-install.php?s=wp%2520guidant&tab=search&type=term' );
    ?>
    <div class="notice void-cf7-guidant-promo" data-nonce="<?php echo esc_attr( wp_create_nonce( 'void_cf7_guidant_promo' ) ); ?>">
        <button type="button" class="void-cf7-guidant-promo__close" title="<?php esc_attr_e( 'Hide for 1 day', 'void' ); ?>">
            <span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
            <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice for 1 day.', 'void' ); ?></span>
        </button>

        <div class="void-cf7-guidant-promo__image">
            <img src="<?php echo esc_url( CF7_WIDGET_E_PLUGIN_URL . 'assets/wp-guidant-banner.jpg' ); ?>" alt="<?php esc_attr_e( 'WP Guidant - Guided Selling & Product Recommendation Quiz', 'void' ); ?>">
        </div>

        <div class="void-cf7-guidant-promo__content">
            <h3><?php esc_html_e( 'We noticed you have WooCommerce installed on your site.', 'void' ); ?></h3>
            <p>
                <?php
                printf(
                    /* translators: %s: plugin name */
                    esc_html__( 'Get %s to boost conversions and increase sales! Drive purchases through guided selling, bespoke product recommendations, and interactive quizzes funneling customers to perfect choices.', 'void' ),
                    '<strong>' . esc_html__( 'WP Guidant', 'void' ) . '</strong>'
                );
                ?>
            </p>
            <div class="void-cf7-guidant-promo__actions">
                <a class="button button-primary void-cf7-guidant-promo__button" href="<?php echo esc_url( $install_url ); ?>"><?php esc_html_e( 'Install the free version now', 'void' ); ?></a>
                <a class="void-cf7-guidant-promo__never" href="#"><?php esc_html_e( 'Never show again ->', 'void' ); ?></a>
            </div>
        </div>
    </div>
    <?php
}
add_action( 'admin_notices', 'void_cf7_guidant_promo_notice' );

/**
 * Enqueue banner assets only when the banner is displayed.
 */
function void_cf7_guidant_promo_assets() {
    if ( ! void_cf7_guidant_promo_should_display() ) {
        return;
    }
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style( 'void-cf7-guidant-promo', CF7_WIDGET_E_PLUGIN_URL . 'assets/css/wp-guidant-promo.css', array(), CF7_WIDGET_E_VERSION );
    wp_enqueue_script( 'void-cf7-guidant-promo', CF7_WIDGET_E_PLUGIN_URL . 'assets/js/wp-guidant-promo.js', array( 'jquery' ), CF7_WIDGET_E_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'void_cf7_guidant_promo_assets' );

/**
 * AJAX: dismiss for 1 day or for ever.
 */
function void_cf7_guidant_promo_dismiss() {
    check_ajax_referer( 'void_cf7_guidant_promo', 'nonce' );

    if ( ! current_user_can( 'install_plugins' ) ) {
        wp_send_json_error( null, 403 );
    }

    $mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : '';

    if ( 'never' === $mode ) {
        update_option( VOID_CF7_GUIDANT_NEVER, 'yes', false );
    } else {
        update_option( VOID_CF7_GUIDANT_HIDE_UNTIL, time() + DAY_IN_SECONDS, false );
    }

    wp_send_json_success();
}
add_action( 'wp_ajax_void_cf7_guidant_promo_dismiss', 'void_cf7_guidant_promo_dismiss' );
