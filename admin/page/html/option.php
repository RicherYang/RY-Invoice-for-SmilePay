<?php defined('ABSPATH') or exit; ?>

<?php
use RY\General\Logs;

?>

<?php $api_info = RY_IFSMILEPAY_Invoice::instance()->get_api_info(); ?>

<h2 class="title"><?php esc_html_e('API credentials', 'ry-invoice-for-smilepay'); ?></h2>

<table class="form-table" role="presentation">
    <tr>
        <th scope="row"><?php esc_html_e('Debug log', 'ry-invoice-for-smilepay'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text"><span><?php esc_html_e('Debug log', 'ry-invoice-for-smilepay'); ?></span></legend>
                <label for="log"><input name="log" type="checkbox" id="log" value="yes" <?php checked(RY_IFSMILEPAY::get_option('log', 'no') === 'yes'); ?>>
                    <?php esc_html_e('Enable log', 'ry-invoice-for-smilepay'); ?></label>
                <p class="description">
                    <?php echo wp_kses(
                        __('<strong>Note:</strong> The log may contain personal information.', 'ry-invoice-for-smilepay'),
                        ['strong' => []]
                    ); ?>
                </p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php esc_html_e('Sandbox', 'ry-invoice-for-smilepay'); ?></th>
        <td>
            <fieldset>
                <legend class="screen-reader-text"><span><?php esc_html_e('Sandbox', 'ry-invoice-for-smilepay'); ?></span></legend>
                <label for="testmode"><input name="testmode" type="checkbox" id="testmode" value="yes" <?php checked($api_info['testmode']); ?>>
                    <?php esc_html_e('Enable sandbox', 'ry-invoice-for-smilepay'); ?></label>
                <p class="description"><?php esc_html_e('Note: For developers use ONLY.', 'ry-invoice-for-smilepay'); ?></p>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="Grvc"><?php esc_html_e('Grvc', 'ry-invoice-for-smilepay'); ?></label></th>
        <td><input name="Grvc" type="text" id="Grvc" value="<?php echo esc_attr($api_info['Grvc']); ?>" class="regular-text"></td>
    </tr>
    <tr>
        <th scope="row"><label for="VerifyKey"><?php esc_html_e('Verify key', 'ry-invoice-for-smilepay'); ?></label></th>
        <td><input name="VerifyKey" type="text" id="VerifyKey" value="<?php echo esc_attr($api_info['VerifyKey']); ?>" class="regular-text"></td>
    </tr>
</table>
