<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
class Plugin {
	private static $instance;

	/**
	 * @return Plugin
	 */
	public static function initialize() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}


	private function __construct() {
		$this->load_dependencies();
		$this->initialize_plugin();
	}

	/**
	 * Import required files
	 */
	protected function load_dependencies() {
		require_once BOOTER_DIR . '/includes/Updater.php';
		require_once BOOTER_DIR . '/includes/Utilities.php';
		require_once BOOTER_DIR . '/includes/AjaxHandlers.php';
		require_once BOOTER_DIR . '/includes/Settings.php';
		require_once BOOTER_DIR . '/includes/RequestBlocker.php';
		require_once BOOTER_DIR . '/includes/Log404.php';
		require_once BOOTER_DIR . '/includes/RateLimiter.php';
		require_once BOOTER_DIR . '/includes/RobotsWriter.php';
	}

	/**
	 * Init all the plugin parts
	 */
	protected function initialize_plugin() {
		register_activation_hook( BOOTER_FILE, [ $this, 'activation_hook' ] );
		register_deactivation_hook( BOOTER_FILE, [ $this, 'deactivation_hook' ] );

		add_action( 'init', [ $this, 'load_translation' ] );

		if ( is_admin() ) {
			// make sure we have the mu plugin installed when saving our settings
			add_action( 'update_option_' . BOOTER_SETTINGS_KEY, [ $this, 'install_mu_plugin' ] );
		}

		Settings::initialize();
		AjaxHandlers::initialize();
		RequestBlocker::initialize();
		Log404::initialize();
		RateLimiter::initialize();
		RobotsWriter::initialize();
	}

	public function load_translation() {
		load_textdomain( 'booter-bots-crawlers-manager', sprintf( '%1$s/%2$s/%2$s-%3$s.mo', WP_LANG_DIR, 'booter-bots-crawlers-manager', get_locale() ) );
		load_plugin_textdomain( 'booter-bots-crawlers-manager', false, basename( dirname( BOOTER_FILE ) ) . '/languages' );
	}

	/**
	 * Plugin activation actions
	 */
	public function activation_hook() {
		// run the plugin upgrade procedure
		Updater::upgrade();

		Settings::save_default_settings();

		$this->install_mu_plugin();

		RobotsWriter::initialize()->maybe_write_robots_file();
	}

	/**
	 * Plugin deactivation actions
	 */
	public function deactivation_hook() {
		$this->uninstall_mu_plugin();

		$settings = get_option( BOOTER_SETTINGS_KEY );

		if ( isset( $settings['uninstall'] ) && ( '1' === $settings['uninstall'] || 'yes' === $settings['uninstall'] ) ) {
			$this->do_uninstall();
		}
	}

	/**
	 * Install the MU plugin
	 */
    function install_mu_plugin() {
        $mu_dir = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
        $mu_dir = untrailingslashit( $mu_dir );
        $source = BOOTER_DIR . '/mu-plugins/booter-crawlers-manager-mu.php';
        $dest   = $mu_dir . '/booter-crawlers-manager-mu.php';

        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if ( ! WP_Filesystem() ) {
            error_log( 'Booter: Failed to initialize WP_Filesystem during MU plugin installation.' );
            return;
        }

        if ( ! $wp_filesystem->exists( $mu_dir ) ) {
            wp_mkdir_p( $mu_dir );
        }

        if ( $wp_filesystem->exists( $dest ) ) {
            $wp_filesystem->delete( $dest );
        }

        $wp_filesystem->copy( $source, $dest, true, FS_CHMOD_FILE );
    }

	/**
	 * Uninstall the MU plugin
	 */
    function uninstall_mu_plugin() {
        $mu_dir    = ( defined( 'WPMU_PLUGIN_DIR' ) && defined( 'WPMU_PLUGIN_URL' ) ) ? WPMU_PLUGIN_DIR : trailingslashit( WP_CONTENT_DIR ) . 'mu-plugins';
        $mu_dir    = trailingslashit( $mu_dir );
        $mu_plugin = $mu_dir . '/booter-crawlers-manager-mu.php';

        global $wp_filesystem;
        if ( empty( $wp_filesystem ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
        }

        if ( ! WP_Filesystem() ) {
            error_log( 'Booter: Failed to initialize WP_Filesystem during MU plugin uninstallation.' );
            return;
        }

        if ( $wp_filesystem->exists( $mu_plugin ) ) {
            $wp_filesystem->delete( $mu_plugin );
        }
    }

	/**
	 * Run the uninstall procedure
	 */
	function do_uninstall() {
		global $wpdb;

		delete_option( BOOTER_SETTINGS_KEY );
		delete_option( 'booter_version' );
		delete_transient( 'booter_disavow_list_downloaded_at' );
		$dbname = $wpdb->prefix . BOOTER_404_DB_TABLE;
		$wpdb->query( "DROP TABLE {$dbname}" );
	}
}
