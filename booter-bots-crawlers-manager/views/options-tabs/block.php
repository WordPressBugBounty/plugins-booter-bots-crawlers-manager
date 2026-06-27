<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$woocommerce_plugin_active = is_plugin_active( 'woocommerce/woocommerce.php' );
$block_useragents = isset( $settings['block']['block_useragents'] ) ? sanitize_text_field( $settings['block']['block_useragents'] ) : 'bots';
$enabled_woocommerce = isset( $settings['block']['enabled_woocommerce'] ) ? sanitize_text_field( $settings['block']['enabled_woocommerce'] ) : 'yes';
$enabled_woocommerce = $woocommerce_plugin_active ? $enabled_woocommerce : 'no';
$block_empty_useragents = isset( $settings['block']['block_empty_useragents'] ) ? sanitize_text_field( $settings['block']['block_empty_useragents'] ) : 'yes';
$regex_enabled = isset( $settings['block']['regex_enabled'] ) ? sanitize_text_field( $settings['block']['regex_enabled'] ) : 'no';
$http_response = isset( $settings['block']['http_response'] ) ? sanitize_text_field( $settings['block']['http_response'] ) : '410';

$strings = isset( $settings['block']['strings'] ) ? ( is_array( $settings['block']['strings'] ) ? $settings['block']['strings'] : json_decode( $settings['block']['strings'], true ) ) : [];
$strings = is_array( $strings ) ? $strings : []; // Casting / Fallback בטוח
$strings = htmlspecialchars( wp_json_encode( array_map( 'sanitize_text_field', $strings ) ) );

$regex = isset( $settings['block']['regex'] ) ? ( is_array( $settings['block']['regex'] ) ? $settings['block']['regex'] : json_decode( $settings['block']['regex'], true ) ) : [];
$regex = is_array( $regex ) ? $regex : []; // Casting / Fallback בטוח
$regex = htmlspecialchars( wp_json_encode( array_map( 'sanitize_text_field', $regex ) ) );
?>

<p class="notice notice-info">
	<?php esc_html_e( 'Block access to predefined URLs, or cleanup old spam URLs by setting a corresponding HTTP status code.', 'booter-bots-crawlers-manager' ); ?><br>
</p>
<p class="notice notice-warning">
    <span aria-hidden="true" class="dashicons dashicons-info"></span>
	<?php esc_html_e( 'Note: If you are in the process of handling spam links, make sure to disable all types of automatic redirects to https/www, this is done to allow search engines to crawl the links and reach the HTTP 410 status. If this is not done, search engines will see a redirect - which will cause the link to remain indexed for a long time. After the treatment is complete make sure to re-enable all required redirects.', 'booter-bots-crawlers-manager' ); ?>
</p>

