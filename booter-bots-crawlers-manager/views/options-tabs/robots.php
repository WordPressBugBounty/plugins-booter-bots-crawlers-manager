<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$manage_type = isset( $settings['robots']['manage_type'] ) ? sanitize_text_field( $settings['robots']['manage_type'] ) : 'simple';
$block_all = isset( $settings['robots']['block_all'] ) ? sanitize_text_field( $settings['robots']['block_all'] ) : 'no';
$crawl_rate = isset( $settings['robots']['crawl_rate'] ) ? sanitize_text_field( $settings['robots']['crawl_rate'] ) : '0';
$sitemap_enabled = isset( $settings['robots']['sitemap_enabled'] ) ? sanitize_text_field( $settings['robots']['sitemap_enabled'] ) : 'yes';
$sitemap_url = isset( $settings['robots']['sitemap_url'] ) ? sanitize_text_field( $settings['robots']['sitemap_url'] ) : '';
$useragents = isset( $settings['robots']['useragents'] ) ? \Upress\Booter\RobotsWriter::sanitize_robots_useragent( $settings['robots']['useragents'] ) : [];
?>

<p class="notice notice-info">
    <?php esc_html_e( 'Allow Booter - Crawlers Manager to manage your robots.txt file. Existing file will be renamed as a backup, but Booter robots.txt file will be overwritten automatically when saving the settings.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'Crawlers are not required to follow these guidelines, but most legitimate crawler will.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'The crawler creator decides which rules to obey and may ignore some of them.', 'booter-bots-crawlers-manager' ); ?>
</p>

<table class="form-table">
    <tr valign="top">
        <th scope="row"><label for="booter-robots-block_all"><?php esc_html_e( 'Deny All Crawlers', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <booter-switch type="danger" id="booter-robots-block_all" name="booter_settings[robots][block_all]" value="<?php echo esc_attr( $block_all ); ?>" data-toggle-off=".js-booter-robots-setting"></booter-switch>
            <p class="description"><?php esc_html_e( 'This will disallow all URLs for all user agents, it is essentially a no-index request.', 'booter-bots-crawlers-manager' ); ?></p>
        </td>
    </tr>

    <tbody class="js-booter-robots-setting">
    <tr valign="top">
        <th scope="row"><label for="booter-robots-sitemap_enabled"><?php esc_html_e( 'Include A URL To The Sitemap', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <booter-switch id="booter-robots-sitemap_enabled" name="booter_settings[robots][sitemap_enabled]" value="<?php echo esc_attr( $sitemap_enabled ); ?>" data-toggle-on=".js-booter-robots-sitemap"></booter-switch>
            <p class="description">
                <?php esc_html_e( 'Make it easier for search engines to find your sitemap by including a link to it in the robots.txt file.', 'booter-bots-crawlers-manager' ); ?>
            </p>
        </td>
    </tr>

    <tr valign="top" class="js-booter-robots-sitemap">
        <th scope="row"><label for="booter-robots-sitemap_url"><?php esc_html_e( 'Sitemap URL', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td <?php echo is_rtl() ? 'style="direction: ltr; text-align: right;"' : ''; ?>>
            <?php echo esc_url( trailingslashit( site_url() ) ); ?>
            <input id="booter-robots-sitemap_url" type="text" class="regular-text" placeholder="sitemap.xml" name="booter_settings[robots][sitemap_url]" value="<?php echo esc_attr( $sitemap_url ); ?>" autocomplete="off">
        </td>
    </tr>

    <tr valign="top">
        <th scope="row"><label for="booter-robots-manage_type"><?php esc_html_e( 'Management Type', 'booter-bots-crawlers-manager' ); ?></label></th>
        <td>
            <radio-toggle id="booter-robots-manage_type"
                          name="booter_settings[robots][manage_type]"
                          value="<?php echo esc_attr( $manage_type ); ?>"
                          options='<?php echo esc_attr( wp_json_encode( [ 'simple' => esc_html__( 'Simple', 'booter-bots-crawlers-manager' ), 'advanced' => esc_html__( 'Advanced', 'booter-bots-crawlers-manager' ) ] ) ); ?>'
                          data-toggle-on=".js-booter-robots-advanced_manage"
                          data-on-value="advanced"
            ></radio-toggle>
            <p class="description"><?php esc_html_e( 'Simple management type will create the most common robots.txt file fit for WordPress, advanced management will allow you to manually control the crawler settings.', 'booter-bots-crawlers-manager' ); ?></p>
        </td>
    </tr>
    </tbody>
</table>


<div class="js-booter-robots-setting" style="margin-top: 40px;">
    <div class="js-booter-robots-advanced_manage">
        <h3><?php esc_html_e( 'Robot Specific Settings', 'booter-bots-crawlers-manager' ); ?></h3>
        <p><?php esc_html_e( 'When defining settings for a specific crawler it will ignore the settings for all crawlers (*), all settings will have to be defined again for that crawler.', 'booter-bots-crawlers-manager' ); ?></p>
        <robots-manager name="booter_settings[robots][useragents]" value='<?php echo esc_attr( wp_json_encode( $useragents ) ); ?>'></robots-manager>

        <datalist id="known-robots">
            <option value="*"><?php esc_html_e( 'All Crawlers', 'booter-bots-crawlers-manager' ); ?></option>
            <?php
            $known_robots = \Upress\Booter\Utilities::get_known_bots();
            foreach( $known_robots as $robot ) :
                if ( preg_match ('/\s/', $robot ) ) {
                    continue;
                }
                ?>
                <option><?php echo esc_html( $robot ); ?></option>
            <?php endforeach; ?>
        </datalist>
    </div>
</div>

<div class="submit">
    <small class="submit-note" style="text-align: <?php echo esc_attr( is_rtl() ? 'left' : 'right' ); ?>">
        <?php esc_html_e( 'Saving changes will overwrite the robots.txt file, if there is an existing file (not generated by Booter) it will be renamed to robots.txt.old.', 'booter-bots-crawlers-manager' ); ?><br>
        <a href="<?php echo esc_url( site_url( '/robots.txt' ) ); ?>" target="_blank" rel="nofollow noopener">
            <?php esc_html_e( 'View robots.txt file', 'booter-bots-crawlers-manager' ); ?>
        </a>
    </small>
    <?php submit_button( null, 'primary', 'submit', false ); ?>
</div>
