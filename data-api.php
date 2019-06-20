<?php
if(!isset($_GET['type'])) $_GET['type'] = 'all';
if(!isset($_GET['query'])) $_GET['query'] = 'init';
else if($_GET['query'] != 'init'){
    $queryTmp = explode( ",", $_GET['query'] );
    $query = array();
    for($i = 0;$i < 2;$i++){
        array_push($query,explode( "@", $queryTmp[$i] ));
    }
    //var_dump($query);
}



if($_GET['type'] == 'all') $type = ['restroom','elevator','food','convenience','amusement'];
else $type = explode(',',$_GET['type']);

$returnArray = array();

$api_query = new WP_Query(
    array(
        'post_type' => $type
    )
);
if ( $api_query->have_posts() ) {
    while ( $api_query->have_posts() ) {
        $api_query->the_post();
        
        $postType = get_post_type();
        $arrayTmp = array();


        //共通データ（リスト状態）
        $arrayTmp['post_type'] = $postType;
        //$arrayTmp['url'] = the_permalink();
        $arrayTmp['name'] = get_field('info')['name'];
        $arrayTmp['dup'] = get_field('info')['dup'];
        $arrayTmp['gps_pos'] = [
            'lat' => floatval(get_field('gps_pos')['lat']),
            'lng' => floatval(get_field('gps_pos')['lng']),
            'dup' => get_field('gps_pos')['dup'],
        ];
    
        //トイレ情報 
        if($postType == 'restroom' && $query != 'init'){
            $arrayTmp['metas'] = [
                'clean' => get_field('metas')['clean'],
                'tukaiyasusa' => get_field('metas')['tukaiyasusa'],
            ];
            $arrayTmp['icon'] = 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png';
        }
        //コンビニ 
        if($postType == 'convenience' && $query != 'init'){
            $arrayTmp['metas'] = [
                'atm' => get_field('metas')['atm'],
                'eatin' => get_field('metas')['eatin'],
                'tanakan' => get_field('metas')['tana'],
            ];
            $arrayTmp['icon'] = 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png';
        }
        //アミューズ 
        if($postType == 'amusement' && $query != 'init'){
            $arrayTmp['metas'] = [
                'genre' => get_field('genre'),
                'sougouhyouka' => get_field('sougouhyouka'),
                'cost' => [
                    'min' => get_field('cost')['min'],
                    'max' => get_field('cost')['max'],
                ]
            ];
            $arrayTmp['icon'] = 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png';
        }
        //飲食 
        if($postType == 'food' && $query != 'init'){
            $arrayTmp['metas'] = [
                'genre' => get_field('foods')['genre'],
                'cost' => [
                    'min' => get_field('match')['min'],
                    'max' => get_field('match')['max'],
                ],
                'sougouhyouka' => get_field('review')['star'],
            ];
            $arrayTmp['icon'] = 'https://developers.google.com/maps/documentation/javascript/examples/full/images/beachflag.png';
        }

        //queryで指定されたものに応じて弾いたりする
        if($query != 'init'){
            if(isset($arrayTmp['metas']['clean'])){
                if($arrayTmp['metas']['clean'] < $query['clean']) continue;
            }
        }


        array_push($returnArray,$arrayTmp);
        
    }
}
wp_reset_postdata();
echo json_encode($returnArray);