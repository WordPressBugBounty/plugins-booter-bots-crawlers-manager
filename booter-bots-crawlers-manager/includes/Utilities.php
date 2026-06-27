<?php
namespace Upress\Booter;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
class Utilities {
    /**
     * @var string[]
     */
    protected static $bad_robots;
    /**
     * @var string[]
     */
    protected static $bad_referers;

    /**
     * Explode a string by new lines
     *
     * @param $string
     *
     * @return string[]
     */
    public static function explode_new_lines( $string ) {
        return preg_split( "/\\r\\n|\\r|\\n/u", trim( $string ), - 1, PREG_SPLIT_NO_EMPTY );
    }

    /**
     * Get the user's IP address
     * @return array
     */
    public static function get_client_ip() {
        $remote_addr = ! empty( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

        if ( ! filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
            return [];
        }

        $client_ip = $remote_addr;

        $trusted_proxy_ips = apply_filters( 'booter_trusted_proxy_ips', [] );
        if ( is_array( $trusted_proxy_ips ) && in_array( $remote_addr, $trusted_proxy_ips, true ) ) {
            $headers = [
                'HTTP_CF_CONNECTING_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP',
            ];

            foreach ( $headers as $header ) {
                if ( empty( $_SERVER[ $header ] ) ) {
                    continue;
                }

                $header_value = sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) );
                $parts        = array_map( 'trim', explode( ',', $header_value ) );
                $candidate    = reset( $parts );

                if ( filter_var( $candidate, FILTER_VALIDATE_IP ) ) {
                    $client_ip = $candidate;
                    break;
                }
            }
        }

