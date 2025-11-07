<?php
if (!defined('ABSPATH')) exit;

function anima_register_waitlist_route() {
  register_rest_route('anima/v1', '/waitlist', [
    'methods'  => 'POST',
    'callback' => 'anima_waitlist_handler',
    'permission_callback' => '__return_true', // público; añade validación propia
  ]);
}

function anima_waitlist_handler(\WP_REST_Request $request) {
  $email = sanitize_email($request->get_param('email'));
  $name  = sanitize_text_field($request->get_param('name'));

  if (empty($email) || !is_email($email)) {
    return new \WP_Error('invalid_email', 'Email inválido', ['status' => 400]);
  }

  // Aquí puedes guardar en una tabla/option o enviar correo
  $saved = add_post_meta(0, '_anima_waitlist', wp_json_encode([
    'email' => $email,
    'name'  => $name,
    'time'  => current_time('mysql'),
  ]));

  if (!$saved) {
    return new \WP_Error('save_failed', 'No se pudo guardar', ['status' => 500]);
  }

  return new \WP_REST_Response(['ok' => true], 200);
}
