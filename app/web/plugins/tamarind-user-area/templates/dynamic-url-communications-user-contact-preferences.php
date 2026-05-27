<?php
/**
 * Dynamic URL: User Contact.
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>User contact preferences</h2>
</header>

<div class="tm-support-center-contact-preferences">

        <?php
        if (is_plugin_active('tamarind-forms/tamarind-forms.php')) {

            \tamarind_forms\display_form\display_form('contact_preferences_userarea', true, get_the_id());
        }
        ?>

</div>