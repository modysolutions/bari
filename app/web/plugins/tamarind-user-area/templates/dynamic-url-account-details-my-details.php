<?php
/**
 * Dynamic URL: My Details.
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>My Details</h2>
</header>

<div class="tm-my-details">

    <?php
    if (is_plugin_active('tamarind-forms/tamarind-forms.php')) {

        \tamarind_forms\display_form\display_form('update_user_userarea', true, get_the_id());
    }
    ?>

</div>