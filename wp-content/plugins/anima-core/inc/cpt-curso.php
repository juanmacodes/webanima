<?php
if (!defined('ABSPATH')) exit;

function anima_register_cpt_curso() {
  anima_register_cpt('curso', 'Curso', 'Cursos', ['title','editor','thumbnail','excerpt']);
}
