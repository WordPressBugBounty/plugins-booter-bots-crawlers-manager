<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$lang = explode( '-', get_bloginfo( 'language' ) )[0];
$downloaded_at = get_transient( 'booter_disavow_list_downloaded_at' );
?>

<p>
    <?php esc_html_e( 'In most cases, search engines can evaluate which links to your site are trusted based on the linking website\'s reputation.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'But sometimes you would like to make search engines disregard links from specific websites that have a high number of spammy links, artificial links, or low quality links pointing to your website.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'Disavowing links allows you to do that in case you are currently experiencing such an attack or want to defend yourself in advance.', 'booter-bots-crawlers-manager' ); ?><br>
    <?php esc_html_e( 'To do this, follow these steps:', 'booter-bots-crawlers-manager' ); ?>
</p>
<ol>
   <li><?php esc_html_e( 'Download the provided list file', 'booter-bots-crawlers-manager' ); ?></li>
   <li>
       <?php esc_html_e( 'Go to the appropriate disavow links page:', 'booter-bots-crawlers-manager' ); ?>
       <a href="https://www.google.com/webmasters/tools/disavow-links?hl=<?php echo esc_attr( urlencode( $lang ) ); ?>&siteUrl=<?php echo esc_url( site_url() ); ?>" target="_blank" rel="nofollow noopener">
		   <?php esc_html_e( 'Google Search Console', 'booter-bots-crawlers-manager' ); ?>
       </a>,
       <a href="https://www.bing.com/webmaster/help/how-to-disavow-links-0c56a26f#<?php echo urlencode( $lang ); ?>" target="_blank" rel="nofollow noopener">
		   <?php esc_html_e( 'Bing Webmaster Tools', 'booter-bots-crawlers-manager' ); ?>
       </a>
   </li>
    <li><?php esc_html_e( 'Select your website', 'booter-bots-crawlers-manager' ); ?></li>
    <li><?php esc_html_e( 'Click Deny Links', 'booter-bots-crawlers-manager' ); ?></li>
    <li><?php esc_html_e( 'Select the file you downloaded and submit it', 'booter-bots-crawlers-manager' ); ?></li>
</ol>
<p>
    <?php esc_html_e( '*It may take several weeks for search engines to process the information you\'ve uploaded.', 'booter-bots-crawlers-manager' ); ?>
</p>

<div>
    <a href="<?php echo esc_url( admin_url( 'admin-ajax.php' ) . '?action=booter_download_disavow_list&_wpnonce=' . wp_create_nonce( 'download-disavow' ) ); ?>" target="_blank" class="button button-info" style="vertical-align: middle;">
        <span class="dashicons dashicons-download" aria-hidden="true"></span>
		<?php esc_html_e( 'Download Disavow List', 'booter-bots-crawlers-manager' ); ?>
    </a>

    <span class="badge" style="margin: 0 4px; <?php echo esc_attr( false === $downloaded_at ? '' : 'background-color: #4AAE9B; color: #fff;' ); ?>">
        <?php if ( false === $downloaded_at ) : ?>
            <?php esc_html_e( 'The File was not yet downloaded', 'booter-bots-crawlers-manager' ); ?>
        <?php else : ?>
            <?php printf(
            /* translators: %s: formatted date and time of download */
                    esc_html__( 'The file was downloaded at %s', 'booter-bots-crawlers-manager' ),
                    esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $downloaded_at ) )
            ); ?>
        <?php endif; ?>
    </span>
</div>
