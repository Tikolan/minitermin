<?php
/*

Plugin URI: https://www.wiesser.at/wordpress/plugins/minitermin
Description: Ein einfaches Plugin Serientermin
Version: 1.0.0
Author: Franz Wieser
Author URI: https://www.wieser.at
License: GPL2
*/
function minitermin_serie_assets() {
    wp_register_script(
        'minitermin-block-serie',
        plugins_url('block-serie.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'block-serie.js')
    );
    register_block_type('minitermin/serie', array(
        'editor_script' => 'minitermin-block-serie',
        'render_callback' => 'minitermin_render_serie_block',
        'attributes' => array(
            'startDate' => array('type' => 'string', 'default' => ''),
            'endDate' => array('type' => 'string', 'default' => ''),
            'timeFrom' => array('type' => 'string', 'default' => ''),
            'timeTo' => array('type' => 'string', 'default' => ''),
            'location' => array('type' => 'string', 'default' => ''),
            'repeatType' => array('type' => 'string', 'default' => 'daily'),
            'repeatEvery' => array('type' => 'number', 'default' => 1), // z.B. 1 für täglich, 2 für alle 2 Tage
            'weekday' => array('type' => 'string', 'default' => 'MO'), 
            'monthday' => array('type' => 'string', 'default' => 1), 
            'monthlyWeek' => array('type' => 'string', 'default' => '1'), // 1,2,3,4,5,last
            'monthlyWeekday' => array('type' => 'string', 'default' => 'MO'), // MO, TU, ...
        ),
        'supports' => array(
        'align' => true,
        'customClassName' => true,
        'color' => array(
            'background' => true,
            'text' => true,
            'gradients' => true,
        ),
        'typography' => array(
            'fontSize' => true,
            'lineHeight' => true,
        ),
        'spacing' => array(
            'padding' => true,
            'margin' => true,
        ),
            // ... weitere Support-Flags wie 'dimensions', 'border' etc.
        ),
    ));
}


add_action('init', 'minitermin_serie_assets');


function minitermin_render_serie_block($attributes) {
    $start = $attributes['startDate'] ?? '';
    $end = $attributes['endDate'] ?? '';
    $timeFrom = $attributes['timeFrom'] ?? '';
    $timeTo = $attributes['timeTo'] ?? '';
    $repeatType = $attributes['repeatType'] ?? 'daily';
    $repeatEvery = intval($attributes['repeatEvery'] ?? 1);
    $weekday = $attributes['weekday'] ?? 'MO';
    $location = $attributes['location'] ?? '';
    $monthlyWeek = $attributes['monthlyWeek'] ?? '1'; // z.B. '1', '2', ..., 'last'
    $monthlyWeekday = $attributes['monthlyWeekday'] ?? 'MO'; // z.B. 'MO'
    $monthlyWeekdayLabels = $attributes['monthlyWeekdayLabels'] ?? '1'; // z.B. 'MO'
    $repeatTypeLabels = $attributes['repeatTypeLabels'] ?? 'daily';
    $weekdayMap = [
        'MO' => 1, 'DI' => 2, 'MI' => 3, 'DO' => 4, 'FR' => 5, 'SA' => 6, 'SO' => 0
    ];
    $wanted = $weekdayMap[$monthlyWeekday] ?? 1;
    $repeatTypeLabels = [
                'daily' => 'täglich',
                'weekly' => 'wöchentlich',
                'monthly' => 'monatlich',
                'yearly' => 'jährlich'
            ];
        $monthlyWeekLabels = [
                '1' => 'ersten',
                '2' => 'zweiten',
                '3' => 'dritten',
                '4' => 'vierten',
                '5' => 'fünften',
                'last' => 'letzten'
            ];
            $weekdayLabels = [
                'MO' => 'Montag',
                'DI' => 'Dienstag',
                'MI' => 'Mittwoch',
                'DO' => 'Donnerstag',
                'FR' => 'Freitag',
                'SA' => 'Samstag',
                'SO' => 'Sonntag'
            ];

$desc = 'Serientermin front: ';
$desc .= esc_html($start) . ' bis ' . esc_html($end) . ' ';
$desc .= esc_html($timeFrom) . ' - ' . esc_html($timeTo) . ' ';
$desc .= $repeatTypeLabels[$repeatType] ?? $repeatType;
$desc .= ' alle ' . $repeatEvery .' '.$repeatTypeLabels[$repeatType];

    if (!$start || !$end) return '<div>Bitte Start- und Enddatum wählen.</div>';
    
    $dates = [];
    $current = strtotime($start);
    $end_ts = strtotime($end);

if ($repeatType === 'daily') {
    while ($current <= $end_ts) {
        $dates[] = date('Y-m-d', $current);
        $current += 86400 * $repeatEvery;
    }
} elseif ($repeatType === 'weekly') {
    // Ermittle den ersten gewünschten Wochentag ab $current
    $weekdayMap = [
        'MO' => 1, 'TU' => 2, 'WE' => 3, 'TH' => 4, 'FR' => 5, 'SA' => 6, 'SU' => 0
    ];
    $wanted = $weekdayMap[$weekday] ?? 1;
    $currentWeek = $current;
    while ($currentWeek <= $end_ts) {
        // Finde den nächsten gewünschten Wochentag in dieser Woche
        $day = $currentWeek;
        for ($i = 0; $i < 7; $i++) {
            if ((int)date('w', $day) === $wanted) {
                if ($day >= $current && $day <= $end_ts) {
                    $dates[] = date('Y-m-d', $day);
                }
                break;
            }
            $day = strtotime('+1 day', $day);
        }
        // Springe um $repeatEvery Wochen weiter
        $currentWeek = strtotime("+$repeatEvery week", $currentWeek);
    }
} elseif ($repeatType === 'monthly') {
    $monthlyWeek = $attributes['monthlyWeek'] ?? '1';
    $monthlyWeekday = $attributes['monthlyWeekday'] ?? 'MO';
    $weekdayMap = [
        'MO' => 1, 'DI' => 2, 'MI' => 3, 'DO' => 4, 'FR' => 5, 'SA' => 6, 'SO' => 0
    ];
    $wanted = $weekdayMap[$monthlyWeekday] ?? 1;

    $year = date('Y', strtotime($start));
    $month = date('n', strtotime($start));
    while ($current <= $end_ts) {

        $first_day = strtotime("$year-$month-01");
        $last_day = strtotime(date('Y-m-t', $first_day));
        $dates_in_month = [];
        for ($d = $first_day; $d <= $last_day; $d = strtotime('+1 day', $d)) {
            if ((int)date('w', $d) === $wanted) {
                $dates_in_month[] = $d;
            }
        }
        if (!empty($dates_in_month)) {
            if ($monthlyWeek === 'last') {
                $target = end($dates_in_month);
            } else {
                $idx = intval($monthlyWeek) - 1;
                $target = $dates_in_month[$idx] ?? null;
            }
            if ($target && $target >= strtotime($start) && $target <= strtotime($end)) {
                $dates[] = date('Y-m-d', $target);
            }
        }
        // Zum nächsten Monat springen
        $month += $repeatEvery;
        if ($month > 12) {
            $year += floor(($month - 1) / 12);
            $month = (($month - 1) % 12) + 1;
        }
        if (strtotime("$year-$month-01") > strtotime($end)) {
            break;
        }
    }
 } elseif ($repeatType === 'yearly') {
    while ($current <= $end_ts) {
        $dates[] = date('Y-m-d', $current);
        $current = strtotime("+$repeatEvery year", $current);
    }
}

    ob_start();
echo '<div class="minitermin-serie-desc">' . $desc . '</div>';
echo '<ul class="minitermin-serie">Anzeige';

if (empty($dates)) {
    echo '<li>' . __('Keine Termine gefunden.', 'minitermin') . '</li>';
    echo '</ul>';
    return ob_get_clean();
}
foreach ($dates as $date) {
$now = strtotime(date('Y-m-d'));
    $bis = $date;
  //  $bis = $date['datumBis'] ?? $date['datumVon'];
  
    $timestampBis = strtotime($bis);   
     echo '<li>';
    if ($timestampBis < $now) {
        if (!empty($attributes['showExpiredText'])) {
            echo '<div class="minitermin-expired">' . esc_html($attributes['expiredText'] ?? 'Termin abgelaufen') . '</div>';
        }
    }
    echo esc_html($monthlyWeekday.' '.$date . ' ' . $timeFrom . ' - ' . $timeTo . ($location ? ' | ' . $location : ''));
    echo '</li>';
}
echo '</ul>';
return ob_get_clean();
}

function minitermin_berechne_serientermine($atts) {
    $start = $atts['startDate'] ?? '';
    $end = $atts['endDate'] ?? '';
    $timeFrom = $atts['timeFrom'] ?? '';
    $timeTo = $atts['timeTo'] ?? '';
    $repeatType = $atts['repeatType'] ?? 'daily';
    $repeatEvery = intval($atts['repeatEvery'] ?? 1);
    $weekday = $atts['weekday'] ?? 'MO';
    $monthlyWeek = $atts['monthlyWeek'] ?? '1';
    $monthlyWeekday = $atts['monthlyWeekday'] ?? 'MO';
    $location = $atts['location'] ?? '';
    $dates = [];
    if (!$start || !$end) return $dates;
    $current = strtotime($start);
    $end_ts = strtotime($end);

    // Täglich
    if ($repeatType === 'daily') {
        while ($current <= $end_ts) {
            $dates[] = [
                'datumVon' => date('Y-m-d', $current),
                'datumBis' => date('Y-m-d', $current),
                'zeitVon' => $timeFrom,
                'zeitBis' => $timeTo,
                'ort' => $location,
            ];
            $current += 86400 * $repeatEvery;
        }
    }
    // Wöchentlich
    elseif ($repeatType === 'weekly') {
        $weekdayMap = [
            'MO' => 1, 'DI' => 2, 'MI' => 3, 'DO' => 4, 'FR' => 5, 'SA' => 6, 'SO' => 0
        ];
        $wanted = $weekdayMap[$weekday] ?? 1;
        $currentWeek = $current;
        while ($currentWeek <= $end_ts) {
            $day = $currentWeek;
            for ($i = 0; $i < 7; $i++) {
                if ((int)date('w', $day) === $wanted) {
                    if ($day >= $current && $day <= $end_ts) {
                        $dates[] = [
                            'datumVon' => date('Y-m-d', $day),
                            'datumBis' => date('Y-m-d', $day),
                            'zeitVon' => $timeFrom,
                            'zeitBis' => $timeTo,
                            'ort' => $location,
                        ];
                    }
                    break;
                }
                $day = strtotime('+1 day', $day);
            }
            $currentWeek = strtotime("+$repeatEvery week", $currentWeek);
        }
    }
    // Monatlich (jeden x-ten Wochentag)
    elseif ($repeatType === 'monthly') {
        $weekdayMap = [
            'MO' => 1, 'DI' => 2, 'MI' => 3, 'DO' => 4, 'FR' => 5, 'SA' => 6, 'SO' => 0
        ];
        $wanted = $weekdayMap[$monthlyWeekday] ?? 1;
        $year = date('Y', strtotime($start));
        $month = date('n', strtotime($start));
        while (true) {
            $first_day = strtotime("$year-$month-01");
            $last_day = strtotime(date('Y-m-t', $first_day));
            $dates_in_month = [];
            for ($d = $first_day; $d <= $last_day; $d = strtotime('+1 day', $d)) {
                if ((int)date('w', $d) === $wanted) {
                    $dates_in_month[] = $d;
                }
            }
            if (!empty($dates_in_month)) {
                if ($monthlyWeek === 'last') {
                    $target = end($dates_in_month);
                } else {
                    $idx = intval($monthlyWeek) - 1;
                    $target = $dates_in_month[$idx] ?? null;
                }
                if ($target && $target >= strtotime($start) && $target <= strtotime($end)) {
                    $dates[] = [
                        'datumVon' => date('Y-m-d', $target),
                        'datumBis' => date('Y-m-d', $target),
                        'zeitVon' => $timeFrom,
                        'zeitBis' => $timeTo,
                        'ort' => $location,
                    ];
                }
            }
            $month += $repeatEvery;
            if ($month > 12) {
                $year += floor(($month - 1) / 12);
                $month = (($month - 1) % 12) + 1;
            }
            if (strtotime("$year-$month-01") > $end_ts) {
                break;
            }
        }
    }
    // Jährlich
    elseif ($repeatType === 'yearly') {
        while ($current <= $end_ts) {
            $dates[] = [
                'datumVon' => date('Y-m-d', $current),
                'datumBis' => date('Y-m-d', $current),
                'zeitVon' => $timeFrom,
                'zeitBis' => $timeTo,
                'ort' => $location,
            ];
            $current = strtotime("+$repeatEvery year", $current);
        }
    }
    return $dates;
}
