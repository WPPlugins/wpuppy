<?php
if (!defined('ABSPATH'))
    die ('No script kiddies please');

header('Content-type: application/json; charset=utf-8');
header("access-control-allow-origin: *");
//Once we got all our variables, load wordpress and our plugin

global $wpuppy;

/****************************************************************
 * Check Variables
 ***************************************************************/

//Key not found
if (filter_input(INPUT_GET, "key") === NULL) {
    echo json_encode(
        array(
            "type" => "error",
            "message" => "You are required to send an API key"
        )
    );
    exit;
}
//Action not found
if (filter_input(INPUT_GET, "action") === NULL) {
    echo json_encode(
        array(
            "type" => "error",
            "message" => "You are required to send an action"
        )
    );
    exit;
}

/****************************************************************
 * API
 ***************************************************************/

$key = filter_input(INPUT_GET, "key");
//API Key not valid
if (!$wpuppy->check_api_key($key)) {
    echo json_encode(
        array(
            "type" => "error",
            "message" => "The API key is invalid"
        )
    );
    exit;
}

/****************************************************************
 * Check Actions
 ***************************************************************/

switch(filter_input(INPUT_GET, "action")) {

    /*
     * Updates
     * update_plugin: Update a plugin based on the Slug
     * update_wordpress: Update wordpress
     */
    case 'update_plugin':
        if (($slug = filter_input(INPUT_GET, "slug")) === NULL) {
            echo json_encode(
                array(
                    "type" => "error",
                    "message" => "No plugin was specified"
                )
            );
            exit;
        }

        $update = $wpuppy->update_plugin($slug);
        
        echo json_encode($update);
        break;
    case 'update_plugins':
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if (($slugs = filter_input(INPUT_GET, "slugs")) === NULL) {
            echo json_encode(
                array(
                    "type" => "error",
                    "message" => "No plugins were specified"
                )
            );
            exit;
        }
        $messages = array();

        $slugs = json_decode($slugs, true);
        foreach($slugs as $slug) {
            $update = $wpuppy->update_plugin($slug);
            $messages[] = $update['message'];
        }
        
        echo json_encode(
            array(
                "type" => "success", 
                "message" => $messages
            )
        );
        break;
    case 'update_wordpress':
        $output = $wpuppy->update_wordpress();
        
        echo json_encode($output);
        break;

    case 'update_theme':
        if (($slug = filter_input(INPUT_GET, "slug")) === NULL) {
            echo json_encode(
                array(
                    "type" => "error",
                    "message" => "No theme was specified"
                )
            );
            exit;
        }

        $update = $wpuppy->update_theme($slug);
        
        echo json_encode($update);
        break;

    /*
     * GET Functions
     * get_plugins: Gets a list of plugins of this website
     * get_sitemap: Generate a sitemap list of this website
     * check_updates: Checks the website on any updates available
     */

    case 'get_plugins':
        $output = json_encode(@$wpuppy->get_plugin_list());
        
        echo $output;
        break;

    case "get_themes":
        $output = json_encode(@$wpuppy->get_themes_list());
        
        echo $output;
        break;

    case 'get_sitemap':
        $output = json_encode(@$wpuppy->get_sitemap());
        
        echo $output;
        break;

    case "check_updates":
        $plugins = @$wpuppy->list_plugin_updates();
        $wordpress = @$wpuppy->list_core_updates();
        $themes = @$wpuppy->list_theme_updates();
        $requirements = @$wpuppy->get_requirements();
        
        if (empty($plugins) && empty($wordpress) && empty($themes)) {
            echo json_encode(
                array(
                    "type" => "uptodate",
                    "data" => @$wpuppy->get_plugin_data(),
                    "requirements" => $requirements
                )
            );
            exit;
        }
        
        //Remove empty arrays
        if (empty($plugins))
            unset($plugins);

        if (empty($themes))
            unset($themes);
            
        if (empty($wordpress))
            unset($wordpress);
        

        echo json_encode(
            array(
                "type" => "notuptodate",
                "data" => @$wpuppy->get_plugin_data(),
                "updates" => array(
                    "core" => $wordpress ?: 'up-to-date',
                    "plugins" => $plugins ?: 'up-to-date',
                    "themes" => $themes ?: 'up-to-date'
                ),
                "requirements" => $requirements
            )
        );
        break;

    /*
     * PUT Functions
     * backup_database: Backups the database and puts a file in the root
     */
    case "backup_database":
        $output = json_encode(@$wpuppy->backup_database());
        
        echo $output;
        break;
    case "cleanup_database":
        $output = json_encode(@$wpuppy->backup_database_clean());
        
        echo $output;
        break;
    case "restore_database":
        $output = json_encode(@$wpuppy->restore_database());
        
        echo $output;
        break;
    /*
     * Action was unrecognized
     */
    default:
        echo json_encode(
            array(
                "type" => "error",
                "message" => "Unrecognized action " . filter_input(INPUT_GET, "action")
            )
        );
        break;
}
die();
?>