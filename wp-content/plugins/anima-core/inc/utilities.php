<?php
if (!defined('ABSPATH')) exit;

function anima_admin_notice($msg, $type = 'error') {
  add_action('admin_notices', function() use ($msg, $type) {
    printf('<div class="notice notice-%s"><p>%s</p></div>', esc_attr($type), wp_kses_post($msg));
  });
}
