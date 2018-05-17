<?php

require_once('EmailReader.php');
require_once('Omekalet.php');

$omekalet = new Omekalet('OMEKA_URL', 'API_KEY');
$inbox = new EmailReader('IMAP_SERVER', 'EMAIL_ADRESS', 'PASSWORD', 993);
$screeshot_url = '';
$collections = json_decode($omekalet->get_collections());

while(count($inbox->get())){
    $email = $inbox->get();
    $content = $email['header']->subject . ' ' . $email['body'];

    preg_match_all('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/', $content, $url_matches, PREG_PATTERN_ORDER);
    preg_match_all("/#(\w+)/", $content, $tag_matches);
    preg_match_all("/@(\w+)/", $content, $collection_matches);

    $source = '';
    $target = '';

    if(isset($url_matches[0][0])){
        $source = $url_matches[0][0];
    }
    if(isset($collection_matches[1][0])){
        $target = $collection_matches[1][0];
    }
    $tags = join($tag_matches[1], ',');
    $collection_id = $collections[0]->id;

    foreach($collections as $key => $collection){
        $title = $collection->element_texts[0]->text;
        if(strpos($target, $omekalet->slugify($title)) !== false){
            $collection_id = $collection->id;
        }
    }

    if (strpos($source,'http://') === false && strpos($source,'https://') === false && strlen($source)){
        $source = 'http://'.$source;
    }

    if(strlen($source)){
        $page = $omekalet->grab_page($source);

        $title = $page['title'];
        $description = '';

        if(isset($page['open_graph']['og_description'])){
            $description = $page['open_graph']['og_description'];
        }
    } else {
        $title = $email['header']->subject;
        $description = $email['body'];
    }

    $attributes = [
        'title' => $title,
        'description' => $description,
        'creator' => '',
        'source' => $source,
        'collection' => $collection_id,
        'tags' => $tags
    ];

    if(strpos($content, '+article') !== false){
        $attributes['type'] = 1;
        $attributes['content'] = $page['html'];
    } else if(strlen($source)) {
        $attributes['type'] = 11;
        $attributes['link'] = $source;
    }

    $item = $omekalet->build_item($attributes);
    $item_id = $omekalet->add_item($item);

    if(strpos($content, '+screen') !== false && strlen($source) && strlen($screenshot_url)){
        $omekalet->add_screenshot($item_id, $screenshot_url.$source, $item["element_texts"][0]["text"]);
    }

    $attachments = array();
    if (isset($email['structure']->parts) && count($email['structure']->parts)) {
        for ($i = 0; $i < count($email['structure']->parts); $i++) {
            $attachments[$i] = array(
                'is_attachment' => FALSE,
                'filename'      => '',
                'name'          => '',
                'attachment'    => ''
            );

            if ($email['structure']->parts[$i]->ifdparameters) {
                foreach ($email['structure']->parts[$i]->dparameters as $object) {
                    if (strtolower($object->attribute) == 'filename') {
                        $attachments[$i]['is_attachment'] = TRUE;
                        $attachments[$i]['filename']      = $object->value;
                    }
                }
            }

            if ($email['structure']->parts[$i]->ifparameters) {
                foreach ($email['structure']->parts[$i]->parameters as $object) {
                    if (strtolower($object->attribute) == 'name') {
                        $attachments[$i]['is_attachment'] = TRUE;
                        $attachments[$i]['name']          = $object->value;
                    }
                }
            }

            if ($attachments[$i]['is_attachment']) {
                $attachments[$i]['attachment'] = imap_fetchbody($inbox->conn, $email['index'], $i+1);

                if ($email['structure']->parts[$i]->encoding == 3) { // 3 = BASE64
                    $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                }
                elseif ($email['structure']->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                    $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                }
            }
        }
    }

    foreach ($attachments as $key => $attachment) {
        if($attachment['is_attachment']){
            $name = dirname(__FILE__).'/temp/'.$attachment['name'];
            $contents = $attachment['attachment'];
            file_put_contents($name, $contents);
            $omekalet->add_file($item_id, $name);
        }
    }

    $inbox->move($email['index']);
}

?>