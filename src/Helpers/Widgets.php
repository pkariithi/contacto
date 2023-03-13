<?php

namespace SMVC\Helpers;

class Widgets {

  public static function clock() {
    $datetime = new \DateTime();
    $dt = (object) [
      'date_day' => $datetime->format('l'),
      'date_datetime' => $datetime->format('jS F Y'),
      'hours' => $datetime->format('H'),
      'minutes' => $datetime->format('i'),
      'seconds' => $datetime->format('s')
    ];

    // format widget
    return <<<END
      <div class="dashboard-widget dashboard-widget-clock">
        <div id="dashboard-widget-clock-date">
          <span>{$dt->date_day}</span>
          <span class="comma-sep">, </span>
          <span>{$dt->date_datetime}</span>
        </div>
        <ul>
          <li id="dashboard-widget-clock-hours">{$dt->hours}</li>
          <li class="dashboard-widget-clock-point">:</li>
          <li id="dashboard-widget-clock-minutes">{$dt->minutes}</li>
          <li class="dashboard-widget-clock-point">:</li>
          <li id="dashboard-widget-clock-seconds">{$dt->seconds}</li>
        </ul>
      </div>
    END;
  }

}
