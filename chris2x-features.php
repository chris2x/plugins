<?php
/*
Plugin Name: Chris2x Features Widget
Plugin URI: http://chris2x.com
Description: A feature widget
Author: Chris Christensen
Version: 0.01
Author URI: http://chris2x.com

*/

/**
 * Chris2xFeatureWidget Class
 */
class Chris2xFeatureWidget extends WP_Widget {
    /** constructor */
    function Chris2xFeatureWidget() {
        parent::WP_Widget(false, $name = 'Chris2xFeatureWidget');	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
      extract( $args );
      $title = esc_attr($instance['title']);

?>
<div class="feature-row">
<?php 
      for ($i = 0; $i < 3; $i++) {
        $image = esc_attr($instance['image'.$i]);
        $link = esc_attr($instance['link'.$i]);
        $atitle = esc_attr($instance['title'.$i]);
        $text = $instance['text'.$i];
?>
    <div class="feature">
<?php
        if ($image != "") {
?>            
      <div class="feature-image">
        <a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $atitle; ?>" /></a>
      </div>
<?php
        }
?>            
      <div class="feature-title">
        <a href="<?php echo $link; ?>"><?php echo $atitle ?></a>
      </div>
      <div class="feature-text"><?php echo $text; ?></div>
    </div> <!-- feature -->
<?php
      }
?>  
<div class="clear"></div>
</div> <!-- feature-row -->
<?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
      $title = esc_attr($instance['title']);
    ?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
  	<?php            
      for ($i = 0; $i < 3; $i++) {    			
        $image = esc_attr($instance['image'.$i]);
        $link = esc_attr($instance['link'.$i]);
        $atitle = esc_attr($instance['title'.$i]);
        $text = esc_attr($instance['text'.$i]);
	?>
	<p><label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('image'.$i); ?>" type="text" value="<?php echo $image; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('URL:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('link'.$i); ?>" type="text" value="<?php echo $link; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?> <input class="widefat" name="<?php echo $this->get_field_name('title'.$i); ?>" type="text" value="<?php echo $atitle; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:'); ?> <textarea name="<?php echo $this->get_field_name('text'.$i); ?>"><?php echo $text; ?></textarea></label></p>
	<?php
	    }
    }

} // class Chris2xFeatureWidget

// register FooWidget widget
add_action('widgets_init', create_function('', 'return register_widget("Chris2xFeatureWidget");'));

?>
