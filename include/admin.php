<?php

class WPCV_Admin {

    var $option_name;

    function __construct(){

        $this->slug = WP_Content_Variables()->slug;
		$this->url = WP_Content_Variables::plugin_url;
		$this->option_name = $this->slug.'_settings';
		$this->option_title = __('Content Variables', 'wpcv');
        add_action( 'admin_menu', array( $this, 'register_custom_menu_pages' ) );
        add_action( 'admin_init', array($this, 'settings_page_init'));
        add_action( 'admin_enqueue_scripts', array( $this, 'add_admin_scripts' ) );
    }

    #Notes http://wordpress.stackexchange.com/questions/100023/settings-api-with-arrays-example

    /**
	 * Register menu pages
	 */
	function register_custom_menu_pages() {

        add_management_page(
            $this->option_title,
            $this->option_title,
            'manage_options',
            $this->slug,
            array($this, 'settings_page')
        );

    }

    public function settings_page_init(){

        register_setting( $this->slug.'-group', $this->option_name, array($this, 'validator') );

        add_settings_section(
            $this->slug.'-section',
            __($this->option_title . ' Options', 'wpcv'),
            array($this, 'section_top'),
            $this->slug
        );

        add_settings_field(
            'agatt-goog-analytics-scroll',
            __('Activate scroll tracking', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'scroll_tracking_check',
              'type'             =>  'checkbox',
              'label_for'        =>  'Turn on scroll tracking. <a href="http://scrolldepth.parsnip.io/" target="_blank">Learn more.</a>',
              'default'          =>  'false'

            )
        );
        add_settings_field(
            'agatt-goog-analytics-scroll-scrolledelements',
            __('Comma seperated list of elements to for scrolldepth to check', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'scrolledElements',
              'type'             =>  'text',
              'label_for'        =>  'Scrolling past these items will trigger an event.',
              'default'          =>  ''


            )
        );
        add_settings_field(
            'agatt-goog-analytics-scroll-minheight',
            __('Minimum Height', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'minHeight',
              'type'             =>  'text',
              'label_for'        =>  'Minimum height',
              'default'          =>  0

            )
        );
        add_settings_field(
            'agatt-goog-analytics-scroll-percentage',
            __('Percentage check', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'percentage',
              'type'             =>  'checkbox',
              'label_for'        =>  'Deactivate to only track scrolling to elements listed above.',
              'default'          =>  'true'

            )
        );
        add_settings_field(
            'agatt-goog-analytics-scroll-usertiming',
            __('User Timing', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'userTiming',
              'type'             =>  'checkbox',
              'label_for'        =>  'Track the amount of time between pageload and first scroll.',
              'default'          =>  'true'

            )
        );
        add_settings_field(
            'agatt-goog-analytics-scroll-pixel_depth',
            __('Pixel Depth', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'scrolldepth',
              'element'          =>  'pixel_Depth',
              'type'             =>  'checkbox',
              'label_for'        =>  'Pixel Depth events',
              'default'          =>  'true'

            )
        );

        add_settings_field(
            'agatt-goog-analytics-viewability-check',
            __('Track elements\' viewability', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'viewability',
              'element'          =>  'viewability_check',
              'type'             =>  'checkbox',
              'label_for'        =>  'Turn on viewability tracking',
              'default'          =>  'false'

            )
        );


        # http://code.tutsplus.com/tutorials/create-a-settings-page-for-your-wordpress-theme--wp-20091
        add_settings_field(
            'agatt-goog-analytics-viewability-events',
            __('Viewable Area Tracking', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(
              'parent_element'  =>  'viewable_tracker',
              'element'         =>  'track_these_viewable_elements',
              'type'            =>  'repeating_text_group',
              'label_for'       =>  '<a href="https://developers.google.com/analytics/devguides/collection/gajs/eventTrackerGuide" target="_blank">Read more about event tracking.</a>.',
              'default'         =>  array(0 => array(
                                        'selector' => 'footer',
                                        'name'   => 'Footer'
                                    )),
              'fields'          =>  array(
                                        'Selected DOM Element'   =>  'selector',
                                        'Element Name'           =>  'name'
                                    )
            )
        );

        add_settings_field(
            'agatt-goog-analytics-viewability-interval',
            __('Report Interval', 'wpcv'),
            array($this, 'option_generator'),
            AGATT_MENU_SLUG,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'viewability',
              'element'          =>  'reportInterval',
              'type'             =>  'text',
              'label_for'        =>  'Interval or seconds to track for viewability events (every X seconds, send an event)',
              'default'          =>  15

            )
        );

        add_settings_field(
            'agatt-goog-analytics-viewability-percent',
            __('Report Interval', 'wpcv'),
            array($this, 'option_generator'),
            AGATT_MENU_SLUG,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'viewability',
              'element'          =>  'percentOnScreen',
              'type'             =>  'text',
              'label_for'        =>  'Percent of container that must be on screen to be counted as viewable (example: 50%)',
              'default'          =>  '50%'

            )
        );

