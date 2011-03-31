<?php
/**
 * Facebook Page Publish - publishes your blog posts to your fan page.
 * Copyright (C) 2011  Martin Tschirsich
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
 * Plugin Name: Facebook Page Publish
 * Plugin URI:  http://wordpress.org/extend/plugins/facebook-page-publish/
 * Description: Publishes your posts on the wall of a facebook page.
 * Author:      Martin Tschirsich
 * Version:     0.2.2
 * Author URI:  http://usr.bplaced.de/
 */

#error_reporting(E_ALL);
define("BASEDIR", dirname(__file__));
define("BASEURL", WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__), '', plugin_basename(__FILE__)));
define("SSL_VERIFY", true);
define("ALWAYS_POST_TO_FACEBOOK", true);

add_action('edit_post', 'fpp_publish_action');
add_action('admin_init', 'fpp_admin_init_action');
add_action('admin_menu', 'fpp_admin_menu_action');
add_action('wp_head', 'fpp_head_action');
add_action('post_submitbox_start', 'fpp_post_submitbox_start_action');
register_activation_hook(__FILE__, 'fpp_activation_hook');

/**
 * Called on plugin activation. Initializes plugin options on first run.
 */
function fpp_activation_hook() {
        $options = get_option('fpp_options');
        if (!is_array($options)) {
                $options = array('app_id' => '', 'app_secret' => '', 'api_key' => '', 'page_id' => '', 'show_gravatar' => '1');
                update_option('fpp_options', $options);
                update_option('fpp_page_access_token', '');
        }
}

/**
 * Called on html head rendering. Prints meta tags to make posts appear correctly in facebook. 
 */
function fpp_head_action() {
        global $post;

        if (is_object($post) && empty($post->post_password) && ($post->post_type == 'post') && is_single()) {
                fpp_render_meta_tags($post);
        }
}

/**
 * Called on edit_post (preview, change, publish) only if fpp_post_to_facebook-post-variable is set.
 * Publishes the given post to facebook.
 *
 * @see fpp_render_post_button()
 */
function fpp_publish_action($post_id) {
        $post_to_facebook = isset($_REQUEST['fpp_post_to_facebook']) && !empty($_REQUEST['fpp_post_to_facebook']);
         
        if ($post_to_facebook) {
                try {
                        $post = get_post($post_id);
                        if (($post->post_type == 'post') && empty($post->post_password)) {
                                $options = get_option('fpp_options');
                                fpp_publish_to_facebook($post, $options['page_id'], get_option('fpp_page_access_token'));
                                add_post_meta($post->ID, '_fpp_post_to_facebook', 'posted');
                        }
                } catch (exception $exception) {
                        wp_die('An exception occured in your Facebook Page Publish plugin: '.$exception->getMessage().
                               '<br>Please inform the author and/or deactivate the plugin.');
                }
        }
}

/**
 * Called on admin menu rendering, adds an options page and its rendering callback.
 *
 * @see fpp_render_options_page()
 */
function fpp_admin_menu_action() {
        add_options_page('Facebook Page Publish Options', 'Facebook Page Publish', 'manage_options', __FILE__, 'fpp_render_options_page');
}

/**
 * Called when a user accesses the admin area. Registers settings and a sanitization callback.
 *
 * @see fpp_validate_options
 */
function fpp_admin_init_action() {
        register_setting('fpp_options_group', 'fpp_options', 'fpp_validate_options');
}

/**
 * Called when the submitbox is rendered. Renders a publish to facebook
 * button if the current user is an author.
 */
function fpp_post_submitbox_start_action() {
        global $post;

        if (is_object($post) && ($post->post_type == 'post') && current_user_can('publish_posts')) {
                fpp_render_post_button();
        }
}

/**
 * Publishes the given post to a facebook page.
 */
