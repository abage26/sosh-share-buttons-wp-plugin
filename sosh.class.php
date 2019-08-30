<?php

class Social_share
{

    public function __construct()
    {
        $this->init();
    }

    private function init(){

        // Hooks
        add_filter('the_content', array($this, "add_icon_to_content"), 1);

        add_action('wp_enqueue_scripts', [$this,'enqueue_sosh_script_files']);
        add_action('wp_enqueue_scripts', [$this,'enqueue_sosh_css_files']);


        // Admin Hooks
        add_action( 'admin_menu', array( $this, 'create_admin_page' ) );
        add_action( 'admin_enqueue_scripts', [$this,'sosh_admin_scripts']);

    }

    public function add_icon_to_content($content){

        global $post;

        $social_share_options = get_option(SOSH_OPTIONS_NAME);

        //$content = $social_share_options['displays']['inline_content'];

        if(is_array($social_share_options) && isset($social_share_options['post_types']) && !empty($social_share_options['post_types'])){

            $post_types=$social_share_options['post_types'];

            $social_share_options_displays = (is_array($social_share_options) && isset($social_share_options['displays']) && !empty($social_share_options['displays'])) ? $social_share_options['displays'] : [];


            if(in_array($post->post_type, $post_types)){

                if (is_array($social_share_options_displays) && !empty($social_share_options_displays)):

                    if (isset($social_share_options_displays['inline_content']) && is_string($social_share_options_displays['inline_content']) && !empty($social_share_options_displays['inline_content'])):
                        switch ($social_share_options_displays['inline_content']):
                            case 'above':
                                $content = $this->get_share_block_html().$content;
                                break;
                            case 'below':
                                $content = $content.$this->get_share_block_html();
                                break;
                            case 'above_and_below':
                                $content = $this->get_share_block_html().$content.$this->get_share_block_html();
                                break;
                                default:
                        endswitch;
                    endif;

                endif;

            }

        }

        return $content;

    }

    public function add_sosh_btns_modal_to_footer(){

        global $post;

        $social_share_options = get_option(SOSH_OPTIONS_NAME);

        $url = ( has_post_thumbnail($post->ID) ? get_the_post_thumbnail_url($post->ID) : '' );
        $title = (isset($social_share_options['share_title']) && is_string($social_share_options['share_title'])) ? $social_share_options['share_title'] : 'Share';

        self::sosh_modal(['title' => 'Partager','body' => $this->get_share_block_html(["share_title" => false]),'bg_img_url' => $url]);

        echo "<script>    
/* Counter */
jQuery(function($) {
  
    jQuery.sharedCount = function(url, fn) {
        url = encodeURIComponent(url || location.href);
        var domain = \"//api.sharedcount.com/v1.0/\";
        var apikey = \"37c5aae52cbb76a7c3e2ac58dffee2528f55af76\"
        var arg = {
            data: {
                url : url,
                apikey : apikey
            },
            url: domain,
            cache: true,
            dataType: \"json\"
        };
        if ('withCredentials' in new XMLHttpRequest) {
            arg.success = fn;
        }
        else {
            var cb = \"sc_\" + url.replace(/\W/g, '');
            window[cb] = fn;
            arg.jsonpCallback = cb;
            arg.dataType += \"p\";
        }
        return jQuery.ajax(arg);
    };

    var f = $.sharedCount('".get_current_page_url()."',function(response){
        
        var fb = response['Facebook']['total_count'],
            gp = response['GooglePlusOne'],
            pr = response['Pinterest'],
            li = response['LinkedIn'];
        var count = 0;
        count = fb+gp+pr+li;
        $('.sosh-total-share-count').html(count);
    });

});
                </script>";
    }

