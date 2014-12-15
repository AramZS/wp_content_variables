<?php

class WPCV_Admin extends WP_Content_Variables {

    var $option_name;

    function __construct(){
      $mothership = WP_Content_Variables();
      $this->slug = $mothership->slug;
		  $this->url = $mothership->plugin_url;
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

        # http://code.tutsplus.com/tutorials/create-a-settings-page-for-your-wordpress-theme--wp-20091
        add_settings_field(
            'wpcv-variable-list',
            __('Variables', 'wpcv'),
            array($this, 'option_generator'),
            $this->slug,
            $this->slug.'-section',
            array(
              'parent_element'  =>  'variables_list',
              'element'         =>  'variables',
              'type'            =>  'repeating_text_group',
              'label_for'       =>  'Create variables here, Name for labeling, shortcode to call, followed by value.',
              'default'         =>  array(0 => array(
                                        'name'      => 'Variable',
                                        'key'       => 'var1',
                                        'value'     => '0'
                                    )),
              'fields'          =>  array(
                                        'Name'              =>  'name',
                                        'Shortcode Key'     =>  'key',
                                        'Shortcode Value'   =>  'value'
                                    )
            )
        );

    }

    public function settings_page() {
      # Methodology: http://kovshenin.com/2012/the-wordpress-settings-api/
      ?>
      <div class="wrap">
          <h2><?php echo $this->option_title; ?></h2>
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
      echo 'Set up shortcodes for containing variables.';
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
        if ('tools_page_'.$this->slug == $hook){
            wp_enqueue_script($this->slug.'-admin');
        }


    }

    public function validator($input){
        $output = get_option( $this->option_name );
        #echo '<pre>'; var_dump($input); die();
        return $input;
    }

}

WP_Content_Variables()->extend->admin = new WPCV_Admin();
