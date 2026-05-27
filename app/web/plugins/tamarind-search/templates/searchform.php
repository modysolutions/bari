<?php

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;

if ( $args['aria_label'] ) {
    $aria_label = 'aria-label="' . esc_attr( $args['aria_label'] ) . '" ';
} else {
    $aria_label = '';
}
$unique_id = _wp_to_kebab_case( $aria_label ) . '-' . uniqid();
?>
<style>
    .<?php echo $unique_id; ?>.search_form button svg {
        fill: var(--color-secondary-lighter, #ababdd);
    }
</style>
<div class="search_form-container">
    <form role="search" <?php echo $aria_label ?>method="get"
          class="search_form <?php echo esc_attr( $unique_id ); ?>" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <label class="search_form-screen_reader_text" for="search-field-<?php echo $unique_id; ?>">
            <?php _x( 'Search for:', 'label' ) ?>
        </label>

        <div class="input-group">
            <?php do_action( 'pre_search_form_field' ); ?>
            <input type="search" class="search_form-field"
                   autocomplete="off"
                   id="search-field-<?php echo $unique_id; ?>"
                   placeholder="<?php echo esc_attr_x( 'Search for markets, regulations, or regions (e.g., UK vaping tax)...', 'placeholder' ) ?>"
                   value="<?php echo get_search_query() ?>" name="s"/>
            <?php do_action( 'post_search_form_field' ); ?>
            <?php do_action( 'pre_search_form_submit_button' ); ?>
            <button type="submit" class="search_form-submit">
                <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px"
                     fill="#ffffff">
                    <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/>
                </svg>
            </button>
            <?php do_action( 'post_search_form_submit_button' ); ?>
        </div>
    </form>
</div>