function fpp_publish_to_facebook($post, $page_id, $acces_token) {
        if (!class_exists('WP_Http')) include_once(ABSPATH.WPINC.'/class-http.php');

        $message = stripslashes(htmlspecialchars_decode(wp_filter_nohtml_kses(strip_shortcodes(empty($post->post_excerpt) ? $post->post_content : $post->post_excerpt))));

        if (strpos($message, '<!--more-->') !== false) {
                $message = substr($message, 0, strpos($message, '<!--more-->'));
        }
        
        // Facebook link description allows max. 420 characters:
        if (strlen($message) >= 420) {
                $last_space_pos = strrpos(substr($message, 0, 417), ' ');
                $message = substr($message, 0, $last_space_pos).'...';
        }

        $api_url = 'https://graph.facebook.com/'.urlencode($page_id).'/links';
        $body = array('message' => $message, 'link' => get_permalink($post->ID), 'access_token' => $acces_token);
        $request = new WP_Http;
        $response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'sslverify' => SSL_VERIFY));

        $return = array();
        if (array_key_exists('errors', $response))
                wp_die('Facebook not reachable: '.(empty($response->errors) ? 'unknown error' : array_pop(array_pop($response->errors))));

        $object = json_decode($response['body']);
        if (property_exists($object, 'error'))
                wp_die('Can\'t access facebook user account data!');
}

/**
 * Checks whether a given facebook page can be accessed (write) or not.
 *
 * TODO: Does not recognize missing manage_pages - permission!
 */
function fpp_check_connection_to_facebook($page_id, $page_access_token) {

        $api_url = 'https://graph.facebook.com/'.urlencode($page_id).'/links';
        $body = array('message' => 'dummy message', 'link' => 'invalid url', 'access_token' => $page_access_token);
        $request = new WP_Http;
        $response = $request->request($api_url, array('method' => 'POST', 'body' => $body, 'sslverify' => SSL_VERIFY));

        if (array_key_exists('errors', $response))
                return array('result' => 'connection_error', 'message' => 'Facebook not reachable: '.(empty($response->errors) ? 'unknown error' : array_pop(array_pop($response->errors))));

        // If everything is ok, we should receive a BAD_REQUEST / 400 response, otherwise FORBIDDEN / 403:
        if (($response['response']['code'] == 403))
                return array('result' => 'token_error', 'message' => 'Authorization withdrawn.');

        return array('result' => 'success', 'message' => 'Successfully connected!');
}

/**
 * Checks whether a given facebook app id is valid.
 */
function fpp_is_valid_facebook_app($app_id) {
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/'.urlencode($app_id);
        $response = $request->get($api_url, array('sslverify' => SSL_VERIFY));

        if (array_key_exists('errors', $response)) // Facebook is unreachable, return null.
                return null;

        $object = json_decode($response['body']);
        if (property_exists($object, 'error'))
                return false;

        return true;
}

/**
 * Tries to acquire page write access (a permanent access token).
 */
function fpp_retrieve_page_access($app_id, $app_secret, $page_id, $redirect_uri, $code) {

        // Retrieve access-token (pems: manage pages, offline access) for the user:
        $request = new WP_Http;
        $api_url = 'https://graph.facebook.com/oauth/access_token?client_id='.urlencode($app_id).'&redirect_uri='.urlencode($redirect_uri).'&client_secret='.urlencode($app_secret).'&code='.urlencode($code);
        $response = $request->get($api_url, array('sslverify' => SSL_VERIFY));

        if (array_key_exists('errors', $response))
                return array('result' => 'connection_error', 'message' => 'Facebook not reachable: '.(empty($response->errors) ? 'unknown error' : array_pop(array_pop($response->errors))));

        $json_response = json_decode($response['body']);
        if ($json_response != null)
                return array('result' => 'authorization_error', 'message' => 'Authorization rejected: '.$json_response->error->message);

        $access_token_url = $response['body'];

        // Request accounts object:
        $api_url = 'https://graph.facebook.com/me/accounts?'.$access_token_url;
        $response = $request->get($api_url, array('sslverify' => SSL_VERIFY));

        if (array_key_exists('errors', $response))
                return array('result' => 'connection_error', 'message' => 'Facebook not reachable: '.(empty($response->errors) ? 'unknown error' : array_pop(array_pop($response->errors))));

        $accounts = json_decode($response['body']);
        if (!is_object($accounts) || !property_exists($accounts, 'data'))
                return array('result' => 'api_error', 'message' => 'Can\'t access facebook user account data!');

        foreach ($accounts->data as $account) {
                if ($account->id == $page_id) {
                        $page_access_token = $account->access_token;
                        break;
                }
        }

        if (!isset($page_access_token))
                return array('result' => 'page_error', 'message' => 'Can\'t find Facebook page with given ID.');
                
        return array('result' => 'success', 'page_access_token' => $page_access_token);
}

