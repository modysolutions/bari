<?php if (!defined('ABSPATH')) exit; ?>

<div class="notice notice-error">
    <p>
        <?php if ($acf_missing): ?>
            <?php echo esc_html__('Mody Blocks requires Advanced Custom Fields Pro to be installed and activated.', APP_THEME_DOMAIN); ?>
        <?php endif; ?>
        <?php if ($timber_missing): ?>
            <?php echo sprintf(
                __(
                      'Mody Blocks requires the Timber library to be installed and activated. Or the <a href="%s">%s</a> may be %s',
                      APP_THEME_DOMAIN
                ),
                    APP_THEME_URI,
                    APP_THEME_DOMAIN,
                $theme_missing ?
                    __('installed', APP_THEME_DOMAIN) :
                    __('activated', APP_THEME_DOMAIN)
            ); ?>
        <?php endif; ?>
    </p>
</div>