<table class="form-table">
	<tr valign="top">
        <th scope="row"><label for="booter-block-block_useragents"><?php esc_html_e( 'Apply To', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
            <radio-toggle id="booter-block-block_useragents"
                          name="booter_settings[block][block_useragents]"
                          value="<?php echo esc_attr($block_useragents); ?>"
                          options='<?php echo esc_attr( wp_json_encode( [ 'bots' => __( 'Known Bots', 'booter-bots-crawlers-manager' ), 'all' => __( 'Everyone', 'booter-bots-crawlers-manager' ) ] ) ); ?>'
            ></radio-toggle>
            <p class="description">
				<?php esc_html_e( 'Choose who will be blocked by Booter, only known bots, or everyone including logged-in users.', 'booter-bots-crawlers-manager' ); ?>
            </p>
		</td>
	</tr>

    <tr valign="top">
        <th scope="row"><label for="booter-block-block_empty_useragents"><?php esc_html_e( 'Block Empty User Agents', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <booter-switch id="booter-block-block_empty_useragents" name="booter_settings[block][block_empty_useragents]" value="<?php echo esc_attr($block_empty_useragents); ?>"></booter-switch>
            <p class="description">
				<?php esc_html_e( 'Every browser has a user agent identifying it, the operating system, and the device it is running on. This option will block access to users identified with a blank user agent.', 'booter-bots-crawlers-manager' ); ?>
            </p>
        </td>
    </tr>

	<tr valign="top">
        <th scope="row"><label for="booter-block-enabled_woocommerce"><?php esc_html_e( 'Include WooCommerce Filtering URLs', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
            <booter-switch id="booter-block-enabled_woocommerce" name="booter_settings[block][enabled_woocommerce]" value="<?php echo esc_attr($enabled_woocommerce); ?>" <?php disabled( false, $woocommerce_plugin_active ); ?>></booter-switch>
            <p class="description">
                <?php esc_html_e( 'Some of the WooCommerce URLs such as add-to-cart buttons and filters can cause infinite loops for search engines if when the theme doesn\'t provide a noindex/nofollow property.', 'booter-bots-crawlers-manager' ); ?>
                <br>
                <?php
                echo wp_kses_post(
                        sprintf(
                        /* translators: 1: example URL, 2: example URL with repeated filter */
                                _x( 'For example for a badly coded theme: %1$s which includes a URL to the same URL with another (same) filter %2$s which again includes the same filter, and so on.', 'WooCommerce infinite loops explanation', 'booter-bots-crawlers-manager' ),
                                sprintf( '<code>%s</code>', esc_url( site_url( '/?filter_brand=example' ) ) ),
                                sprintf( '<code>%s</code>', esc_url( site_url( '/?filter_brand=example&filter_brand=example' ) ) )
                        )
                );
                ?>
            </p>
		</td>
	</tr>

	<tr valign="top">
        <th scope="row"><label for="booter-block-strings"><?php esc_html_e( 'URL Strings', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
            <tags-list-single value="<?php echo esc_attr($strings); ?>" id="booter-block-strings" add-label-text="<?php esc_html_e( 'Add a string to block', 'booter-bots-crawlers-manager' ); ?>" name="booter_settings[block][strings]"></tags-list-single>
            <p class="description">
				<?php esc_html_e( 'Booter will search for these strings in the URL a bot is trying to access and will block the request if it finds one of the strings.', 'booter-bots-crawlers-manager' ); ?>
            </p>
        </td>
	</tr>

    <tr valign="top">
        <th scope="row"><label for="booter-block-regex_enabled"><?php esc_html_e( 'Regular Expression Based Blocks', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <booter-switch id="booter-block-regex_enabled" name="booter_settings[block][regex_enabled]" value="<?php echo esc_attr($regex_enabled); ?>" type="warning" data-toggle-on=".js-booter-block-regex"></booter-switch>
            <p class="description">
				<?php esc_html_e( 'This is an advanced option, only enable if you know what you are doing.', 'booter-bots-crawlers-manager' ); ?>
            </p>
        </td>
    </tr>
    <tr valign="top" class="js-booter-block-regex">
        <th scope="row"><label for="booter-block-regex"><?php esc_html_e( 'Regular Expressions', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <strings-list id="booter-block-regex"
                                 name="booter_settings[block][regex]"
                                 value="<?php echo esc_attr($regex); ?>"
                                 add-label-text="<?php echo esc_attr_x( 'Regular Expression eg. ^/index.php?filter=.*$', 'regular expression string placeholder', 'booter-bots-crawlers-manager' ); ?>"></strings-list>
        </td>
    </tr>

	<tr valign="top">
        <th scope="row"><label for="booter-block-http_response"><?php esc_html_e( 'Block HTTP Response', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
			<select id="booter-block-http_response" name="booter_settings[block][http_response]">
				<option value="401" <?php selected( '401', $http_response ); ?>>
					<?php esc_html_e( '401 Unauthorized - The crawler is not permitted to access this URL', 'booter-bots-crawlers-manager' ); ?>
				</option>
				<option value="403" <?php selected( '403', $http_response ); ?>>
					<?php esc_html_e( '403 Forbidden - Access to this specific URL is not allowed', 'booter-bots-crawlers-manager' ); ?>
				</option>
				<option value="404" <?php selected( '404', $http_response ); ?>>
					<?php esc_html_e( '404 Page Not Found - There is nothing in this URL (search engines might continue crawling this URL in case something will be there in the future)', 'booter-bots-crawlers-manager' ); ?>
				</option>
				<option value="410" <?php selected( '410', $http_response ); ?>>
					<?php esc_html_e( '410 Gone - The URL will never be available', 'booter-bots-crawlers-manager' ); ?>
					<?php esc_html_e( '(Recommended)', 'booter-bots-crawlers-manager' ); ?>
				</option>
			</select>
			<br>
			<p class="description"><?php esc_html_e( 'This is the response returned when blocking. Each response means something different to search engines, so make sure to use the most correct response.', 'booter-bots-crawlers-manager' ); ?></p>
		</td>
	</tr>
</table>

<?php submit_button(); ?>