    /**
* @param array $params
 */
    public static function sosh_modal($params = []) {
        if (empty($params))return;

        $title = (isset($params['title']) ? $params['title'] : '');
        $body = (isset($params['body']) ? $params['body'] : '');
        $footer = (isset($params['footer']) ? $params['footer'] : '');
        $bg_img_url = (isset($params['bg_img_url']) ? $params['bg_img_url'] : '');

        ob_start()?>
            <div class="soshmodal soshfade " id="sosh_btns_modal" tabindex="-1" role="dialog" aria-labelledby="sosh_btns_modal_label" aria-hidden="true">

                <div class="soshmodal-dialog">
                    <div class="soshmodal-content" <?= (!empty($bg_img_url) ? 'style="background-image: url('.$bg_img_url.');"' : "" )?>>
                        <div class="soshmodal-header">
                            <button type="button" class="sosh-close" data-dismiss="soshmodal"><span aria-hidden="true">&times;</span><span class="sosh-sr-only">Close</span></button>
                            <h4 class="soshmodal-title" id="sosh_btns_modal_label" style="background: rgba(255,255,255,0.2)"><?= (!empty($title) ? $title : '') ?></h4>
                        </div>

                            <?= (!empty($body) ? '<div class="soshmodal-body">'.$body.'</div>' : '') ?>

                        <div class="soshmodal-footer">

                            <?= (!empty($footer) ? $footer : '<button type="button" class="soshbtn soshbtn-default" data-dismiss="soshmodal">Fermer</button>' ) ?>
                            <!--<button type="button" class="btn btn-primary" id="dont_show_again">Don\'t Show Again</button>-->
                        </div>
                    </div>
                </div></div>
        <?php $html = ob_get_clean();
        echo $html;

    }

    public function create_admin_page() {

        add_menu_page( 'Social share', 'Social share', 'manage_options', 'social-share', array($this, 'admin_page_content'), "dashicons-share", 100 );

    }

    public function admin_page_content(){

        if(isset($_POST[SOSH_OPTIONS_NAME])){
            $update = update_option(SOSH_OPTIONS_NAME, $_POST[SOSH_OPTIONS_NAME]);

            if (isset($update)): ?>
                <div class="notice notice-<?php echo ($update) ? 'success' : 'error' ?> is-dismissible">
                    <p> <strong><?php echo ($update) ? 'Mise à jour réussie' : 'Aucune mise à jour' ?> </strong>.</p>
                    <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
                </div>
            <?php endif;
        }
        ?>

        <div class="wrap">

        <h1>Share Button config page</h1>

        <form method="POST">

        <?php

        $social_share_options = get_option(SOSH_OPTIONS_NAME);

        $post_types = get_post_types(array('public' => true));
        $share_btns = $this->get_social_share_btns();
        $displays = [
            'floating_sidebar' => ['No' => ''/*,'Left' => 'left','Right' => 'right'*/],
            'inline_content' => ['No' => '','Above' => 'above','Below' => 'below','Above and Below' => 'above_and_below']
        ];
        ?>

            <label>
                <h2>Share title:</h2>
                <p>
                <input type="text" name="<?= SOSH_OPTIONS_NAME.'[share_title]' ?>" value="<?= $social_share_options['share_title'] ?>">
                </p>
            </label>


            <h2>Share Buttons:</h2>
            <p class="checklist" id="sosh_btns_checklist">
            <label class="">
                <input type="checkbox" id="sosh_btns_checkAll">Tout
            </label>
                <?php foreach ($share_btns as $key => $share_btn) { ?>

                    <label class="">

                        <input type="checkbox" value="<?php echo $key; ?>" name="<?= SOSH_OPTIONS_NAME.'[share_btns][]' ?>" <?php echo (is_array($social_share_options) && isset($social_share_options['share_btns']) && is_array($social_share_options['share_btns']) && in_array($key, $social_share_options['share_btns'])) ? "checked" : "" ?> >

                        <span><?php echo $key; ?></span>

                    </label>

                <?php } ?>
            </p>


            <h2>Share Pages:</h2>
            <p class="checklist" id="sosh_pages_checklist">
            <label class="">
                <input type="checkbox" id="sosh_pages_checkAll">Tout
            </label>
                <?php foreach ($post_types as $key => $post_type) { ?>

                    <label class="">

                        <input type="checkbox" value="<?php echo $key; ?>" name="<?= SOSH_OPTIONS_NAME.'[post_types][]' ?>" <?php echo (is_array($social_share_options) && isset($social_share_options['post_types']) && is_array($social_share_options['post_types']) && in_array($key, $social_share_options['post_types']))?"checked":"" ?> >

                        <span><?php echo $key; ?></span>

                    </label>

                <?php } ?>
            </p>

            <h2>Display:</h2>
            <p>
                <?php foreach ($displays as $display => $positions) { ?>

                    <label><?= $display ?>

                        <select name="<?=  SOSH_OPTIONS_NAME."[displays][$display]" ?>">

                            <?php foreach ($positions as $key => $position): ?>

                                <option value="<?= $position ?>" <?php echo (is_array($social_share_options) && isset($social_share_options['displays']) && is_array($social_share_options['displays']) && isset($social_share_options['displays'][$display]) && $position === $social_share_options['displays'][$display]) ? "selected" : "" ?>><?= $key ?></option>

                            <?php endforeach; ?>

                        </select>

                    </label>

                <?php } ?>
            </p>

            <?php submit_button(); ?>

        </form>

        <?php

        //vdump_pre(get_option(SOSH_OPTIONS_NAME));
    }

