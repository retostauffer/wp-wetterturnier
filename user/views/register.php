

<form name="registerform" id="registerform" action="<?php echo esc_url( wp_registration_url() ); ?>" method="post" novalidate="novalidate">
<!-- submission form is created via jquery to prevent spam! -->
   <p>
      <label for="user_login"><?php _e('Username') ?><br />
      <input type="text" name="user_login" id="user_login" class="input" value="" size="20" /></label>
   </p>
   <p>
      <label for="user_email"><?php _e('Email') ?><br />
      <input type="email" name="user_email" id="user_email" class="input" value="" size="25" /></label>
   </p>
   <?php
   /**
    * Fires following the 'Email' field in the user registration form.
    *
    * @since 2.1.0
    */
   do_action( 'register_form' );
   ?>
   <p id="reg_passmail"><?php _e( 'Registration confirmation will be emailed to you.' ); ?></p>
   <br class="clear" />
   <input type="hidden" name="redirect_to" value="<?php echo
        esc_attr( (isset($redirect_to)) ? $redirect_to : $_SERVER["REQUEST_URI"] ); ?>" />
   <p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Register'); ?>" /></p>
</form>

<p id="nav">
<a href="<?php echo esc_url( wp_login_url() ); ?>"><?php _e( 'Log in' ); ?></a> |
<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" title="<?php esc_attr_e( 'Password Lost and Found' ) ?>"><?php _e( 'Lost your password?' ); ?></a>
</p>

