<?php
/**
 * Plugin Name: WooCommerce Address Shipping Surcharge
 * Description: Automatically apply shipping surcharges based on customer address keywords, cities, and delivery zones in WooCommerce.
 * Version: 1.0.2
 * Author: Kamran Hajhossein
 * Author URI: https://webrabin.com
 * Text Domain: woocommerce-address-shipping-surcharge
 * Requires Plugins: woocommerce
 * Requires PHP: 7.4
 * Requires at least: 6.5
 * WC requires at least: 8.0
 * License: GPL-2.0-or-later
 */
if ( ! defined( 'ABSPATH' ) ) exit;

final class WASS_Address_Shipping_Surcharge {
    const OPT = 'wass_settings';
    const LEGACY_OPT = 'dadf_settings';
    const LOG_SOURCE = 'woocommerce-address-shipping-surcharge';
    const SESSION_LABEL = 'wass_current_fee_label';

    public function __construct() {
        $this->maybe_migrate_legacy_settings();
        add_action( 'admin_menu', [ $this, 'menu' ] );
        add_action( 'admin_init', [ $this, 'register' ] );
        add_action( 'woocommerce_checkout_update_order_review', [ $this, 'capture_checkout' ], 1 );
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'apply_fee' ], 9999 );
        add_action( 'wp_footer', [ $this, 'checkout_js' ], 99 );
    }

    private function maybe_migrate_legacy_settings() {
        if ( false === get_option( self::OPT, false ) ) {
            $legacy = get_option( self::LEGACY_OPT, false );
            if ( is_array( $legacy ) ) {
                add_option( self::OPT, $legacy );
            }
        }
    }

    private function defaults() {
        return [
            'enabled'       => 'yes',
            'debug'         => 'no',
            'display_mode'  => 'custom',
            'label_template'=> 'افزایش هزینه ارسال به محدوده {zone}',
            'rules'         => "منطقه نمونه|50000\nشهر نمونه|75000",
        ];
    }

    private function settings() {
        return wp_parse_args( get_option( self::OPT, [] ), $this->defaults() );
    }

    public function menu() {
        add_submenu_page(
            'woocommerce',
            'هزینه محدوده آدرس',
            'هزینه محدوده آدرس',
            'manage_woocommerce',
            'woocommerce-address-shipping-surcharge',
            [ $this, 'page' ]
        );
    }

    public function register() {
        register_setting( 'dadf_group', self::OPT, [ $this, 'sanitize' ] );
    }

    public function sanitize( $in ) {
        $mode = isset( $in['display_mode'] ) ? sanitize_key( $in['display_mode'] ) : 'custom';
        if ( ! in_array( $mode, [ 'custom', 'simple' ], true ) ) $mode = 'custom';

        return [
            'enabled'        => isset( $in['enabled'] ) ? 'yes' : 'no',
            'debug'          => isset( $in['debug'] ) ? 'yes' : 'no',
            'display_mode'   => $mode,
            'label_template' => sanitize_text_field( $in['label_template'] ?? 'افزایش هزینه ارسال به محدوده {zone}' ),
            'rules'          => sanitize_textarea_field( $in['rules'] ?? '' ),
        ];
    }

    public function page() {
        $s = $this->settings(); ?>
        <div class="wrap" dir="rtl">
            <h1>هزینه محدوده بر اساس آدرس</h1>
            <p>مبلغ افزایشی بر اساس کلمات موجود در شهر یا آدرس مشتری به جمع سفارش اضافه می‌شود.</p>

            <form method="post" action="options.php">
                <?php settings_fields( 'dadf_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th>فعال</th>
                        <td><label><input type="checkbox" name="<?php echo esc_attr( self::OPT ); ?>[enabled]" value="1" <?php checked( $s['enabled'], 'yes' ); ?>> فعال باشد</label></td>
                    </tr>

                    <tr>
                        <th>نحوه نمایش هزینه</th>
                        <td>
                            <select name="<?php echo esc_attr( self::OPT ); ?>[display_mode]">
                                <option value="custom" <?php selected( $s['display_mode'], 'custom' ); ?>>متن سفارشی</option>
                                <option value="simple" <?php selected( $s['display_mode'], 'simple' ); ?>>متن ساده</option>
                            </select>
                            <p class="description">هزینه برای شفافیت کامل همیشه در سبد خرید و تسویه‌حساب نمایش داده می‌شود.</p>
                        </td>
                    </tr>

                    <tr>
                        <th>متن سفارشی</th>
                        <td>
                            <input class="regular-text" style="min-width:420px" name="<?php echo esc_attr( self::OPT ); ?>[label_template]" value="<?php echo esc_attr( $s['label_template'] ); ?>">
                            <p class="description">از <code>{zone}</code> برای نام محدوده استفاده کن. مثال: <code>هزینه ارسال به محدوده {zone} افزایش دارد</code></p>
                            <p class="description">مبلغ به‌صورت خودکار توسط ووکامرس روبه‌روی این متن نمایش داده می‌شود.</p>
                        </td>
                    </tr>

                    <tr>
                        <th>قوانین</th>
                        <td>
                            <textarea rows="14" class="large-text code" dir="ltr" name="<?php echo esc_attr( self::OPT ); ?>[rules]"><?php echo esc_textarea( $s['rules'] ); ?></textarea>
                            <p>هر خط: <code>کلمه|مبلغ</code> مثال: <code>چیتگر|200000</code></p>
                            <p><strong>مبلغ را با همان واحد داخلی ووکامرس وارد کنید.</strong></p>
                        </td>
                    </tr>

                    <tr>
                        <th>Debug</th>
                        <td>
                            <label><input type="checkbox" name="<?php echo esc_attr( self::OPT ); ?>[debug]" value="1" <?php checked( $s['debug'], 'yes' ); ?>> لاگ فعال باشد</label>
                            <p>مسیر: ووکامرس ← وضعیت ← گزارش‌ها ← Source: <code>woocommerce-address-shipping-surcharge</code></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button( 'ذخیره تنظیمات' ); ?>
            </form>

            <p style="margin-top:24px;color:#646970">Developed by <a href="https://webrabin.com" target="_blank" rel="noopener"><strong>Kamran Hajhossein</strong></a></p>
        </div>
    <?php }

    private function norm( $v ) {
        $v = (string) $v;
        $v = strtr( $v, [
            'ي'=>'ی','ى'=>'ی','ك'=>'ک','ۀ'=>'ه','ة'=>'ه',
            '۰'=>'0','۱'=>'1','۲'=>'2','۳'=>'3','۴'=>'4','۵'=>'5','۶'=>'6','۷'=>'7','۸'=>'8','۹'=>'9',
            '٠'=>'0','١'=>'1','٢'=>'2','٣'=>'3','٤'=>'4','٥'=>'5','٦'=>'6','٧'=>'7','٨'=>'8','٩'=>'9',
            "\xE2\x80\x8C"=>' '
        ] );
        $v = wp_strip_all_tags( $v );
        $v = preg_replace( '/[،,;؛\-_\/\\|]+/u', ' ', $v );
        $v = preg_replace( '/\s+/u', ' ', $v );
        return trim( mb_strtolower( $v, 'UTF-8' ) );
    }

    private function logger( $msg, $ctx = [] ) {
        $s = $this->settings();
        if ( $s['debug'] !== 'yes' || ! function_exists( 'wc_get_logger' ) ) return;
        wc_get_logger()->debug( $msg . ' ' . wp_json_encode( $ctx, JSON_UNESCAPED_UNICODE ), [ 'source' => self::LOG_SOURCE ] );
    }

    public function capture_checkout( $posted ) {
        if ( ! function_exists( 'WC' ) || ! WC()->session ) return;
        $data = [];
        parse_str( wp_unslash( $posted ), $data );
        $ship = ! empty( $data['ship_to_different_address'] );
        $parts = [];
        $keys = $ship
            ? [ 'shipping_city','shipping_address_1','shipping_address_2','shipping_postcode' ]
            : [ 'billing_city','billing_address_1','billing_address_2','billing_postcode' ];

        foreach ( $keys as $k ) {
            if ( ! empty( $data[$k] ) ) $parts[] = wc_clean( $data[$k] );
        }
        $text = $this->norm( implode( ' ', $parts ) );
        WC()->session->set( 'wass_address_text', $text );
        $this->logger( 'CHECKOUT_CAPTURE', [ 'address_source' => $ship ? 'shipping' : 'billing' ] );
    }

    private function match_rule( $text ) {
        $s = $this->settings();
        $best = null;

        foreach ( preg_split( '/\r\n|\r|\n/', $s['rules'] ) as $line ) {
            $line = trim( $line );
            if ( $line === '' || strpos( $line, '|' ) === false ) continue;

            [ $keyword, $amount ] = array_map( 'trim', explode( '|', $line, 2 ) );
            $keyword = $this->norm( $keyword );
            $amount  = (float) str_replace( [ ',', ' ' ], '', $amount );

            if ( $keyword === '' || $amount <= 0 ) continue;

            if ( mb_strpos( $text, $keyword, 0, 'UTF-8' ) !== false ) {
                $len = mb_strlen( $keyword, 'UTF-8' );
                if ( ! $best || $len > $best['len'] ) {
                    $best = [ 'keyword' => $keyword, 'amount' => $amount, 'len' => $len ];
                }
            }
        }
        return $best;
    }

    private function fee_label( $match, $settings ) {
        if ( $settings['display_mode'] === 'simple' ) {
            return 'افزایش هزینه ارسال - ' . $match['keyword'];
        }

        $template = trim( (string) $settings['label_template'] );
        if ( $template === '' ) $template = 'افزایش هزینه ارسال به محدوده {zone}';

        return str_replace( '{zone}', $match['keyword'], $template );
    }

    public function apply_fee( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
        $s = $this->settings();
        if ( $s['enabled'] !== 'yes' ) return;

        $text = '';
        if ( function_exists( 'WC' ) && WC()->session ) {
            $text = (string) WC()->session->get( 'wass_address_text', '' );
        }

        if ( $text === '' && function_exists( 'WC' ) && WC()->customer ) {
            $c = WC()->customer;
            $text = $this->norm( implode( ' ', [
                $c->get_shipping_city(), $c->get_shipping_address_1(), $c->get_shipping_address_2(),
                $c->get_billing_city(), $c->get_billing_address_1(), $c->get_billing_address_2()
            ] ) );
        }

        $match = $this->match_rule( $text );
        $this->logger( 'FEE_CALC', [
            'matched_keyword' => $match['keyword'] ?? null,
            'amount'          => $match['amount'] ?? null,
        ] );

        if ( ! $match ) {
            if ( function_exists( 'WC' ) && WC()->session ) WC()->session->__unset( self::SESSION_LABEL );
            return;
        }

        $label = $this->fee_label( $match, $s );
        $cart->add_fee( $label, $match['amount'], false );

        if ( function_exists( 'WC' ) && WC()->session ) {
            WC()->session->set( self::SESSION_LABEL, $label );
        }

        $this->logger( 'FEE_APPLIED', [
            'label'   => $label,
            'keyword' => $match['keyword'],
            'amount'  => $match['amount'],
            'mode'    => $s['display_mode'],
        ] );
    }

    public function checkout_js() {
        if ( ! function_exists( 'is_checkout' ) || ( ! is_checkout() && ! is_cart() ) || is_order_received_page() ) return;
        ?>
        <script>
        jQuery(function($){
            let t;
            function refresh(){
                clearTimeout(t);
                t=setTimeout(function(){ $(document.body).trigger('update_checkout'); },500);
            }

            $(document.body).on('input change blur',
                'input[name="billing_address_1"],input[name="billing_address_2"],input[name="billing_city"],input[name="billing_postcode"],input[name="shipping_address_1"],input[name="shipping_address_2"],input[name="shipping_city"],input[name="shipping_postcode"],select[name="billing_city"],select[name="shipping_city"]',
                refresh
            );

        });
        </script>
        <?php
    }
}
new WASS_Address_Shipping_Surcharge();
