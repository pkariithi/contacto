<?php

namespace SMVC\Helpers;

use Mpdf\Mpdf;
use Mpdf\HTMLParserMode;

class Pdf {

  private $mpdf;

  public function __construct() {
    $this->mpdf = new Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
  }

  public function exportListing($title, $columns, $rows) {
    $h = '<div class="pdf-page">';
    $h .= $this->getPdfTitle($title);
    $h .= '<div class="pdf-page-body">';
    $h .= $this->getPdfListingTable($columns, $rows);
    $h .= '</div>';
    $h .= '</div>';

    return $this->writePdf($h, $title);
  }

  private function getPdfTitle($title) {
    $t = '<div class="pdf-page-title">';
    $t .= '<h1>'.$title.'</h1>';
    $t .= '<p>PDF export date: '.Date::now('Y-m-d H:i:s').'</p>';
    $t .= '</div>';
    return $t;
  }

  private function getPdfListingTable($columns, $rows) {
    $values = array_keys($columns);
    $columns = array_values($columns);

    if(empty($rows)):
      $t = '<div class="pdf-page-body-empty">';
      $t .= '<p>No records found</p>';
      $t .= '</div>';
    else:
      $t = '<div class="pdf-page-body-table">';
      $t .= '<table><thead><tr>';
      foreach($columns as $column) {
        $t .= '<th>'.$column['label'].'</th>';
      }
      $t .= '</tr></thead><tbody>';
      foreach($rows as $row) {
        $t .= '<tr>';
        foreach($values as $value) {
          $t .= '<td>'.$row->{$value}.'</td>';
        }
        $t .= '</tr>';
      }
      $t .= '</tbody></table></div>';
    endif;
    return $t;
  }

  private function writePdf($html, $title) {
    $css = $this->loadCss();
    $this->mpdf->WriteHTML($css, HTMLParserMode::HEADER_CSS);
    $this->mpdf->WriteHTML($html, HTMLParserMode::HTML_BODY);
    $this->mpdf->Output(Text::slugify($title).'-'.time().'.pdf', 'I');
    exit;
  }

  private function loadCss() {
    $c = 'body {font-family: sans-serif}';
    $c .= 'table, tr, th, td {border: 1px solid #000; border-collapse: collapse}';
    $c .= 'th, td {padding: 10px}';
    return $c;
  }

}
