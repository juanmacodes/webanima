<?php
namespace AnimaEngine\Elementor\Widgets;

use Elementor\Widget_Base;

class Share extends Widget_Base {
  public function get_name(){ return 'anima_share'; }
  public function get_title(){ return __('Compartir', 'anima-engine'); }
  public function get_icon(){ return 'eicon-share-arrow'; }
  public function get_categories(){ return ['anima']; }

  protected function render(){
    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    echo '<div class="anima-share">';
    echo '<a href="https://twitter.com/intent/tweet?text='.$title.'&url='.$url.'" target="_blank" rel="noopener">X/Twitter</a>';
    echo '<a href="https://www.linkedin.com/shareArticle?mini=true&url='.$url.'&title='.$title.'" target="_blank" rel="noopener">LinkedIn</a>';
    echo '<a href="https://www.facebook.com/sharer/sharer.php?u='.$url.'" target="_blank" rel="noopener">Facebook</a>';
    echo '</div>';
  }
}