<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Log404 {

	private static $instance;

	/**
	 * @return Log404
	 */
	public static function initialize() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'booter_404_log_cleanup', [ $this, 'cron_404_log_cleanup' ] );
		add_action( 'booter_404_log_report', [ $this, 'cron_404_log_report' ] );
		add_filter( 'cron_schedules', [ $this, 'add_cron_schedules' ] );
		add_action( 'template_redirect', [ $this, 'maybe_log_404' ] );
		add_action( 'init', [ $this, 'schedule_cronjobs' ], 100 );

		register_deactivation_hook( BOOTER_FILE, [ $this, 'deactivation_hook' ] );
	}

	function add_cron_schedules( $schedules ) {
		if ( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = [
				'interval' => 7 * DAY_IN_SECONDS,
                /* translators: %s: interval name */
				'display' => __( 'Weekly', 'booter-bots-crawlers-manager' )
			];
		}

		if ( ! isset( $schedules['monthly'] ) ) {
			$schedules['monthly'] = [
				'interval' => 30 * DAY_IN_SECONDS,
                /* translators: %s: interval name */
				'display' => __( 'Weekly', 'booter-bots-crawlers-manager' )
			];
		}

		return $schedules;
	}

	function schedule_cronjobs( $new_settings = null ) {
		if ( ! wp_next_scheduled( 'booter_404_log_cleanup' ) ) {
			wp_schedule_event( time(), 'hourly', 'booter_404_log_cleanup' );
		}

		if ( wp_next_scheduled( 'booter_404_log_daily_report' ) ) {
			wp_clear_scheduled_hook( 'booter_404_log_daily_report' );
		}

		if ( ! wp_next_scheduled( 'booter_404_log_report' ) ) {
			$settings = $new_settings ?: get_option( 'booter_settings' );

			if ( ! Utilities::bool_value( $settings['log_404']['enabled'] ) || ! in_array( $settings['log_404']['send_report'], [ 'yes', 'daily', 'weekly', 'monthly' ] ) ) {
				return;
			}

			$interval = 'day';
			if ( 'weekly' == $settings['log_404']['send_report'] ) {
				$interval = 'week';
			} elseif ( 'monthly' == $settings['log_404']['send_report'] ) {
				$interval = 'month';
			}

			wp_schedule_event( strtotime("+1 {$interval} 9:00 am"), $settings['log_404']['send_report'], 'booter_404_log_report' );
		}
	}

	/**
	 * Destroy database and cancel cronjob
	 */
	function deactivation_hook() {
		wp_clear_scheduled_hook( 'booter_404_log_cleanup' );
		wp_clear_scheduled_hook( 'booter_404_log_report' );
	}


	/**
	 * Do the logging
	 */
	public function maybe_log_404() {
		if ( ! is_404() ) {
			return;
		}

		$settings = get_option( 'booter_settings' );
		if ( ! Utilities::bool_value( $settings['log_404']['enabled'] ) ) {
			return;
		}

		self::log();
	}

	/**
	 * Get the latest 100 log items
	 * @param int $limit Limit the maximum results returned
	 * @return array|object|null
	 */
	public static function get_logs( $limit=100 ) {
		global $wpdb;

		$dbname = $wpdb->prefix . BOOTER_404_DB_TABLE;

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$dbname} ORDER BY updated_at DESC LIMIT %d", [ $limit ] ) );
	}

	/**
	 * Log a 404 to the database
	 * @return bool|int
	 */
	public static function log() {
		global $wpdb;

		$url        = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_url( $_SERVER['REQUEST_URI'] ) : '';
		$updated_at = current_time( 'mysql', true );

		$dbname = $wpdb->prefix . BOOTER_404_DB_TABLE;

        return $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$dbname} (uid, url, hits, created_at, updated_at) VALUES (%s, %s, 1, %s, %s) ON DUPLICATE KEY UPDATE hits = hits + 1, updated_at = %s",
                [ sha1( $url ), $url, $updated_at, $updated_at, $updated_at ]
            )
        );
	}

	/**
	 * CRON: cleanup 404 logs older than 2 months
	 */
	public function cron_404_log_cleanup() {
		global $wpdb;

		$dbname = $wpdb->prefix . BOOTER_404_DB_TABLE;
		$wpdb->query( "DELETE FROM {$dbname} WHERE updated_at <= (NOW() - INTERVAL 2 MONTH)" );
	}


    /**
     * CRON: send daily 404 report
     */
    public function cron_404_log_report() {
        global $wpdb;

        $settings = get_option( 'booter_settings' );
        $settings = $settings['log_404'];

        // backwards compatibility
        if ( 'yes' == $settings['send_report'] ) {
            $settings['send_report'] = 'daily';
        }

        if ( 'no' == $settings['send_report'] || ! in_array( $settings['send_report'], [ 'daily', 'weekly', 'monthly' ], true ) ) {
            return;
        }

        $email = sanitize_email( ! empty( $settings['report_email'] ) ? $settings['report_email'] : get_option( 'admin_email' ) );
        $dbname = $wpdb->prefix . BOOTER_404_DB_TABLE;
        $homepage = site_url();
        $site_name = get_bloginfo( 'name' );
        if ( empty( $site_name ) ) {
            $site_name = str_replace( [ 'https://', 'http://' ], '', $homepage );
        }

        $interval = 'DAY';
        if ( 'weekly' == $settings['send_report'] ) {
            $interval = 'WEEK';
        } elseif ( 'monthly' == $settings['send_report'] ) {
            $interval = 'MONTH';
        }

        $safe_interval = esc_sql( $interval );
        $safe_dbname   = esc_sql( $dbname );

        // get the URLs from the last 24 hours (or week/month)
        $query_total = sprintf(
            "SELECT COUNT(*) FROM %s WHERE created_at BETWEEN DATE_SUB( NOW(), INTERVAL 1 %s ) AND NOW()",
            $safe_dbname,
            $safe_interval
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $total = $wpdb->get_var( $query_total );

        $query_results = sprintf(
            "SELECT * FROM %s WHERE created_at BETWEEN DATE_SUB( NOW(), INTERVAL 1 %s ) AND NOW() ORDER BY hits DESC LIMIT 10",
            $safe_dbname,
            $safe_interval
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results( $query_results );

        if ( ! $results || count( $results ) <= 0 ) {
            return;
        }

        $html = '<html dir="' . (is_rtl() ? 'rtl' : 'ltr') . '"><body>';
        $html .= '<p>';
        $html .= esc_html_x( 'Hi,', '404 log report email', 'booter-bots-crawlers-manager' );
        $html .= '<br>';
        /* translators: %s: homepage URL */
        $html .= esc_html( sprintf( _x( 'This is a daily 404 errors report for the %s website.', '404 log report email', 'booter-bots-crawlers-manager' ), $homepage ) );
        $html .= '<br>';
        /* translators: 1: total pages, 2: number of top results */
        $html .= esc_html( sprintf( _x( 'Today, a total of %1$s pages were identified with a 404 error. This is the list of the %2$s most viewed pages:', '404 log report email', 'booter-bots-crawlers-manager' ), $total, 10 ) );
        $html .= '</p>';
        $html .= '<ol>';
        foreach( $results as $result ) {
            /* translators: %s: number of hits */
            $html .= '<li><code>' . esc_url( $homepage . $result->url ) . '</code> - <bdi><strong>' . esc_html( sprintf( _n( '%s hit', '%s hits', $result->hits, 'booter-bots-crawlers-manager' ), intval( $result->hits ) ) ) . '</strong></bdi></li>';
        }
        $html .= '</ol>';
        $html .= '<p>' . esc_html_x( 'If these links are unused, go to WordPress dashboard -> Settings -> Booter - Crawlers Manager, and block the links with a 410 status code, and/or block them via robots.txt file, to make search engines not index these pages.', '404 log report email', 'booter-bots-crawlers-manager' ) . '</p>';
        $html .= '</body></html>';

        wp_mail(
            $email,
            "[{$site_name}] " . esc_html_x( 'Daily 404 Log Report', '404 log report email subject', 'booter-bots-crawlers-manager' ),
            $html,
            ['Content-Type: text/html; charset=UTF-8']
        );
    }
}