/**
 * Renders the options page. Uses the settings API (options validation, checking and storing by WP).
 * Also validates certain options (facebook access) that need redirecting.
 */
function fpp_render_options_page() {
        $options = get_option('fpp_options');
        $redirect_uri = admin_url('admin.php?page='.urlencode(plugin_basename(__FILE__)));

        if (array_key_exists('code', $_GET)) {
                $access = fpp_retrieve_page_access($options['app_id'], $options['app_secret'], $options['page_id'], $redirect_uri, $_GET['code']);
                
                if ($access['result'] == 'success') {
                        update_option('fpp_page_access_token', $access['page_access_token']);
                } else {
                        update_option('fpp_page_access_token', 'invalid');
                        echo '<div class="error"><p><strong>'.htmlentities($access['message']).'</strong></p></div>';
                }
        }

        ?>
        <div class="wrap">
                <div class="icon32" id="icon-options-general"><br></div>
                
                <h2>Facebook Page Publish Plugin Options</h2>
                <p>Configure the plugin options below</p>
                <form method="post" action="options.php">
                        <h3>Facebook Connection</h3>
                        <a target="_blank" href="<?php echo BASEURL; ?>setup.htm">Detailed Setup Instructions</a>
                        <table class="form-table">
                                <?php settings_fields('fpp_options_group'); ?>
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options[app_id]">Application ID</label></th>
                                        <td><input id="fpp_options[app_id]" name="fpp_options[app_id]" type="text" value="<?php echo htmlentities($options['app_id']); ?>" /></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options[app_secret]">Application Secret</label></th>
                                        <td><input id="fpp_options[app_secret]" name="fpp_options[app_secret]" type="text" value="<?php echo htmlentities($options['app_secret']); ?>" /></td>
                                </tr>
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options[page_id]">Page ID</label></th>
                                        <td><input id="fpp_options[page_id]" name="fpp_options[page_id]" type="text" value="<?php echo htmlentities($options['page_id']); ?>" /></td>
                                </tr>
                        </table class="form-table">
                        <?php
                                $page_access_token = get_option('fpp_page_access_token');
                                
                                if (!empty($options['app_id']) && !empty($options['app_secret']) && !empty($options['page_id'])) {# && (!isset($access) || $access['result'] == 'success')) {
                                        if ($page_access_token == 'invalid') {
                                                echo '<p><font style="color:red">Facebook recognized these settings as invalid!</font></p>';
                                        } else if (empty($page_access_token)) {
                                                $validation = fpp_is_valid_facebook_app($options['app_id']);
                                                if ($validation === true) {
                                                        echo '<p><font style="color:red">Not authorized!</font> <a class="button-secondary" href="https://www.facebook.com/dialog/oauth?client_id='.$options['app_id'].'&redirect_uri='.urlencode($redirect_uri).'&scope=manage_pages,offline_access,share_item">Authorize</a></p>';
                                                } else if ($validation === false)  {
                                                        echo '<p><font style="color:red">Not authorized - an application with the given id does not exist!</font></p>';
                                                } else if ($validation === null) {
                                                        echo '<p><font style="color:red">Can\'t connect to facebook!</font></p>';
                                                }
                                        } else {
                                                $check = fpp_check_connection_to_facebook($options['page_id'], $page_access_token);
                                                if ($check['result'] == 'success') {
                                                        echo '<p><font style="color:green">Authorized.</font></p>';
                                                } else {
                                                        echo '<p><font style="color:red">'.htmlentities($check['message']).' <a class="button-secondary" href="https://www.facebook.com/dialog/oauth?client_id='.$options['app_id'].'&redirect_uri='.urlencode($redirect_uri).'&scope=manage_pages,offline_access,share_item">Authorize</a></font></p>';
                                                }
                                        }
                                } else {
                                        echo '<p><font style="color:gray">Please fill all given fields.</font></p>';
                                }
                        ?>
                        <h3>Further Options</h3>
                        <table class="form-table">
                                <tr valign="top">
                                        <th scope="row"><label for="fpp_options[show_gravatar]">Show <a href="http://gravatar.com" target="_new">Gravatar</a></label></th>
                                        <td><input id="fpp_options[show_gravatar]" type="checkbox" name="fpp_options[show_gravatar]" value="1" <?php checked('1', $options['show_gravatar']); ?> /></td>
                                </tr>
                        </table>
                        <p class="submit">
                                <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                        </p>
                </form>
                <?php
                if (!SSL_VERIFY) echo '<div class="updated"><p><strong>Info:</strong> SSL verification manually turned off</p></div>';
                ?>
        </div>
        <?php
}