        add_settings_field(
            'agatt-goog-analytics-viewability-google-active',
            __('Report Interval', 'wpcv'),
            array($this, 'option_generator'),
            AGATT_MENU_SLUG,
            $this->slug.'-section',
            array(

              'parent_element'   =>  'viewability',
              'element'          =>  'googleAnalytics',
              'type'             =>  'checkbox',
              'label_for'        =>  'Send viewablity events to Google',
              'default'          =>  'true'

            )
        );

        # http://code.tutsplus.com/tutorials/create-a-settings-page-for-your-wordpress-theme--wp-20091
        add_settings_field(
            'agatt-goog-analytics-events',
            __('Click Event Tracking', 'wpcv'),
            array($this, 'option_generator'),
            AGATT_MENU_SLUG,
            $this->slug.'-section',
            array(
              'parent_element'  =>  'click_tracker',
              'element'         =>  'track_these_elements',
              'type'            =>  'repeating_text_group',
              'label_for'       =>  '<a href="https://developers.google.com/analytics/devguides/collection/gajs/eventTrackerGuide" target="_blank">Read more about event tracking.</a>.',
              'default'         =>  array(0 => array(
                                        'domElement' => 'body',
                                        'category'   => 'primary_elements',
                                        'action'     => 'click',
                                        'label'      => 'Body Click'
                                    )),
              'fields'          =>  array(
                                        'DOM Element'       =>  'domElement',
                                        'Object Group Name' =>  'category',
                                        'Action'            =>  'action',
                                        'Event Label'       =>  'label'
                                    )
            )
        );

    }

    public function settings_page() {
      # Methodology: http://kovshenin.com/2012/the-wordpress-settings-api/
      ?>
      <div class="wrap">
          <h2>Advanced Google Analytics Tracking</h2>
          <form action="options.php" method="POST">
              <?php settings_fields( $this->slug.'-group' ); ?>
              <?php $settings = get_option( $this->option_name, array() ); ?>
              <?php do_settings_sections( $this->slug ); ?>
              <?php submit_button(); ?>
          </form>
      </div>
      <?php
    }

    public function section_top(){
      echo 'Set up options for advanced Google Analytics tracking.';
    }

    public function setting($args, $default = array()){
          # Once we're sure that we've enforced singleton, we'll take care of it that way.
          if (empty($settings)){
            $settings = get_option( $this->option_name, array() );
          }
        if (empty($settings)) {



        } elseif (empty($settings[$args['parent_element']]) || empty($settings[$args['parent_element']][$args['element']])){
            $r = '';
        } elseif (!empty($args['parent_element']) && !empty($args['element'])){
            $r = $settings[$args['parent_element']][$args['element']];
        } elseif (!empty($args['parent_element'])) {
            $r = $settings[$args['parent_element']];
        } else {
          $r = '';
        }

        if (empty($r)){
            #$default = array($args['parent_element'] => array($args['element'] => ''));
            return $default;
        } else {
            return $r;
        }
    }

    # Method from http://wordpress.stackexchange.com/questions/21256/how-to-pass-arguments-from-add-settings-field-to-the-callback-function
    public function option_generator($args){
       #echo '<pre>'; var_dump($args); echo '</pre>';  return;
      $parent_element = $args['parent_element'];
      $element = $args['element'];
      $type = $args['type'];
      $label = $args['label_for'];
      $default = $args['default'];
      switch ($type) {
          case 'checkbox':
            $check = self::setting($args, $default);
            if ('true' == $check){
                $mark = 'checked';
            } else {
                $mark = '';
            }
            echo '<input type="checkbox" name="'.$this->option_name.'settings['.$parent_element.']['.$element.']" value="true" '.$mark.' class="'.$args['parent_element'].' '.$args['element'].'" />  <label for="'.$this->option_name.'settings['.$parent_element.']['.$element.']" class="'.$args['parent_element'].' '.$args['element'].'" >' . $label . '</label>';
            break;
          case 'text':
            echo "<input type='text' name=''.$this->option_name.'settings[".$parent_element."][".$element."]' value='".esc_attr(self::setting($args, $default))."' class='".$args['parent_element']." ".$args['element']."' /> <label for=''.$this->option_name.'settings[".$parent_element."][".$element."]' class='".$args['parent_element']." ".$args['element']."' >" . $label . "</label>";
            break;
          case 'repeating_text_group':
            $fields = $args['fields'];
            $c = 0;
            $group = self::setting($args, $default);
            ?>
            <h3 class="<?php echo $this->option_name; ?>-event">Events to track:</h3>
            <ul class="repeater-container" for="repeat-element-<?php echo $parent_element; ?>-<?php echo $element; ?>" id="repeater-<?php echo $parent_element; echo '-'; echo $element; ?>">
                <?php foreach ($group as $event){
                    if ($c > 0) { $id_c = '-'.$c; } else { $id_c = ''; }
                ?>
                    <li class="repeat-element repeat-element-<?php echo $element; echo ' '; echo $element; echo ' '; echo $parent_element; ?> " id="repeat-element-<?php echo $parent_element.'-'.$element . $id_c; ?>">

                        <?php

                            foreach ($fields as $f_label => $field){
                                echo '<input class="'.$field.'" type="text" name="'.$this->option_name.'settings['.$parent_element.']['.$element.'][element-num-'.$c.']['.$field.']" value="'.esc_attr($event[$field]).'" /> <label class="'.$field.'" for="'.$this->option_name.'settings['.$parent_element.']['.$element.'][element-num-'.$c.']['.$field.']">' . $f_label . '</label><br />';
                            }

                        if ($c>0){
                          echo '<a class="repeat-element-remover" href="#">Remove</a><br /><br />';
                        } else {
                          echo '<a class="repeat-element-remover" style="display:none;" href="#">Remove</a><br /><br />';
                        }
                        ?>
                    </li>
                <?php
                $c++;

                }
				echo '<input type="hidden" id="counter-for-repeat-element-'.$parent_element.'-'.$element.'" name="element-max-id-'.$parent_element.'-'.$element.'" value="'.$c.'">';
                ?>
				<a href="#" class="add-repeater">Add another.</a>
            </ul>
            <?php
            break;
      }

    }

    public function add_admin_scripts($hook){
        global $pagenow;

        wp_register_script($this->slug . '-admin', $this->url . 'assets/js/admin.js' , array( 'jquery' ));
        if ('tools_page_'.$this->slug.'-menu' == $hook){
            wp_enqueue_script($this->slug.'-admin');
        }


    }

    public function validator($input){
        $output = get_option( $this->option_name );
        #echo '<pre>'; var_dump($input); die();
        return $input;
    }

}
