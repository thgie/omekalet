<?php

require_once('Omekalet.php');
$omekalet = new Omekalet('https://library.thgie.ch', 'c535fe66302e9127942da1a47aab631186d31b28');

for($id = 1; $id < 100; $id++){

    $response = $omekalet->get_item($id);

    if($response->code === 404) {
        echo 'no item ' . $id . PHP_EOL;
    } else {
        file_put_contents('data/'.$id.'.json', json_encode($response->body, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

?>