<?php
if (!defined('ABSPATH')) exit;

function anima_register_cpt_proyecto() {
  anima_register_cpt('proyecto', 'Proyecto', 'Proyectos', ['title','editor','thumbnail','excerpt']);
}
