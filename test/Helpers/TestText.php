<?php

use PHPUnit\Framework\TestCase;
use SMVC\Helpers\Text;

class TestText extends TestCase {

  public function testExcerpt() {
    $text = '<h1>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</h1><p>Donec dui elit, cursus sit amet tellus volutpat, tempus mattis leo. Sed tempor nulla a ipsum posuere, nec vehicula tortor ultrices. Phasellus ante est, consectetur et arcu vitae, tincidunt sodales orci. Mauris porta volutpat lacus, vitae dignissim sapien imperdiet a. Nullam tincidunt ex sem, eget rutrum lectus feugiat a. Pellentesque vel vulputate ipsum, vitae tempus velit. Aenean pretium maximus maximus.</p><ul><li>Vestibulum vitae dapibus augue.</li><li>Fusce eget arcu ac nisi bibendum varius a at ligula.</li><li>Cras egestas bibendum purus ut venenatis. Quisque in ornare quam.</li></ul><h2>Vestibulum neque quam, semper ut tellus in, euismod convallis justo.</h2><p>Maecenas dignissim sodales arcu, non imperdiet nibh mollis sit amet.</p><p>Donec ornare fermentum ipsum a vestibulum. Nulla pellentesque id est et scelerisque. Suspendisse condimentum erat non iaculis vehicula. Donec lobortis libero vitae bibendum ultrices. Cras gravida aliquam eleifend. Nam laoreet volutpat urna in finibus.</p>';

    $this->assertEquals(
      'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec dui elit, cursus sit amet tellus volutpat, tempus mattis leo. Sed tempor nulla a ipsum posuere, nec vehicula tortor ultrices. Phasellus',
      Text::excerpt($text, 200, null)
    );
  }

  /**
   * @dataProvider providerAvatarize
   */
  public function testAvatarize($n, $len, $result) {
    $this->assertEquals($result, Text::avatarize($n, $len));
  }

  public function providerAvatarize() {
    return [
      ['Lorem Ipsum', 1, 'L'],
      ['Lorem Ipsum Dolor', 2, 'LD'],
      ['Lorem', 4, 'L'],
      ['Lorem Ipsum Dolor Amet', 2, 'LA'],
    ];
  }

}
