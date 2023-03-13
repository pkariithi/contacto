<?php

namespace SMVC\Helpers;

class Pager {

  public static function paginate($count, $page, $pages, $url = '', $get = '', $options = []) {

    // trim left / from url
    $url = ltrim($url, '/');

    // empty labels
    if(!isset($options['labels']['next'])) { $options['labels']['next'] = 'Next &rarr;'; }
    if(!isset($options['labels']['prev'])) { $options['labels']['prev'] = '&larr; Prev'; }

    if(!isset($options['showCount'])) { $options['showCount'] = true; }
    if(!isset($options['showButtons'])) { $options['showButtons'] = true; }

    if(!isset($options['name']['singular'])) { $options['name']['singular'] = 'row'; }
    if(!isset($options['name']['plural'])) { $options['name']['plural'] = 'rows'; }

    // variables
    $adjacent = 2;
    $prev = $page - 1;
    $next = $page + 1;

    // structure
    $structure = [];

    // show all pages
    if($pages <= (($adjacent * 2) + 1)) {
      $structure = range(1, $pages);
    } else {

      // first section
      $second = '-';
      if($page - ($adjacent + 1) <= 1) {
        $second = 2;
      }
      $first = [1, $second];

      // last section
      $secondlast = '-';
      if($page + ($adjacent + 1) == $pages) {
        $secondlast = $pages - 1;
      }
      $last = [$secondlast, $pages];

      // mid section low
      $low = $page - 2;
      if(($page - ($adjacent + 2)) <= 1) {
        $first = [];
        $low = 1;
      }

      // mid section high
      $high = $page + 2;
      if(($page + $adjacent + 2) >= $pages) {
        $last = [];
        $high = $pages;
      }

      // create skeleton
      $structure = array_merge($first, range($low, $high), $last);
    }

    // create links
    $html = '<div class="paginate"><ul>';
    foreach($structure as $i) {
      if(is_numeric($i)) {
        if($page == $i) {
          $html .= '<li><span class="active">'.$i.'</span></li>';
        } else {
          $html .= '<li><a href="'.$url.$i.$get.'" class="button">'.$i.'</a></li>';
        }
      } else {
        $html .= '<li><span class="sep">&hellip;</span></li>';
      }
    }
    $html .= '</ul>';

    if($options['showCount']) {
      $count_text = $count == 1 ? $count.' '.$options['name']['singular'].' found' : $count.' '.$options['name']['plural'].' found';
      $html .= '<p class="paginate-count">'.$count_text.'</p>';
    }


    if($options['showButtons']) {
      $html .= '<p class="paginate-buttons">';
      if($page > 1) {
        $html .= '<a href="'.$url.$prev.$get.'" class="button">'.$options['labels']['prev'].'</a>';
      }
      if($pages > $page) {
        $html .= '<a href="'.$url.$next.$get.'" class="button">'.$options['labels']['next'].'</a>';
      }
      $html .= '</p>';
    }

    $html .= '</div>';
    return $html;
  }

}