        return [ $client_ip ];
    }

    /**
     * Get the WordPress filesystem instance.
     *
     * @return object|false
     */
    public static function get_filesystem() {
        global $wp_filesystem;

        if ( ! function_exists( 'WP_Filesystem' ) || ! function_exists( 'get_filesystem_method' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if ( function_exists( 'get_filesystem_method' ) && 'direct' !== get_filesystem_method( [], WP_CONTENT_DIR, true ) ) {
            return false;
        }

        if ( ! WP_Filesystem() ) {
            return false;
        }

        if ( ! is_object( $wp_filesystem ) || ! method_exists( $wp_filesystem, 'put_contents' ) ) {
            return false;
        }

        return $wp_filesystem;
    }

    /**
     * Check if current user is logged in via cookie
     * @return bool
     */
    public static function is_user_logged_in() {
        // הדרך התקינה - אם הפונקציה כבר נטענה, נשתמש בה
        if ( function_exists( 'is_user_logged_in' ) ) {
            return is_user_logged_in();
        }

        if ( ! defined( 'LOGGED_IN_COOKIE' ) && function_exists( 'wp_cookie_constants' ) ) {
            wp_cookie_constants();
        }

        if ( ! function_exists( 'wp_validate_auth_cookie' ) && defined( 'ABSPATH' ) && defined( 'WPINC' ) ) {
            require_once ABSPATH . WPINC . '/pluggable.php';
        }

        if ( function_exists( 'wp_validate_auth_cookie' ) ) {
            return false !== wp_validate_auth_cookie( '', 'logged_in' );
        }

        return false;
    }

    /**
     * Get the user's fingerprint by useragent + ip
     * @return string
     */
    public static function generate_user_fingerprint_string() {
        $ips = self::get_client_ip();
        sort( $ips );
        $ips = implode( ';', $ips );

        $ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
        return "{$ips};{$ua}";
    }

    /**
     * Get array of known bot useragent strings
     * @return string[]
     */
    public static function get_known_bots() {
        return apply_filters( 'booter_known_bots',
            array_merge(
                self::get_bad_robots(),
                [
                    'Googlebot',
                    'Google',
                    'YandexBot',
                    'Yandex',
                    'bingbot',
                    'BLEXBot',
                    'BlackWidow',
                    'Nutch',
                    'Jetbot',
                    'WebVac',
                    'Stanford',
                    'scooter',
                    'naver',
                    'dumbot',
                    'Hatena Antenna',
                    'grub',
                    'looksmart',
                    'WebZip',
                    'larbin',
                    'b2w/0.1',
                    'Copernic',
                    'psbot',
                    'Python-urllib',
                    'NetMechanic',
                    'URL_Spider_Pro',
                    'CherryPicker',
                    'EmailCollector',
                    'EmailSiphon',
                    'WebBandit',
                    'EmailWolf',
                    'ExtractorPro',
                    'CopyRightCheck',
                    'Crescent',
                    'SiteSnagger',
                    'ProWebWalker',
                    'LNSpiderguy',
                    'Alexibot',
                    'Teleport',
                    'MIIxpc',
                    'Telesoft',
                    'Website Quester',
                    'moget',
                    'WebStripper',
                    'WebSauger',
                    'WebCopier',
                    'NetAnts',
                    'Mister PiX',
                    'WebAuto',
                    'TheNomad',
                    'WWW-Collector-E',
                    'libWeb/clsHTTP',
                    'asterias',
                    'httplib',
                    'turingos',
                    'spanner',
                    'Harvest',
                    'InfoNaviRobot',
                    'Bullseye',
                    'WebBandit',
                    'NICErsPRO',
                    'Microsoft URL Control',
                    'DittoSpyder',
                    'Foobot',
                    'WebmasterWorldForumBot',
                    'SpankBot',
                    'BotALot',
                    'lwp-trivial',
                    'WebmasterWorld',
                    'BunnySlippers',
                    'URLy Warning',
                    'LinkWalker',
                    'cosmos',
                    'hloader',
                    'humanlinks',
                    'LinkextractorPro',
                    'Offline Explorer',
                    'Mata Hari',
                    'LexiBot',
                    'Collector',
                    'The Intraformant',
                    'True_Robot',
                    'BlowFish',
                    'SearchEngineWorld',
                    'JennyBot',
                    'MIIxpc',
                    'BuiltBotTough',
                    'ProPowerBot',
                    'BackDoorBot',
                    'toCrawl/UrlDispatcher',
                    'WebEnhancer',
                    'suzuran',
                    'WebViewer',
                    'VCI',
                    'Szukacz',
                    'QueryN',
                    'Openfind',
                    'Openbot',
                    'Webster',
                    'EroCrawler',
                    'LinkScan',
                    'Keyword',
                    'Kenjin',
                    'Iron33',
                    'Bookmark search tool',
                    'GetRight',
                    'FairAd Client',
                    'Gaisbot',
                    'Aqua_Products',
                    'Radiation Retriever 1.1',
                    'Flaming AttackBot',
                    'Oracle Ultra Search',
                    'MSIECrawler',
                    'PerMan',
                    'searchpreview',
                    'sootle',
                    'Enterprise_Search',
                    'ChinaClaw',
                    'Custo',
                    'DISCo',
                    'Download Demon',
                    'eCatch',
                    'EirGrabber',
                    'EmailSiphon',
                    'EmailWolf',
                    'Express WebPictures',
                    'ExtractorPro',
                    'EyeNetIE',
                    'FlashGet',
                    'GetRight',
                    'GetWeb!',
                    'Go!Zilla',
                    'Go-Ahead-Got-It',
                    'GrabNet',
                    'Grafula',
                    'HMView',
                    'HTTrack',
                    'Image Stripper',
                    'Image Sucker',
                    'Indy Library',
                    'InterGET',
                    'Internet Ninja',
                    'JetCar',
                    'JOC',
                    'Spider',
                    'larbin',
                    'LeechFTP',
                    'Mass Downloader',
                    'MIDown tool',
                    'Mister PiX',
                    'Navroad',
                    'NearSite',
                    'NetAnts',
                    'NetSpider',
                    'Net Vampire',
                    'NetZIP',
                    'Octopus',
                    'Offline Explorer',
                    'Offline Navigator',
                    'PageGrabber',
                    'Papa Foto',
                    'pavuk',
                    'pcBrowser',
                    'RealDownload',
                    'ReGet',
                    'SiteSnagger',
                    'SmartDownload',
                    'SuperBot',
                    'SuperHTTP',
                    'Surfbot',
                    'tAkeOut',
                    'Teleport Pro',
                    'VoidEYE',
                    'Collector',
                    'Sucker',
                    'WebAuto',
                    'WebCopier',
                    'WebFetch',
                    'WebGo IS',
                    'WebLeacher',
                    'WebReaper',
                    'WebSauger',
                    'Website eXtractor',
                    'Website Quester',
                    'WebStripper',
                    'WebWhacker',
                    'WebZIP',
                    'Widow',
                    'WWWOFFLE',
                    'Xaldon WebSpider',
                    'Zeus',
                    'Semrush',
                    'BecomeBot',
                    'Screaming Frog SEO Spider',
                    'GrapeshotCrawler',
                    'trendkite-akashic-crawler',
                    'GetIntent Crawler',
                    'special_archiver',
                    'SirdataBot',
                    'bidswitchbot',
                    'proximic',
                    'NetSeer',
                    'crawler',
                    'rogerbot',
                    'exabot',
                    'Xenu',
                    'gigabot',
                    'BlekkoBot',
                    'AhrefsBot',
                    'omgili',
                    'Slurp',
                    'ia_archiver',
                    'agent1',
                    'Cheesebot',
                    'Catall Spider',
                    'MJ12bot',
                    'seo-audit-check-bot',
                    'webceo',
                    'dotbot',
                    'WP Rocket',
                ]
            )
        );
    }

    /**
     * Get array of bad bot useragent strings
     * @return string[]
     */
    public static function get_bad_robots() {
        if ( ! self::$bad_robots ) {
            $additional_robots = self::get_list_from_local_file( 'bad-user-agents-extra.list' );
            $excluded_robots   = self::get_list_from_local_file( 'bad-user-agents-whitelist.list' );
            $bad_robots        = self::get_list_from_local_file( 'bad-user-agents.list' );

            $bad_robots = array_merge( $bad_robots, $additional_robots );
            $bad_robots = array_diff( $bad_robots, $excluded_robots );

            self::$bad_robots = apply_filters( 'booter_bad_bots', array_values( array_unique( $bad_robots ) ) );
        }

        return self::$bad_robots;
    }

    /**
     * Get array of bad referer domains
     * @return string[]
     */
    public static function get_bad_referers() {
        if ( ! self::$bad_referers ) {
            self::$bad_referers = self::get_list_from_local_file( 'bad-referrers.list' );
        }

        return self::$bad_referers;
    }

    protected static function get_list_from_local_file( $filename ) {
        $filename = sanitize_file_name( $filename );
        $path     = trailingslashit( BOOTER_DIR ) . 'assets/lists/' . $filename;

        $filesystem = self::get_filesystem();

        if ( ! $filesystem || ! $filesystem->exists( $path ) ) {
            return [];
        }

        $contents = $filesystem->get_contents( $path );

        if ( ! is_string( $contents ) || '' === trim( $contents ) ) {
            return [];
        }

        $lines = preg_split( '/\r\n|\r|\n/', $contents );

        if ( ! is_array( $lines ) ) {
            return [];
        }

        $list = [];

        foreach ( $lines as $line ) {
            $line = trim( $line );

            if ( '' === $line || '#' === substr( $line, 0, 1 ) ) {
                continue;
            }

            $list[] = $line;
        }

        return array_values( array_unique( $list ) );
    }

    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function array_get( $array, $key, $default = null ) {
        if ( is_null( $key ) ) {
            return $array;
        }

        if ( isset( $array[ $key ] ) ) {
            return $array[ $key ];
        }

        foreach ( explode( '.', $key ) as $segment ) {
            if ( ! is_array( $array ) || ! array_key_exists( $segment, $array ) ) {
                return $default;
            }

            $array = $array[ $segment ];
        }

        return $array;
    }

    public static function bool_value( $value ) {
        return true === $value || 1 === $value || '1' === $value || 'yes' === $value || 'true' === $value;
    }

    public static function sanitize_int( $value ) {
        return (string) intval( $value );
    }

    public static function sanitize_bool( $value ) {
        return filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ? 'yes' : 'no';
    }

    /**
     * Check if the current request is running through the CLI or WP-CLI
     * @return bool
     */
    public static function is_running_in_cli() {
        return ! isset( $_SERVER['REQUEST_METHOD'] ) || ( defined( 'WP_CLI' ) && WP_CLI );
    }

    /**
     * Check if the request is coming from the servers IP
     * @return bool
     */
    public static function is_request_coming_from_server_ip() {
        if ( ! empty( $_SERVER['SERVER_ADDR'] ) )
            $server_ip = $_SERVER['SERVER_ADDR'];
        elseif ( ! empty( $_SERVER['LOCAL_ADDR'] ) )
            $server_ip = $_SERVER['LOCAL_ADDR'];
        else
            return false;

        $ips = self::get_client_ip();

        return in_array( $server_ip, $ips );
    }

}