    /**
     * @param bool|array $sosh_param
     * @return string|void
     */
    private function get_share_block_html($sosh_param = true){
        $default_param = ['share_title' => true, 'share_btns_label' => true];
        $sosh_block_params_array = $default_param;
        if ($sosh_param = true){
            $sosh_block_params_array = $default_param;
        }elseif (is_array($sosh_param)){
            $sosh_block_params_array = array_merge($default_param,$sosh_param);
        }

        $share_title = $sosh_block_params_array['share_title'];
        $share_btns_label = $sosh_block_params_array['share_btns_label'];


        $social_share_options = get_option(SOSH_OPTIONS_NAME);

        $social_options_btns = $social_share_options['share_btns'];

        $social_btns = $this->get_social_share_btns();

        $btns = [];
        foreach ($social_btns as $key => $social_btn):
            if (is_array($social_options_btns) && in_array($key,$social_options_btns))
                $btns[$key] = $social_btn;
        endforeach;

        if (empty($btns)){return;}

        add_filter('wp_footer',[$this,'add_sosh_btns_modal_to_footer']);

        ob_start();
        ?>

        <!--<div id="sosh-content-top" class="sosh-content-wrapper sosh-shape-circle sosh-column-3 sosh-has-spacing sosh-hide-on-mobile sosh-button-style-1 sosh-has-icon-background sosh-has-button-background sosh-show-total-share-count sosh-show-total-share-count-after">-->
        <div>
            <?= ($share_title && isset($social_share_options['share_title']) && is_string($social_share_options['share_title'])) ? '<span class="sosh-networks-btns-title">'.$social_share_options['share_title'].'</span>' : '' ?>
            <div class="sosh-content-wrapper sosh-shape-circle sosh-column-<?= count($btns) ?> sosh-has-spacing sosh-hide-on-mobile sosh-button-style-1 sosh-has-icon-background sosh-has-button-background sosh-show-total-share-count sosh-show-total-share-count-after">

                <ul class="sosh-networks-btns-wrapper sosh-networks-btns-content sosh-has-button-icon-animation">

                <?php /*<a href="https://twitcount.com/btn" class="twitcount-button" data-count="vertical" data-size="large" data-text="" data-url="" data-via="" data-related="" data-hashtags="">TwitCount</a> <script type="text/javascript" src="https://static1.twitcount.com/js/button.js"></script> */ ?>
                <?php foreach ($btns as $btn_name => $btn_data): ?>
                            <li class="">
                                <a rel="nofollow" href="<?= $btn_data["url"] ?>" class="sosh-network-btn sosh-<?= $btn_name ?> sosh-first" marked="1">
                                    <span class="sosh-network-icon"></span>
                                    <?php if ( ($share_btns_label) ): ?>
                                    <span class="sosh-network-label-wrapper">
                                        <span class="sosh-network-label"><?= $btn_data['name'] ?></span>
                                    </span>
                                    <?php endif; ?>
                                </a>
                            </li>
                <?php endforeach; ?>

                </ul>

                <div class="sosh-total-share-wrapper">
                    <span class="sosh-icon-total-share"></span><span class="sosh-total-share-count">0</span>
                    <span>Partages</span>
                </div>

            </div>
        </div>

        <?php

        return ob_get_clean();
    }