/**
 * Render facebook recognized meta tags (Open Graph protocol).
 * Facebooks uses them to refine shared links for example.
 */
function fpp_render_meta_tags($post) {
        $options = get_option('fpp_options');

        // Description = post category names:
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
                        $description = $categories[0]->cat_name;
                        for ($i = 1; $i < sizeof($categories); ++$i)
                                $description .= ', '.$categories[$i]->cat_name;
        }

        // Image = post author gravatar or post content or attachment:
        if ($options['show_gravatar']) {
                preg_match_all('/<img .*src=["|\']([^"|\']+)/i', get_avatar($post->post_author), $matches);
                if (!empty($matches[1][0])) $image_url = $matches[1][0];
        }

        if (!isset($image_url)) {
                preg_match_all('/<img .*src=["|\']([^"|\']+)/i', $post->post_content, $matches);
                if (!empty($matches[1][0])) $image_url = $matches[1][0];
        }

        if (!isset($image_url)) {
                $images = get_children('post_type=attachment&post_mime_type=image&post_parent='.$post->ID);
                if (!empty($images)) {
                        foreach ($images as $image_id => $value) {
                                $image = wp_get_attachment_image_src($image_id);
                                $image_url = $image[0];
                                break;
                        }
                }
        }

        #echo '<meta property="og:site_name" content="'.htmlentities(get_bloginfo('url')).'"/>';
        #echo '<meta property="og:title" content="'.htmlentities($post->post_title).'"/>';
        if (isset($description)) echo '<meta property="og:description" content="'.htmlspecialchars($description, ENT_COMPAT, 'UTF-8').'"/>';
        if (isset($image_url)) echo '<meta property="og:image" content="'.htmlspecialchars($image_url, ENT_COMPAT, 'UTF-8').'"/>';
        else echo '<meta property="og:image" content="'.BASEURL.'default.png"/>'; // No image (prevents FB from choosing a poor random image).
}

/**
 * Renders a 'publish to facebook' checkbox. Renders the box only if 
 * the current post is a real post, not a page or something else.
 */
function fpp_render_post_button() {
        global $post;

        if (array_pop(get_post_meta($post->ID, '_fpp_post_to_facebook')) != 'posted') {
                ?>
                <label for="fpp_post_to_facebook">Post to Facebook </label><input <?php if (ALWAYS_POST_TO_FACEBOOK) echo 'checked="checked"' ?> type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" />
                <div><em>Can't be undone!</em></div>
                <?php
        } else {
                ?>
                <label for="fpp_post_to_facebook">Post again to Facebook </label><input type="checkbox" value="1" id="fpp_post_to_facebook" name="fpp_post_to_facebook" />
                <div><em>Can't be undone!</em></div>
                <?php
        }
}

function fpp_validate_options($input) {
        $options = get_option('fpp_options');
        if ($options['app_id'] != $input['app_id'] || $options['app_secret'] != $input['app_secret'] || $options['page_id'] != $input['page_id']) {
                update_option('fpp_page_access_token', '');
        }

        $options['app_id'] = $input['app_id'];
        $options['app_secret'] = $input['app_secret'];
        $options['page_id'] = $input['page_id'];
        $options['show_gravatar'] = array_key_exists('show_gravatar', $input) && !empty($input['show_gravatar']);

        return $options;
}
?>