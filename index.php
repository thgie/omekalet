<?php

require_once 'Omekalet.php';

$omekalet = new Omekalet(rtrim($_POST['base_url'], '/'), $_POST['api_key']);

if (isset($_GET["collections"])) {
    return $omekalet->get_collections();
}

$attributes = [
    'title' => $_POST['title'],
    'description' => $_POST['description'],
    'creator' => $_POST['creator'],
    'source' => $_POST['source'],
    'collection' => $_POST['collection'],
    'type' => $_POST['item_type'],
];

if($_POST['item_type'] == '11'){
    $attributes['link'] = $_POST['hyperlink'];
} else if($_POST['item_type'] == '1'){
    $attributes['content'] = $_POST['text'];
}
if(isset($_POST['tags'])){
    $attributes['tags'] = $_POST['tags'];
}
if(isset($_POST['extract_article'])){
    $attributes['extract_article'] = $_POST['extract_article'];
}

$item = $omekalet->build_item($attributes);
$item_id = $omekalet->add_item($item);

// make screenshot
if(isset($_POST['make_screenshot'])){
    $omekalet->add_screenshot($item_id, $_POST['screenshot_url'], $item["element_texts"][0]["text"]);
}

// upload attachements
if (isset($_FILES['files']['tmp_name'])) {
    $num_files = count($_FILES['files']['tmp_name']);
    for ($i = 0; $i < $num_files; $i++) {
        if (is_uploaded_file($_FILES['files']['tmp_name'][$i])) {
            move_uploaded_file($_FILES['files']['tmp_name'][$i], dirname(__FILE__).'/temp/' . $_FILES['files']['name'][$i]);
            $omekalet->add_file($item_id, 'temp/' . $_FILES['files']['name'][$i]);
        }
    }
}

echo 'true: ' . $item_id;