<?php
/**
 * Dynamic URL: Change Password.
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea;

?>
<header>
    <h2>Change Password</h2>
</header>
<?php

if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {

    \tamarind_forms\display_form\display_form( 'change_password_userarea', true, get_the_id() );

}