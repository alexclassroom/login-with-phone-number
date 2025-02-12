<?php
class lwp_mellipayamak
{
    function __construct()
    {
        add_action('idehweb_custom_fields', array(&$this, 'admin_init'));
        add_filter('lwp_add_to_default_gateways', array(&$this, 'lwp_add_to_default_gateways'));
        add_action('lwp_send_sms_mellipayamak', array(&$this, 'lwp_send_sms_mellipayamak'), 10, 2);
    }

    function lwp_add_to_default_gateways($args = [])
    {
        if (!is_array($args)) {
            $args = [];
        }
        array_push($args, ["value" => "mellipayamak", "label" => __("Mellipayamak", 'lwp-mellipayamak')]);

        return $args;
    }

    function admin_init()
    {
        add_settings_field('idehweb_mellipayamak_username', __('Enter Mellipayamak username', 'lwp-mellipayamak'), array(&$this, 'setting_idehweb_username'), 'idehweb-lwp', 'idehweb-lwp', ['label_for' => '', 'class' => 'ilwplabel lwp-gateways related_to_mellipayamak']);
        add_settings_field('idehweb_mellipayamak_password', __('Enter Mellipayamak password', 'lwp-mellipayamak'), array(&$this, 'setting_idehweb_password'), 'idehweb-lwp', 'idehweb-lwp', ['label_for' => '', 'class' => 'ilwplabel lwp-gateways related_to_mellipayamak']);
        add_settings_field('idehweb_mellipayamak_from', __('Enter Mellipayamak from', 'lwp-mellipayamak'), array(&$this, 'setting_idehweb_from'), 'idehweb-lwp', 'idehweb-lwp', ['label_for' => '', 'class' => 'ilwplabel lwp-gateways related_to_mellipayamak']);
        add_settings_field('idehweb_mellipayamak_message', __('Enter Mellipayamak message', 'lwp-mellipayamak'), array(&$this, 'setting_idehweb_message'), 'idehweb-lwp', 'idehweb-lwp', ['label_for' => '', 'class' => 'ilwplabel lwp-gateways related_to_mellipayamak']);
    }

    function settings_validate($input)
    {
        // Add any validation rules here if necessary
        return $input;
    }

    function lwp_send_sms_mellipayamak($phone_number, $code)
    {
        $options = get_option('idehweb_lwp_settings');
        $username = isset($options['idehweb_mellipayamak_username']) ? sanitize_text_field($options['idehweb_mellipayamak_username']) : '';
        $password = isset($options['idehweb_mellipayamak_password']) ? sanitize_text_field($options['idehweb_mellipayamak_password']) : '';
        $from = isset($options['idehweb_mellipayamak_from']) ? sanitize_text_field($options['idehweb_mellipayamak_from']) : '';
        $message = isset($options['idehweb_mellipayamak_message']) ? sanitize_textarea_field($options['idehweb_mellipayamak_message']) : '';

        $message = $this->lwp_replace_strings($message, $phone_number, $code);

        // Send SMS via Mellipayamak's new API
        $response = wp_safe_remote_post("http://rest.payamak-panel.com/api/SendSMS/SendSMS", [
            'timeout' => 60,
            'redirection' => 1,
            'blocking' => true,
            'headers' => array('Content-Type' => 'application/json'),
            'body' => wp_json_encode([
                'username' => $username,
                'password' => $password,
                'from' => $from,
                'isflash' => 'false',
                'to' => $phone_number,
                'text' => $message
            ])
        ]);

        $body = wp_remote_retrieve_body($response);
        // Handle response if needed (e.g., log errors)
    }

    public function lwp_replace_strings($string, $phone, $code, $message = '')
    {
        $string = str_replace('${phone_number}', sanitize_text_field($phone), $string);
        $string = str_replace('${code}', sanitize_text_field($code), $string);
        $string = str_replace('${message}', sanitize_text_field($message), $string);

        return $string;
    }

    function setting_idehweb_username()
    {
        $options = get_option('idehweb_lwp_settings');
        $username = isset($options['idehweb_mellipayamak_username']) ? esc_attr($options['idehweb_mellipayamak_username']) : '';
        echo '<input type="text" name="idehweb_lwp_settings[idehweb_mellipayamak_username]" class="regular-text" value="' . $username . '" />';
        echo '<p class="description">' . __('Enter Mellipayamak username', 'lwp-mellipayamak') . '</p>';
    }

    function setting_idehweb_password()
    {
        $options = get_option('idehweb_lwp_settings');
        $password = isset($options['idehweb_mellipayamak_password']) ? esc_attr($options['idehweb_mellipayamak_password']) : '';
        echo '<input type="password" name="idehweb_lwp_settings[idehweb_mellipayamak_password]" class="regular-text" value="' . $password . '" />';
        echo '<p class="description">' . __('Enter Mellipayamak password', 'lwp-mellipayamak') . '</p>';
    }

    function setting_idehweb_from()
    {
        $options = get_option('idehweb_lwp_settings');
        $from = isset($options['idehweb_mellipayamak_from']) ? esc_attr($options['idehweb_mellipayamak_from']) : '';
        echo '<input type="text" name="idehweb_lwp_settings[idehweb_mellipayamak_from]" class="regular-text" value="' . $from . '" />';
        echo '<p class="description">' . __('Enter Mellipayamak from (sender)', 'lwp-mellipayamak') . '</p>';
    }

    function setting_idehweb_message()
    {
        $options = get_option('idehweb_lwp_settings');
        $message = isset($options['idehweb_mellipayamak_message']) ? sanitize_textarea_field($options['idehweb_mellipayamak_message']) : '';
        echo '<textarea name="idehweb_lwp_settings[idehweb_mellipayamak_message]" class="regular-text">' . esc_textarea($message) . '</textarea>';
        echo '<p class="description">' . __('Enter message, use ${code} for the verification code', 'login-with-phone-number') . '</p>';
    }
}

global $lwp_mellipayamak;
$lwp_mellipayamak = new lwp_mellipayamak();

/**
 * Template Tag
 */
function lwp_mellipayamak()
{
    // This function can be further expanded if necessary
}
