<?php
/*
Plugin Name: Minitermin
Plugin URI: https://www.wiesser.at/wordpress/plugins/minitermin
Description: Ein einfaches Plugin für Terminverwaltung.
Version: 1.0.0
Author: Franz Wieser
Author URI: https://www.wieser.at
License: GPL2
*/
// Block Assets laden

require_once plugin_dir_path(__FILE__) . 'miniterminserie.php';

function minitermin_block_assets() {
    wp_register_script(
        'minitermin-block',
        plugins_url('block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'block.js')
    );
    register_block_type('minitermin/block', array(
        'editor_script' => 'minitermin-block',
        'render_callback' => 'minitermin_render_block',
        'attributes' => array(
            'datumVon' => array('type' => 'string', 'default' => ''),
            'datumBis' => array('type' => 'string', 'default' => ''),
            'zeitVon' => array('type' => 'string', 'default' => ''),
            'zeitBis' => array('type' => 'string', 'default' => ''),
            'ort' => array('type' => 'string', 'default' => ''),
            'showExpiredText' => array('type' => 'boolean', 'default' => false),
            'expiredText' => array('type' => 'string', 'default' => 'Termin abgelaufen'),
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
add_action('init', 'minitermin_block_assets');




function minitermin_render_block($attributes) {
    $now = strtotime(date('Y-m-d'));
    $datumBis = $attributes['datumBis'] ? $attributes['datumBis']  : $attributes['datumVon'];
    $timestampBis = strtotime($datumBis);

    // Optionale Ersatztext-Logik
    $show_expired_text = !empty($attributes['showExpiredText']);
    $expired_text = !empty($attributes['expiredText']) ? $attributes['expiredText'] : 'Termin abgelaufen';
    // Wenn Termin abgelaufen
    if ($timestampBis && $timestampBis < $now) {
        if ($show_expired_text) {
            return '<div class="minitermin-expired">' . esc_html($expired_text) . '</div>';
        }
        return ''; // Block bleibt leer
    }

    ob_start();
    ?>
    <div class="minitermin-block">
        <strong>Termin:</strong><br>
        <?php if (!empty($attributes['datumVon'])): ?>
            <span>Von: <?php echo esc_html($attributes['datumVon']); ?></span><br>
        <?php endif; ?>
        <?php if (!empty($attributes['datumBis'])): ?>
            <span>Bis: <?php echo esc_html($attributes['datumBis']); ?></span><br>
        <?php endif; ?>
        <?php if (!empty($attributes['zeitVon'])): ?>
            <span>Zeit von: <?php echo esc_html($attributes['zeitVon']); ?></span><br>
        <?php endif; ?>
        <?php if (!empty($attributes['zeitBis'])): ?>
            <span>Zeit bis: <?php echo esc_html($attributes['zeitBis']); ?></span><br>
        <?php endif; ?>
        <?php if (!empty($attributes['ort'])): ?>
            <span>Ort: <?php echo esc_html($attributes['ort']); ?></span>
        <?php endif; ?>
    </div>
    <div>
        <?php
            // Im Render-Callback von minitermin_render_block
            // Hinweis: $block_index ist hier nicht verfügbar, daher wird der Link nicht angezeigt.
            // Um einen korrekten Link zu generieren, muss $block_index beim Rendern übergeben werden.
             $ical_url = add_query_arg(array(
                 'post_id' => get_the_ID(),
                 //'block_index' => $block_index
             ), rest_url('minitermin/v1/ical'));
          
            ?>
            <a href="<?php   echo esc_url($ical_url); ?>">iCal für diesen Termin</a>
    </div>
    <?php
    return ob_get_clean();
}
function minitermin_liste_block_assets() {
    wp_register_script(
        'minitermin-block-liste',
        plugins_url('block-liste.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'block-liste.js')
    );
    register_block_type('minitermin/liste', array(
        'editor_script' => 'minitermin-block-liste',
        'render_callback' => 'minitermin_render_liste_block',
        'attributes' => array(
            'categories' => array('type' => 'array', 'default' => array()),
            'postType' => array('type' => 'string', 'default' => 'post'),
            'sortOrder' => array('type' => 'string', 'default' => 'upcoming'),
            'showType' => array('type' => 'string', 'default' => 'all'), // all, upcoming, past
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
      // 'render_callback' => 'minitermin_render_liste_block',
    ));
}
add_action('init', 'minitermin_liste_block_assets');


function minitermin_ical_link_block_assets() {
    wp_register_script(
        'minitermin-block-ical-link',
        plugins_url('block-ical.js', __FILE__),
        array('wp-blocks', 'wp-element'),
        filemtime(plugin_dir_path(__FILE__) . 'block-ical.js')
    );
    register_block_type('minitermin/ical-link', array(
        'editor_script' => 'minitermin-block-ical-link',
        'render_callback' => function() {
            $ical_url = add_query_arg(
                array('post_id' => get_the_ID()),
                rest_url('minitermin/v1/ical-all')
            );
            return '<a href="' . esc_url($ical_url) . '">iCal für alle Termine dieses Beitrags</a>';
        }
    ));
}
add_action('init', 'minitermin_ical_link_block_assets');

function minitermin_render_liste_block($attributes) {
    $categories = $attributes['categories'] ?? array();
    $post_type = $attributes['postType'] ?? 'post';
    $sort_order = $attributes['sortOrder'] ?? 'asc';
    $show_type = $attributes['showType'] ?? 'all';

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'category__in' => !empty($categories) ? $categories : '',
    );
    $query = new WP_Query($args);
    $termine = array();

    foreach ($query->posts as $post) {
        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'minitermin/block') {
                $atts = $block['attrs'];
                $datumVon = $atts['datumVon'] ?? '';
                $timestamp = strtotime($datumVon);
                $termine[] = array(
                    'title' => get_the_title($post),
                    'datumVon' => $datumVon,
                    'datumBis' => $atts['datumBis'] ?? '',
                    'zeitVon' => $atts['zeitVon'] ?? '',
                    'zeitBis' => $atts['zeitBis'] ?? '',
                    'ort' => $atts['ort'] ?? '',
                    'permalink' => get_permalink($post),
                    'timestamp' => $timestamp,
                );
            }
            if ($block['blockName'] === 'minitermin/serie') {
        $atts = $block['attrs'];
        // Hole alle Einzeltermine aus dem Serientermin-Block
        $serieTermine = minitermin_berechne_serientermine($atts);
        foreach ($serieTermine as $serientermin) {
            $termine[] = array(
                'title' => get_the_title($post),
                'datumVon' => $serientermin['datumVon'],
                'datumBis' => $serientermin['datumBis'],
                'zeitVon' => $serientermin['zeitVon'],
                'zeitBis' => $serientermin['zeitBis'],
                'ort' => $serientermin['ort'],
                'permalink' => get_permalink($post),
                'timestamp' => strtotime($serientermin['datumVon']),
            );
        }
    }
        }
    }

    // Filter nach Zeitraum
   $now = strtotime(date('Y-m-d'));
if ($show_type === 'upcoming') {
    $termine = array_filter($termine, function($t) use ($now) {
        // Nur Termine mit gültigem Datum und in der Zukunft
        return !empty($t['datumVon']) && $t['timestamp'] && $t['timestamp'] >= $now;
    });
} elseif ($show_type === 'past') {
    $termine = array_filter($termine, function($t) use ($now) {
        // Nur Termine mit gültigem Datum und in der Vergangenheit
        return !empty($t['datumVon']) && $t['timestamp'] && $t['timestamp'] < $now;
    });
}

    // Sortierung
   usort($termine, function($a, $b) use ($sort_order) {
        if ($sort_order === 'upcoming' || $sort_order === 'asc') {
            return $a['timestamp'] <=> $b['timestamp'];
        } else {
            return $b['timestamp'] <=> $a['timestamp'];
        }
    });

    if (empty($termine)) {
        return '<p>Keine Termine gefunden.</p>';
    }

    ob_start();
    ?>
    <ul class="minitermin-liste">
        <?php foreach ($termine as $termin): ?>
            <li>
                <a href="<?php echo esc_url($termin['permalink']); ?>">
                    <?php echo esc_html($termin['title']); ?>
                </a>
                <?php if ($termin['datumVon']): ?>
                    <br>Von: <?php echo esc_html($termin['datumVon']); ?>
                <?php endif; ?>
                <?php if ($termin['datumBis']): ?>
                    <br>Bis: <?php echo esc_html($termin['datumBis']); ?>
                <?php endif; ?>
                <?php if ($termin['zeitVon']): ?>
                    <br>Zeit von: <?php echo esc_html($termin['zeitVon']); ?>
                <?php endif; ?>
                <?php if ($termin['zeitBis']): ?>
                    <br>Zeit bis: <?php echo esc_html($termin['zeitBis']); ?>
                <?php endif; ?>
                <?php if ($termin['ort']): ?>
                    <br>Ort: <?php echo esc_html($termin['ort']); ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    $ical_url = add_query_arg(
    array(
        'categories' => implode(',', $categories),
        'postType' => $post_type,
        'sortOrder' => $sort_order,
        'showType' => $show_type,
    ),
    rest_url('minitermin/v1/ical-liste')
);
echo '<div><a href="'.esc_url($ical_url) . '">iCal für diese Terminliste</a></div>';
    return ob_get_clean();
}


add_action('rest_api_init', function() {
    register_rest_route('minitermin/v1', '/ical', array(
        'methods' => 'GET',
        'callback' => 'minitermin_ical_single',
    ));
    register_rest_route('minitermin/v1', '/ical-all', array(
        'methods' => 'GET',
        'callback' => 'minitermin_ical_all',
    ));
});

function minitermin_ical_single($request) {

    $post_id = intval($request->get_param('post_id'));
    $block_index = intval($request->get_param('block_index'));
   $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('not_found', 'Beitrag nicht gefunden', array('status' => 404));
    }

    $blocks = parse_blocks($post->post_content);
    $block = $blocks[$block_index] ?? null;
    if (!$block || $block['blockName'] !== 'minitermin/block') {
        return new WP_Error('not_found', 'Termin nicht gefunden', array('status' => 404));
    }

    $atts = $block['attrs'];
    $summary = get_the_title($post);
    $location = $atts['ort'] ?? '';
    $date = $atts['datumVon'] ?? '';
    $time = $atts['zeitVon'] ?? '00:00';
    $enddate = $atts['datumBis'] ?? $date;
    $endtime = $atts['zeitBis'] ?? $time;

    // iCal-Datumsformat: YYYYMMDDTHHMMSSZ
    $dtstart = date('Ymd\THis\Z', strtotime($date . ' ' . $time));
    $dtend = date('Ymd\THis\Z', strtotime($enddate . ' ' . $endtime));

    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//Minitermin//DE\r\n";
    $ical .= "BEGIN:VEVENT\r\n";
    $ical .= "SUMMARY:" . esc_html($summary) . "\r\n";
    $ical .= "DTSTART:" . $dtstart . "\r\n";
    $ical .= "DTEND:" . $dtend . "\r\n";
    $ical .= "LOCATION:" . esc_html($location) . "\r\n";
    $ical .= "END:VEVENT\r\n";
    $ical .= "END:VCALENDAR\r\n";

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="termin.ics"');
    echo $ical;
    exit;

}

function minitermin_ical_all($request) {
    $post_id = intval($request->get_param('post_id'));
    $post = get_post($post_id);
    if (!$post) {
        return new WP_Error('not_found', 'Beitrag nicht gefunden', array('status' => 404));
    }

    $blocks = parse_blocks($post->post_content);

    $ical = "BEGIN:VCALENDAR\r\n";
    $ical .= "VERSION:2.0\r\n";
    $ical .= "PRODID:-//Minitermin//DE\r\n";

    foreach ($blocks as $block) {
        if ($block['blockName'] === 'minitermin/block') {
            $atts = $block['attrs'];
            $summary = get_the_title($post);
            $location = $atts['ort'] ?? '';
            $date = $atts['datumVon'] ?? '';
            $time = $atts['zeitVon'] ?? '00:00';
            $enddate = $atts['datumBis'] ?? $date;
            $endtime = $atts['zeitBis'] ?? $time;

            $dtstart = date('Ymd\THis\Z', strtotime($date . ' ' . $time));
            $dtend = date('Ymd\THis\Z', strtotime($enddate . ' ' . $endtime));

            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "SUMMARY:" . esc_html($summary) . "\r\n";
            $ical .= "DTSTART:" . $dtstart . "\r\n";
            $ical .= "DTEND:" . $dtend . "\r\n";
            $ical .= "LOCATION:" . esc_html($location) . "\r\n";
            $ical .= "END:VEVENT\r\n";
        }
    }

    $ical .= "END:VCALENDAR\r\n";

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="termine.ics"');
    echo $ical;
    exit;
}
add_action('rest_api_init', function() {
    register_rest_route('minitermin/v1', '/ical-liste', array(
        'methods' => 'GET',
        'callback' => 'minitermin_ical_liste',
    ));
});

function minitermin_ical_liste($request) {
    $categories = array_filter(array_map('intval', explode(',', $request->get_param('categories') ?? '')));
    $post_type = $request->get_param('postType') ?: 'post';
    $sort_order = $request->get_param('sortOrder') ?: 'asc';
    $show_type = $request->get_param('showType') ?: 'all';

    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );
    if (!empty($categories)) {
        $args['category__in'] = $categories;
    }
    $query = new WP_Query($args);
    $termine = array();

    foreach ($query->posts as $post) {
        $blocks = parse_blocks($post->post_content);
        foreach ($blocks as $block) {
            if ($block['blockName'] === 'minitermin/block') {
                $atts = $block['attrs'];
                $datumVon = $atts['datumVon'] ?? '';
                $timestamp = strtotime($datumVon);
                $termine[] = array(
                    'title' => get_the_title($post),
                    'datumVon' => $datumVon,
                    'datumBis' => $atts['datumBis'] ?? '',
                    'zeitVon' => $atts['zeitVon'] ?? '',
                    'zeitBis' => $atts['zeitBis'] ?? '',
                    'ort' => $atts['ort'] ?? '',
                    'timestamp' => $timestamp,
                );
            }
        }
    }

    $now = strtotime(date('Y-m-d'));
    if ($show_type === 'upcoming') {
        $termine = array_filter($termine, function($t) use ($now) {
            return !empty($t['datumVon']) && $t['timestamp'] && $t['timestamp'] >= $now;
        });
    } elseif ($show_type === 'past') {
        $termine = array_filter($termine, function($t) use ($now) {
            return !empty($t['datumVon']) && $t['timestamp'] && $t['timestamp'] < $now;
        });
    }

    usort($termine, function($a, $b) use ($sort_order) {
        if ($sort_order === 'asc') {
            return $a['timestamp'] <=> $b['timestamp'];
        } else {
            return $b['timestamp'] <=> $a['timestamp'];
        }
    });

    $ical = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Minitermin//DE\r\n";
    foreach ($termine as $termin) {
        $dtstart = date('Ymd\THis\Z', strtotime($termin['datumVon'] . ' ' . ($termin['zeitVon'] ?? '00:00')));
        $dtend = date('Ymd\THis\Z', strtotime(($termin['datumBis'] ?: $termin['datumVon']) . ' ' . ($termin['zeitBis'] ?? $termin['zeitVon'] ?? '00:00')));
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "SUMMARY:" . esc_html($termin['title']) . "\r\n";
        $ical .= "DTSTART:" . $dtstart . "\r\n";
        $ical .= "DTEND:" . $dtend . "\r\n";
        $ical .= "LOCATION:" . esc_html($termin['ort']) . "\r\n";
        $ical .= "END:VEVENT\r\n";
    }
    $ical .= "END:VCALENDAR\r\n";

    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="terminliste.ics"');
    echo $ical;
    exit;
}
