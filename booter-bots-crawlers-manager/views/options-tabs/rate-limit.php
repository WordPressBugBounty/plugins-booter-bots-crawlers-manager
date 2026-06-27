<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$enabled_logged_in = isset( $settings['rate_limit']['enabled_logged_in'] ) ? sanitize_text_field( $settings['rate_limit']['enabled_logged_in'] ) : 'no';
$requests_limit = isset( $settings['rate_limit']['requests_limit'] ) ? sanitize_text_field( $settings['rate_limit']['requests_limit'] ) : '30';
$block_for = isset( $settings['rate_limit']['block_for'] ) ? sanitize_text_field( $settings['rate_limit']['block_for'] ) : '300';
$exclude = isset( $settings['rate_limit']['exclude'] ) ? ( is_array( $settings['rate_limit']['exclude'] ) ? $settings['rate_limit']['exclude'] : json_decode( $settings['rate_limit']['exclude'] ) ) : [];
$exclude = htmlspecialchars( json_encode( array_map( 'sanitize_text_field', $exclude ) ) );
?>

<p class="notice notice-info">
    <?php esc_html_e( 'Throttle excessive access from bots, crawlers, and malicious users.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'When a user exceeds the defined amount of requests per minute he will be blocked for a period of time defined.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'The block will return a 429 HTTP status code (too many requests), which tells legitimate users/bots to reduce the crawl rate, making them realize that they make request too frequently but are still desirable, unlike 403 status code which means that they are not allowed here.', 'booter-bots-crawlers-manager' ); ?>
</p>

<table class="form-table">
	<tr valign="top">
        <th scope="row"><label for="booter-rate_limit-enabled_logged_in"><?php esc_html_e( 'Rate Limit Logged In Users', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
            <booter-switch id="booter-rate_limit-enabled_logged_in" name="booter_settings[rate_limit][enabled_logged_in]" value="<?php echo esc_attr( $enabled_logged_in ); ?>"></booter-switch>
            <p class="description">
                <?php esc_html_e( 'Choose if you want to rate limit logged in users, otherwise only guests will be rate limited.', 'booter-bots-crawlers-manager' ); ?>
            </p>
		</td>
	</tr>

	<tr valign="top">
        <th scope="row"><label for="booter-rate_limit-requests_limit"><?php esc_html_e( 'Requests Limit Per Minute', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
			<input id="booter-rate_limit-requests_limit" type="number" name="booter_settings[rate_limit][requests_limit]" class="text"
                   value="<?php echo esc_attr( $requests_limit ); ?>"
			       min="10" max="60"/>
			<span class="description"><?php esc_html_e( 'per minute', 'booter-bots-crawlers-manager' ); ?></span>
            <p class="description">
				<?php esc_html_e( 'This is the maximum number of request a user can make to the website before getting blocked.', 'booter-bots-crawlers-manager' ); ?>
            </p>
		</td>
	</tr>
	<tr valign="top">
        <th scope="row"><label for="booter-rate_limit-block_for"><?php esc_html_e( 'Block For', 'booter-bots-crawlers-manager' ); ?></label></th>
		<td>
            <radio-toggle id="booter-rate_limit-block_for"
                          name="booter_settings[rate_limit][block_for]"
                          value="<?php echo esc_attr( $block_for ); ?>"
                          options='<?php
                          // הוספת הערות מתרגמים מפורשות לכל שורה עם פלייסהולדר
                          echo esc_attr( wp_json_encode( [
                                  '300' => sprintf(
                                  /* translators: %s: number of minutes */
                                          _n( '%s Minute', '%s Minutes', 5, 'booter-bots-crawlers-manager' ), 5
                                  ),
                                  '600' => sprintf(
                                  /* translators: %s: number of minutes */
                                          _n( '%s Minute', '%s Minutes', 10, 'booter-bots-crawlers-manager' ), 10
                                  ),
                                  '900' => sprintf(
                                  /* translators: %s: number of minutes */
                                          _n( '%s Minute', '%s Minutes', 15, 'booter-bots-crawlers-manager' ), 15
                                  ),
                                  '1800' => sprintf(
                                  /* translators: %s: number of minutes */
                                          _n( '%s Minute', '%s Minutes', 30, 'booter-bots-crawlers-manager' ), 30
                                  ),
                                  '3600' => sprintf(
                                  /* translators: %s: number of hours */
                                          _n( '%s Hour', '%s Hours', 1, 'booter-bots-crawlers-manager' ), 1
                                  ),
                          ] ) );
                          ?>'
            ></radio-toggle>
            <p class="description">
				<?php esc_html_e( 'This is the time the user will be blocked for with a 429 status code after reaching the requests limit.', 'booter-bots-crawlers-manager' ); ?>
            </p>
		</td>
	</tr>

    <tr valign="top">
        <th scope="row"><label for="booter-rate_limit-exclude"><?php esc_html_e( 'Excluded User Agents', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <tags-list-single value="<?php echo esc_attr( $exclude ); ?>" id="booter-rate_limit-exclude" add-label-text="<?php esc_html_e( 'Add a string to block', 'booter-bots-crawlers-manager' ); ?>" name="booter_settings[rate_limit][exclude]"></tags-list-single>
            <p class="description">
				<?php esc_html_e( 'Any useragent in this list will not be rate limited.', 'booter-bots-crawlers-manager' ); ?>
				<?php esc_html_e( 'Make sure you add only bots you trust.', 'booter-bots-crawlers-manager' ); ?>
            </p>
        </td>
    </tr>
</table>

<?php submit_button(); ?>
