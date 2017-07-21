<?php
    /*
    Plugin Name: WPuppy
    Plugin URI: http://www.wpuppy.com/
    Description: This is the plugin used by WPuppy web Application to communicate to Wordpress.
    Version: 1.2.2.2
    Author: WPuppy
    Author URI: http://www.wpuppy.com/
    */

    if (!defined('ABSPATH'))
        die ('No script kiddies plis');

    require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );

    class WPuppy
    {
        private $errors, 
                $installed, 
                $key, 
                $pluginDir, 
                $adminDir;

        public function __construct()
        {
            $this->init();
        }

        private function init()
        {
            add_action('admin_menu', array($this, 'generate_menus'));
            add_action('admin_init', array($this, 'frame_header'));

            add_filter( 'auto_core_update_send_email', 'wpb_stop_auto_update_emails', 10, 4 );

            if (($this->installed = get_option('wpuppy_setup')) === false) {
                $this->installed = "false";
                add_option("wpuppy_setup", "false");
            }

            if ($this->installed === '')
                update_option("wpuppy_setup", "false");

            $this->key = get_option('wpuppy_key');

            $this->pluginDir = str_replace("//", "/", ABSPATH . substr(plugins_url(), strlen(home_url())) . "/");
            $this->adminDir = ABSPATH . "wp-admin/";

            add_action('init', function() {
                $site_url = str_replace("http://", "", str_replace("https://", "", str_replace("www.", "", site_url())));

                if (substr($site_url, -1) !== "/")
                    $site_url .= "/";

                $url = trim(str_replace($site_url, "", str_replace("www.", "",$_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI']));
                $url = explode("/", $url);

                if ( $url[0] === "wpuppy" && $url[1] === "api" ) {
                    @ini_set('memory_limit', '128M');
                    @set_time_limit(0);

                    // load the file if exists
                    include_once(__DIR__ . "/wpuppy-api.php");
                    exit;
                }
                if ( $url[0] === "wpuppy" && $url[1] === "setup" ) {
                    @ini_set('memory_limit', '128M');
                    @set_time_limit(0);

                    // load the file if exists
                    include_once(__DIR__ . "/wpuppy-setup.php");
                    exit;
                }
            });
        }

        public function frame_header()
        {
            @header('X-Frame-Options: ALLOWALL');
        }

        public function generate_menus()
        {
            add_menu_page('WPuppy', 'WPuppy', 'manage_options', 'wpuppy/wpuppy-admin.php');
            add_submenu_page('wpuppy/wpuppy-admin.php', 'WPuppy Settings', 'Settings', 'manage_options', 'wpuppy/wpuppy-settings.php');
        }

        public function check_api_key($key)
        {

            if ($this->installed === "true") {
                if ($this->key === $key)
                    return true;
                else
                    return false;
            }
            else
                return false;
        }

        public function show_key()
        {
            return $this->key;
        }

        public function get_plugin_data() {
            return get_plugin_data(__FILE__);
        }

        public function get_requirements() {
            $requirements = array(
                "htaccess" => false,
                "accessible" => false,
                "phpversion" => phpversion(),
                "php" => false,
                "safemode" => ini_get('safe_mode')
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, get_site_url() . "/wpuppy/api/");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);

            //Check if we can execute the CURL and if we get output
            if (($output = curl_exec($curl)) && $output !== "") {
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

                if ($httpCode === 200)
                    $requirements['accessible'] = true;
            }

            if (version_compare($requirements['phpversion'], '5.4.0', '>='))
                $requirements['php'] = true;

            return $requirements;
        }

        /*
         * UPDATE Functions
         */

        public function update_wordpress() {
            $this->setupFSMethod();
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/update.php' );

            $upgrader = new Core_Upgrader(new Automatic_Upgrader_Skin());

            wp_version_check(array(),true);

            $current = get_site_transient( 'update_core' )->updates[0];

            $result = $upgrader->upgrade($current);

            if (!$result || is_wp_error($result)) {
                $this->unflagMaintenance();

                if ($result->get_error_message() !== __("WordPress is at the latest version."))
                    return array(
                        "type" => "error",
                        "version" => $version,
                        "message" => $result->get_error_message()
                    );
            }

            $translation_upgrader = new Language_Pack_Upgrader(new Automatic_Upgrader_Skin());
            $result = $translation_upgrader->bulk_upgrade();

            wp_localize_script( 'updates', '_wpUpdatesItemCounts', array(
                'totals'  => wp_get_update_data(),
            ) );

            if (!$result || is_wp_error($result)) {
                $this->unflagMaintenance();

                return array(
                    "type" => "error",
                    "message" => $result->get_error_message()
                );
            }

            $this->unflagMaintenance();

            return array(
                "type" => "success",
                "version" => $current->version
            );
        }

        public function wpb_stop_update_emails( $send, $type, $core_update, $result ) {
            if ( ! empty( $type ) && $type == 'success' ) {
                return false;
            }
            return true;
        }

        public function update_theme($slug) {
            $this->setupFSMethod();
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/theme.php' );

            wp_clean_themes_cache();
            wp_update_themes();

            $theme_upgrader = new Theme_Upgrader(new Automatic_Upgrader_Skin());
            $result = $theme_upgrader->upgrade($slug);

            if (!$result || is_wp_error($result)) {
                $this->unflagMaintenance();

                $errors = $theme_upgrader->skin->get_upgrade_messages();

                if ($errors[0] === __('The theme is at the latest version.'))
                    return array(
                        "type" => "success",
                        "message" => "up-to-date"
                    );
                
                return array("type" => "error", "message" => $theme_upgrader->skin->get_upgrade_messages());
            }

            $this->unflagMaintenance();
            return array("type" => "success");
        }

        public function update_plugin($slug) {
            $this->setupFSMethod();
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if (($file = $this->get_plugin_file($slug)) === false) {
                $this->unflagMaintenance();
                return array("type" => "error", "message" => "Couldn't find file for '{$slug}'");

            }

            $isActive = is_plugin_active($file);

            wp_clean_plugins_cache();
            wp_update_plugins();

            $plugin_updater = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
            $result = $plugin_updater->upgrade($file);

            if (!$result || is_wp_error($result)) {
                $this->unflagMaintenance();
                return array(
                    "type" => "success",
                    "file" => $file,
                    "message" => array($slug => $plugin_updater->skin->get_upgrade_messages())
                );
            }

            //Try to re-activate the plugin if it turned off
            if ($isActive) {
                $activate = activate_plugin($file);

                if (is_wp_error($activate)) {
                    $this->unflagMaintenance();
                    return array("type" => "success", "message" => $result->get_upgrade_messages());
                }
            }
            $this->unflagMaintenance();

            return array("type" => "success");
        }

        /*
         * GET Functions
         */

        public function get_sitemap(){
            $the_query = new WP_Query(array('post_type' => 'any', 'posts_per_page' => '-1', 'post_status' => 'publish'));
            $urls = array();
            while ($the_query->have_posts()) {
                $the_query->the_post();
                $urls[] = get_permalink();
            };

            return array("type" => "success", "data" => $urls);
        }

        public function list_plugin_updates() {
            $plugins = $this->get_plugin_list();
            $updates = array();
            foreach($plugins['data'] as $key=>$value) {
                $uptodate = $value['up-to-date'];

                if($uptodate == 'false') {
                    $updates[$key] = $value['slug'];
                }
            }

            return $updates;
        }

        public function list_theme_updates() {
            $themes = $this->get_themes_list();
            $updates = array();

            foreach($themes as $key => $theme) {
                if ($theme['version'] !== $theme['latest_version'])
                    $updates[$key] = $theme['slug'];
            }

            return $updates;
        }

        public function list_core_updates() {
            $from_api = get_site_transient( 'update_core' );

            if ( ! isset( $from_api->updates ) || ! is_array( $from_api->updates ) )
                return false;

            foreach ( $from_api->updates as $update ) {
                if ( $update->current !== $from_api->version_checked )
                    return $update;
            }
            return false;
        }

        public function get_plugin_list() {
            $pluginJSON = array();
            if (!function_exists("get_plugins"))
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            if (!function_exists("plugins_api"))
                require_once(ABSPATH . "wp-admin/includes/plugin-install.php");

            wp_clean_plugins_cache();
            wp_update_plugins();

            $active_plugins = get_option('active_plugins');
            $plugins = get_plugins();
            
            $to_update = get_site_transient( 'update_plugins' );

            $needs_update = array();
            $updated = array();

            if (!empty($to_update)) {
                if (!empty($to_update->response))
                    foreach($to_update->response as $key => $value){
                        array_push($needs_update, $key);
                    }
                if (!empty($to_update->no_update))
                    foreach($to_update->no_update as $key => $value){
                        array_push($updated, $key);
                    }
            }

            if (!empty($active_plugins))
                foreach($active_plugins as $plugin){
                    $plugins[$plugin]['activated'] = 'true';
                }

            foreach($plugins as $key => $plugin) {
                if(!isset($plugin['activated']))
                    $plugin['activated'] = 'false';

                if (strpos($key, "/") !== false) {
                    $string = explode("/", $key);
                    $slug = $string[0];
                } else {
                    $slug = basename(
                        $key,
                        ".php"
                    );
                }

                if(in_array($key, $updated)) {
                    $plugin['updated'] = 'true';
                    $plugin['latest_version'] = $to_update->no_update[$key]->new_version;
                } elseif (in_array($key, $needs_update)){
                    $plugin['updated'] = 'false';
                    $plugin['latest_version'] = $to_update->response[$key]->new_version;
                } else {
                    $r = plugins_api("plugin_information", array(
                        "slug" => $slug
                    ));
                    if (is_wp_error($r)) {
                        $plugin['updated'] = "Can't update this plugin because it can not be found in the Plugin API";
                        $plugin['latest_version'] = $plugin['Version'];
                    } else {
                        $plugin['latest_version'] = $r->new_version;
                    }
                    
                }

                array_push($pluginJSON, 
                           array(
                               "name" => $plugin['Name'],
                               "slug" => $slug,
                               "activated" =>  $plugin['activated'],
                               "up-to-date" => $plugin['updated'],
                               "latest_version" => $plugin['latest_version'],
                               "version" => $plugin['Version']
                           )
                ); 
            }

            $urlJSON = array("type" => "success", "data" => $pluginJSON);

            return $urlJSON;
        }

        public function get_themes_list() {
            require_once(ABSPATH . "/wp-admin/includes/update.php");
            $output = array();

            wp_clean_themes_cache();
            wp_update_themes();

            $themes = wp_get_themes();
            $updates = get_theme_updates();
            $current_theme = wp_get_theme();
            $current_theme = $current_theme->get("Name");

            foreach($themes as $theme) {
                array_push($output, array(
                    'name' => $theme->get("Name"),
                    "slug" => $theme['Template'],
                    'activated' => (($current_theme === $theme->get("Name")) ? true : false),
                    'version' => $theme->get("Version"),
                    'latest_version' => (
                    (isset($updates[$theme['Template']])) ?
                        $updates[$theme['Template']]->update['new_version'] :
                        $theme->get("Version")
                    )
                ));
            }

            return array("type" => "success", "themes" => $output);
        }

        /*
         * PUT Functions
         */
        public function backup_database() {
            global $wpdb, $wp_filesystem;

            try {
                require_once(dirname(__FILE__) . "/lib/database/mysqldump.php");

                $host = $wpdb->dbhost;
                $port = "";

                if (strpos($host, ":") !== false) {
                    $host = explode(":", $host);
                    $port = "port={$host[1]};";
                    $host = $host[0];
                }

                if(!$this->setupFilesystemAPI())
                    throw new \Exception("Failed to setup filesystem api: " . print_r($wp_filesystem->errors, true));
                
                $dump = new Ifsnop\Mysqldump\Mysqldump("mysql:host={$host};{$port}dbname={$wpdb->dbname}",
                    $wpdb->dbuser,
                    $wpdb->dbpassword,
                    array(
                        "add-drop-table" => true,
                        "compress" => "Wordpress"
                    ));
                $dump->start("backup.sql");
            } catch(\Exception $e) {
                return array("type" => "error", "message" => $e->getMessage());
            }

            return array("type" => "success");

        }

        public function backup_database_clean() {
            global $wp_filesystem;

            if(!$this->setupFilesystemAPI())
                return array("type" => "error", "message" => "failed to setup filesystem api");
            
            $folder = $wp_filesystem->abspath();

            if (!$wp_filesystem->exists($folder . "/backup.sql"))
                return array("type" => "success", "message" => "backup.sql didn't exist");

            if (!$wp_filesystem->delete($folder . "/backup.sql"))
                return array("type" => "success", "message" => "Couldn't remove backup.sql, please remove manually");

            return array("type" => "success");
        }

        public function restore_database() {
            global $wpdb, $wp_filesystem;

            if(!$this->setupFilesystemAPI())
                return array("type" => "error", "message" => "failed to setup filesystem api");
            
            $folder = $wp_filesystem->abspath();
            $backup = $wp_filesystem->get_contents($folder . "/backup.sql");

            $host = $wpdb->dbhost;

            if (strpos($host, ":") !== false) {
                $host = explode(":", $host);
                $port = $host[1];
                $host = $host[0];
                $mysqli = new mysqli($host, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname, $port);
            } else {
                $mysqli = new mysqli($host, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname);
            }

            if ($mysqli->connect_error) {
                return array(
                    "type" => "error",
                    "message" => "({$mysqli->connect_errno}) {$mysqli->connect_error}"
                );
            }

            if (!$mysqli->multi_query($backup) || ( $mysqli->errno )) {
                return array("type" => "success", "message" => $mysqli->error);
            }

            return $this->backup_database_clean();
        }

        /*
         * Private functions
         */

        private function testFilesystem() {
            global $wp_filesystem;

            if(!$wp_filesystem->put_contents($wp_filesystem->abspath() . "/test.txt", "test", FS_CHMOD_FILE))
                return false;

            $wp_filesystem->delete($wp_filesystem->abspath() . "/test.txt");
            return true;
        }

        private function setupFilesystemAPI() {
            global $wp_filesystem;

            $this->setupFSMethod();
            require_once(ABSPATH . 'wp-admin/includes/file.php');

            if(!WP_Filesystem(
                array(
                    "hostname" => FTP_HOST, "username" => FTP_USER, "password" => FTP_PASS
                )
            )) {
                if(!defined("FTP_USER_ALT"))
                    return false;

                if(!WP_Filesystem(
                    array(
                        "hostname" => FTP_HOST_ALT, "username" => FTP_USER_ALT, "password" => FTP_PASS_ALT
                    )
                ))
                    return false;
            }

            if(!$this->testFilesystem()) {
                $method = "ftpext";

                if ( ! class_exists( "WP_Filesystem_$method" ) ) {
                    /**
                     * Filters the path for a specific filesystem method class file.
                     *
                     * @since 2.6.0
                     *
                     * @see get_filesystem_method()
                     *
                     * @param string $path   Path to the specific filesystem method class file.
                     * @param string $method The filesystem method to use.
                     */
                    $abstraction_file = apply_filters( 'filesystem_method_file', ABSPATH . 'wp-admin/includes/class-wp-filesystem-' . $method . '.php', $method );
            
                    if ( ! file_exists($abstraction_file) )
                        return false;
            
                    require_once($abstraction_file);
                }
                $method = "WP_Filesystem_$method";
            
                $wp_filesystem = new $method(
                    array(
                        "hostname" => FTP_HOST, "username" => FTP_USER, "password" => FTP_PASS
                    )
                );
                if ( !$wp_filesystem->connect() ) {
                    if(!defined("FTP_HOST_ALT"))
                        return false;
                    
                    $wp_filesystem = new $method(
                        array(
                            "hostname" => FTP_HOST_ALT, "username" => FTP_USER_ALT, "password" => FTP_PASS_ALT
                        )
                    );
                    if ( !$wp_filesystem->connect() )
                        return false;

                    if(!$this->testFilesystem())
                        return false;
                } else if(!$this->testFilesystem()) {
                    return false;
                }
            }

            return true;
        }

        private function get_plugin_file($slug) {
            if (!function_exists("get_plugins"))
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            wp_clean_plugins_cache();
            wp_update_plugins();

            $plugins = get_plugins();
            $test = array();

            foreach($plugins as $file => $plugin) {
                if (strpos($file, "/") !== false) {
                    $string = explode("/", $file);
                    $plugin_slug = $string[0];
                } else {
                    $plugin_slug = basename(
                        $file,
                        ".php"
                    );
                }

                array_push($test, $plugin_slug);

                if ($plugin_slug === $slug)
                    return $file;
            }

            return false;
        }

        private function setupFSMethod() {
            //Include the template file for submit_button etc
            require_once(ABSPATH . "/wp-admin/includes/template.php");
            $input = INPUT_POST;

            if (!defined("FS_METHOD"))
                return true;
            
            if (strtolower(FS_METHOD) === "direct")
                return true;

            if (defined("FTP_USER") && defined("FTP_PASS") && defined("FTP_HOST"))
                return true;

            if (filter_input(INPUT_POST, "username") === NULL) {
                if (filter_input(INPUT_GET, "username") === NULL) {
                    echo json_encode(
                        array(
                            "type" => "error",
                            "message" => "FTP Details required"
                        )
                    );
                    exit;
                }

                $input = INPUT_GET;
            }

            if (!defined("FTP_HOST"))
                define("FTP_HOST", filter_input($input, "hostname"));

            if (!defined("FTP_USER"))
                define("FTP_USER", filter_input($input, "username"));

            if (!defined("FTP_PASS"))
                define("FTP_PASS", filter_input($input, "password"));
        }

        private function ZipStatusString( $status )
        {
            switch( (int) $status )
            {
                case ZipArchive::ER_OK           : return 'N No error';
                case ZipArchive::ER_MULTIDISK    : return 'N Multi-disk zip archives not supported';
                case ZipArchive::ER_RENAME       : return 'S Renaming temporary file failed';
                case ZipArchive::ER_CLOSE        : return 'S Closing zip archive failed';
                case ZipArchive::ER_SEEK         : return 'S Seek error';
                case ZipArchive::ER_READ         : return 'S Read error';
                case ZipArchive::ER_WRITE        : return 'S Write error';
                case ZipArchive::ER_CRC          : return 'N CRC error';
                case ZipArchive::ER_ZIPCLOSED    : return 'N Containing zip archive was closed';
                case ZipArchive::ER_NOENT        : return 'N No such file';
                case ZipArchive::ER_EXISTS       : return 'N File already exists';
                case ZipArchive::ER_OPEN         : return 'S Can\'t open file';
                case ZipArchive::ER_TMPOPEN      : return 'S Failure to create temporary file';
                case ZipArchive::ER_ZLIB         : return 'Z Zlib error';
                case ZipArchive::ER_MEMORY       : return 'N Malloc failure';
                case ZipArchive::ER_CHANGED      : return 'N Entry has been changed';
                case ZipArchive::ER_COMPNOTSUPP  : return 'N Compression method not supported';
                case ZipArchive::ER_EOF          : return 'N Premature EOF';
                case ZipArchive::ER_INVAL        : return 'N Invalid argument';
                case ZipArchive::ER_NOZIP        : return 'N Not a zip archive';
                case ZipArchive::ER_INTERNAL     : return 'N Internal error';
                case ZipArchive::ER_INCONS       : return 'N Zip archive inconsistent';
                case ZipArchive::ER_REMOVE       : return 'S Can\'t remove file';
                case ZipArchive::ER_DELETED      : return 'N Entry has been deleted';

                default: return sprintf('Unknown status %s', $status );
            }
        }

        private function unflagMaintenance() {
            global $wp_filesystem;
            if(!$this->setupFilesystemAPI())
                return false;

            $folder = $wp_filesystem->abspath();
            if ($wp_filesystem->exists($folder . "/.maintenance"))
                $wp_filesystem->delete($folder . "/.maintenance");

            return true;
        }
    }

    global $wpuppy;
    $wpuppy = new WPuppy();
?>