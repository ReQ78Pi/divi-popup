    <?php

    get_header();

    $loop = new WP_Query( array( 'post_type' => 'divi-popup', 'no_found_rows' => true  ) ); 
    while ( $loop->have_posts() ) : $loop->the_post();

      $divi_popup_bg_color  = get_post_meta( get_the_ID(), 'divi_popup_bg_color', true );
      $divi_popup_blur      = get_post_meta( get_the_ID(), 'divi_popup_blur', true );
      $divi_popup_id        = get_post_meta( get_the_ID(), 'divi_popup_id', true );
      ?>

      <!-- Divi Popup Post -->
      <div id="divi-popup" <?php if( !empty( $divi_popup_id ) ) { ?> class="<?php echo $divi_popup_id; ?>"<?php } ?> style="background-color: <?php echo $divi_popup_bg_color; ?>;">
        <div id="divi-popup-wrapper">
          <?php the_content(); ?>
        </div>
      </div>

      <!-- Divi Popup CSS -->
      <?php if ( !empty( $divi_popup_blur ) ) { ?>
        <style>#divi-popup.<?php echo $divi_popup_id; ?>.open {-webkit-backdrop-filter:blur(<?php echo $divi_popup_blur; ?>px);backdrop-filter:blur(<?php echo $divi_popup_blur; ?>px);overflow:hidden;}</style>
      <?php }

    endwhile;
    wp_reset_query();

    get_footer();