    private function get_social_share_btns(){
        global $post;

        $post_title = ( !is_admin() ? get_the_title($post->ID) : '');
        $post_url = ( !is_admin() ? urlencode(get_current_page_url()) : '');

        $share_btns = [
            'facebook' => ['name' => 'Partager', 'enabled' => true, 'url' => 'https://facebook.com/sharer/sharer.php?u='.$post_url],
            'twitter' => ['name' => 'Twitter', 'enabled' => true, 'url' => 'https://twitter.com/intent/tweet?text='.$post_title.'&url='.$post_url],
            'googleplus' => ['name' => 'Google+', 'enabled' => true, 'url' => 'https://plus.google.com/share?url='.$post_url],
            'pinterest' => ['name' => 'Pinterest', 'enabled' => true, 'url' => 'https://pinterest.com/pin/create/bookmarklet/?&url='.$post_url],
            'linkedin' => ['name' => 'LinkedIn', 'enabled' => true, 'url' => 'https://www.linkedin.com/shareArticle?mini=true&title='.$post_title.'&url='.$post_url],
            'whatsapp' => ['name' => 'WhatsApp', 'enabled' => true, 'url' => 'whatsapp://send?text='],
        ];

        return $share_btns;
    }

    public function enqueue_sosh_script_files() {
        $path = _PLUGIN_DIR_URL.'assets/';
        $social_share_btns = $this->get_social_share_btns();

        $files_array = array(
            "sosh_bootstrap_modal" => $path . 'js/soshmodal.min.js',
            "sosh_script_js" => $path . 'js/sosh_script.js',
        );

        foreach ($files_array as $key => $file) {
            //wp_register_script($key, $file . "?ver=" . $ver, array('jquery'), '', true);
            wp_register_script($key, $file ,['jquery'],null,true);
            wp_enqueue_script($key);
        }

        $files = [
            'sosh_script_js' => [
                'assets_url' => $path,
		        'x_url' => admin_url('admin-ajax.php'),
            ],
        ];

        foreach ($files as $script => $trans_array):
            wp_localize_script($script, $script.'_vars_object',$trans_array);
        endforeach;
    }

    public function enqueue_sosh_css_files() {
        $path = _PLUGIN_DIR_URL.'assets/';
        $files_array = array(
            "sosh-bootstrap-modal-css" => $path . 'css/soshmodal.min.css',
            "sosh-style-css" => $path . 'css/sosh-style.css',
        );

        foreach ($files_array as $key => $file) {
            wp_register_style($key, $file,[],null);
            wp_enqueue_style($key);
        }
    }

    public function sosh_admin_scripts($hook) {

        if(!in_array($hook,['toplevel_page_social-share','plugins.php'])) {
            return;
        }
        if ( 'toplevel_page_social-share' === $hook ) :
            wp_enqueue_style( 'custom_wp_admin_css', plugins_url('assets/css/sosh-admin-style.css', __FILE__) );
            wp_enqueue_script( 'custom_wp_admin_script', plugins_url('assets/js/sosh-admin-script.js', __FILE__) );

            elseif ( 'plugins.php' === $hook ) :
                wp_register_script('admin_page_plugins', plugins_url('assets/js/admin-page-plugins.js', __FILE__, ['jquery','jquery-ui-core','jquery-ui-dialog'],false,true));
                wp_localize_script( 'admin_page_plugins', 'transObj', ['x_url' => admin_url('admin-ajax.php')] );
                wp_enqueue_script('admin_page_plugins');
        endif;
    }

    public function wp_admin_scripts($hook){
        $method = 'admin_page_'.strtr($hook,['.php'=>""]);
        if (!method_exists(__CLASS__,$method))
            return;
        //self::$method();

        add_action('admin_footer-'.$hook, [$this,$method]);
    }

    static function social_share_install(){
        $social_share_options['share_title'] = 'Share';
        $social_share_options['share_btns'] = ['facebook','twitter','whatsapp'];
        $social_share_options['post_types'] = ['post'];
        $social_share_options['displays'] = ['floating_sidebar' => 'left', 'inline_content' => 'below'];

        if ( get_option( SOSH_OPTIONS_NAME ) === false ) {
            add_option( SOSH_OPTIONS_NAME, $social_share_options );
        }
    }

    static function social_share_uninstall(){
    }
}
?>