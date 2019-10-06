

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
    
// TODO: check doesnt work, possible to add group application?

add_action( 'register_form', 'myplugin_register_form' );
function myplugin_register_form() {

    $first_name = ( ! empty( $_POST['first_name'] ) ) ? trim( $_POST['first_name'] ) : '';
    $last_name = ( ! empty( $_POST['last_name'] ) ) ? trim( $_POST['last_name'] ) : '';
    ?>
           <p>
            <label for="first_name"><?php _e( 'Vorname', 'mydomain' ) ?><br />
                <input type="text" name="first_name" id="first_name" class="input" value="" size="25" /></label>
        </p>

        <p>
            <label for="last_name"><?php _e( 'Nachname', 'mydomain' ) ?><br />
                <input type="text" name="last_name" id="last_name" class="input" value="" size="25" /></label>
        </p>
<?php
//<?php echo esc_attr( wp_unslash( $first_name ) );
//<?php echo esc_attr( wp_unslash( $last_name ) );

    }

    // TODO: ERROR is not shown. WHY???
    //2. Add validation. In this case, we make sure first_name and last_name is required.
    add_filter( 'registration_errors', 'myplugin_registration_errors', 10, 3 );
    function myplugin_registration_errors( $errors, $sanitized_user_login, $user_email ) {

        if ( empty( $_POST['first_name'] ) || ! empty( $_POST['first_name'] ) && trim( $_POST['first_name'] ) == '' ) {
            $errors->add( 'first_name_error', __( '<strong>ERROR</strong>: You must include a first name.', 'mydomain' ) );
        }
        if ( empty( $_POST['last_name'] ) || ! empty( $_POST['last_name'] ) && trim( $_POST['last_name'] ) == '' ) {
            $errors->add( 'last_name_error', __( '<strong>ERROR</strong>: You must include a last name.', 'mydomain' ) );
        }
        else return("Registration complete!");
        return $errors;
    }

    //3. Finally, save our extra registration user meta.
    add_action( 'user_register', 'myplugin_user_register' );
    function myplugin_user_register( $user_id ) {
        if ( ! empty( $_POST['first_name'] ) && ! empty( $_POST['last_name'] ) ) {
            update_user_meta( $user_id, 'first_name', trim( $_POST['first_name'] ) );
            update_user_meta( $user_id, 'last_name', trim( $_POST['last_name'] ) );
        }
    }    
    
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

