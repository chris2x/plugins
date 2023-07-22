<?php
/*
Plugin Name: Chris2x Features Widget Version 2
Plugin URI: http://chris2x.com
Description: A feature widget
Author: Chris Christensen
Version: 0.1
Author URI: http://chris2x.com

*/

/**
 * Chris2xFeatureWidget2 Class
 */
class Chris2xFeatureWidget2 extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::WP_Widget(false, $name = 'Chris2x2FeatureWidget2');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		
      extract( $args );
      $title = esc_attr($instance['title']);
      $text = esc_attr($instance['text']);
      $id = esc_attr($instance['id']);
      $delayed = esc_attr($instance['delayed']);
?>
<div class="feature-outer" id="<?php echo $id; ?>">
<div class="feature-row" >
<div class="center">
<h2><?php echo $title; ?></h2>
<p><div class="feature-desc"><?php echo $text; ?></div></p>
</div>
<?php 
      $count = $instance['count'];
      for ($i = 0; $i < $count; $i++) {
        $image = esc_attr($instance['image'.$i]);
        $link = esc_attr($instance['link'.$i]);
        $atitle = esc_attr($instance['title'.$i]);
        $text = $instance['text'.$i];
        $nomobileimage = $instance['nomobileimage'.$i];
?>
    <div class="feature-square">
<?php
        if ($image != "") {
          if ($delayed != "") {
?>            
      <div class="feature-image <?php echo $nomobileimage == 'on' ? 'not-mobile' : '' ?>" ><!-- <?php echo $nomobileimage ?> -->
        <a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $atitle; ?>" width="250" height="250" /></a>
      </div>
<?php
          }
          else {
?>            
      <div class="feature-image <?php echo $nomobileimage == 'on' ? 'not-mobile' : '' ?>" ><!-- <?php echo $nomobileimage ?> -->
        <a href="<?php echo $link; ?>" title="<?php echo $title; ?>"><img src="<?php echo $image; ?>" alt="<?php echo $atitle; ?>" width="250" height="250" /></a>
      </div>
<?php
          }
        }
?>            
      <a href="<?php echo $link; ?>"><h3><?php echo $atitle ?></h3></a>
      <div class="feature-text"><?php echo $text; ?></div>
    </div> <!-- feature -->
<?php
      }
?>  
<div class="clear"></div>
</div> <!-- feature-row -->
<div class="clear"></div>
</div> <!-- feature-outer -->
<?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
        return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {	
      $title = esc_attr($instance['title']);
      $text = esc_attr($instance['text']);
      $count = esc_attr($instance['count']);
      $id = esc_attr($instance['id']);
      $delayed = esc_attr($instance['delayed']);
      if ($count == 0) {
        $count = 4;
      }
    ?>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:'); ?> <textarea name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea></label></p>
	<p><label for="<?php echo $this->get_field_id('count'); ?>"><?php _e('Widget Count:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo $count; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Id:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo $id; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('delayed'); ?>"><?php _e('Dealyed Image Load:'); ?> <input name="<?php echo $this->get_field_name('delayed'); ?>" type=checkbox <?php echo $delayed == 'on' ? "checked" : "" ?>></label></p>
  	<?php            
      for ($i = 0; $i < $count; $i++) {    			
        $image = esc_attr($instance['image'.$i]);
        $link = esc_attr($instance['link'.$i]);
        $atitle = esc_attr($instance['title'.$i]);
        $text = esc_attr($instance['text'.$i]);
        $nomobileimage = esc_attr($instance['nomobileimage'.$i]);
	?>
	<h2>field <?php echo $i; ?></h2>
	<p><label for="<?php echo $this->get_field_id('image'); ?>"><?php _e('Image:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('image'.$i); ?>" type="text" value="<?php echo $image; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('link'); ?>"><?php _e('URL:'); ?> <input class="widefat" name="<?php echo $this->get_field_name('link'.$i); ?>" type="text" value="<?php echo $link; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title'); ?> <input class="widefat" name="<?php echo $this->get_field_name('title'.$i); ?>" type="text" value="<?php echo $atitle; ?>" /></label></p>
	<p><label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:'); ?> <textarea name="<?php echo $this->get_field_name('text'.$i); ?>"><?php echo $text; ?></textarea></label></p>
	<p><label for="<?php echo $this->get_field_id('nomobileimage'); ?>"><?php _e('No Mobile Image:'); ?> <input name="<?php echo $this->get_field_name('nomobileimage'.$i); ?>" type=checkbox <?php echo $nomobileimage == 'on' ? "checked" : "" ?>></label></p>
	<?php
	    }
    }

} // class Chris2xFeatureWidget2

// register FooWidget widget
add_action('widgets_init', 'Chris2xFeatureWidget2Function');
function Chris2xFeatureWidget2Function() {
  return register_widget("Chris2xFeatureWidget2");
}

?>
