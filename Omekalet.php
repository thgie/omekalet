<?php

require_once 'vendor/autoload.php';

class Omekalet
{

    private $base_url = '';
    private $api_url = '';
    private $api_key = '';

    private $proto = [
        "item_type" => [],
        "collection" => ["id" => 1],
        "element_texts" => [],
        "public" => false,
        "featured" => false,
    ];
    private $element_text = [
        "html" => false,
        "text" => "",
        "element_set" => [],
        "element" => []
    ];
    private $element_sets = [
        "1" => [
            "id" => 1,
            "url" => "element_sets/1",
            "name" => "Dublin Core",
            "resource" => "element_sets"
        ],
        "3" => [
            "id" => 3,
            "url" => "element_sets/3",
            "name" => "Item Type Metadata",
            "resource" => "element_sets"
        ]
    ];
    private $elements = [
        "text" => [
            "id" => 1,
            "url" => "/elements/1",
            "name" => "Text",
            "resource" => "elements"
        ],
        "title" => [
            "id" => 50,
            "url" => "elements/50",
            "name" => "Title",
            "resource" => "elements"
        ],
        "description" => [
            "id" => 41,
            "url" => "elements/41",
            "name" => "Description",
            "resource" => "elements"
        ],
        "hyperlink" => [
            "id" => 28,
            "url" => "elements/28",
            "name" => "URL",
            "resource" => "elements"
        ],
        "creator" => [
            "id" => 39,
            "url" => "elements/39",
            "name" => "Creator",
            "resource" => "elements"
        ],
        "source" => [
            "id" => 48,
            "url" => "elements/48",
            "name" => "Source",
            "resource" => "elements"
        ]
    ];

    function __construct($base_url, $api_key)
    {
        $this->base_url = $base_url;
        $this->api_key = $api_key;
        $this->api_url = $base_url . '/api/';

        foreach ($this->element_sets as $key => $e) {
            $e['url'] = $this->api_url . $e['url'];
        }
        foreach ($this->elements as $key => $e) {
            $e['url'] = $this->api_url . $e['url'];
        }
    }

    public function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    public function build_item($attributes)
    {
        $item = $this->proto;

        $title = $this->element_text;
        $description = $this->element_text;
        $creator = $this->element_text;
        $source = $this->element_text;
        $hyperlink = $this->element_text;
        $text = $this->element_text;

        $title["element_set"] = $this->element_sets["1"];
        $description["element_set"] = $this->element_sets["1"];
        $creator["element_set"] = $this->element_sets["1"];
        $source["element_set"] = $this->element_sets["1"];
        $hyperlink["element_set"] = $this->element_sets["3"];
        $text["element_set"] = $this->element_sets["3"];

        $title["element"] = $this->elements["title"];
        $description["element"] = $this->elements["description"];
        $creator["element"] = $this->elements["creator"];
        $source["element"] = $this->elements["source"];
        $hyperlink["element"] = $this->elements["hyperlink"];
        $text["element"] = $this->elements["text"];

        $title["text"] = $attributes['title'];
        $description["text"] = $attributes['description'];
        $creator["text"] = $attributes['creator'];
        $source["text"] = $attributes['source'];

        $item["element_texts"][] = $title;
        $item["element_texts"][] = $description;
        $item["element_texts"][] = $creator;
        $item["element_texts"][] = $source;

        if(isset($attributes['type'])) {
            if ($attributes['type'] == '11') {
                $hyperlink["text"] = $attributes['link'];
                $item["element_texts"][] = $hyperlink;
            }
            if ($attributes['type'] == '1') {
                if (isset($attributes['extract_article'])) {
                    $result = $this->grab_page($attributes['source']);
                    $text["text"] = $result["html"];
                    $text["html"] = true;
                    $item["element_texts"][] = $text;
                } else {
                    $text["text"] = $attributes['content'];
                    $text["html"] = true;
                    $item["element_texts"][] = $text;
                }
            }
            $item["item_type"]["id"] = $attributes['type'];
        }

        $item["collection"]["id"] = $attributes['collection'];

        if (strlen($attributes['tags']) > 0) {
            $tags = explode(',', $attributes['tags']);
            $tags_arr = [];
            foreach ($tags as $key => $tag) {
                $tags_arr[] = ["name" => trim($tag)];
            }
            $item["tags"] = $tags_arr;
        }

        return $item;
    }

    public function grab_page($url){
        $graby = new Graby\Graby();
        $result = $graby->fetchContent($url);

        return $result;
    }

    public function add_item($item, $screenshot = false)
    {
        $headers = array('Accept' => 'application/json');
        $body = Unirest\Request\Body::json($item);
        $response = Unirest\Request::post($this->api_url . "items?key=" . $this->api_key, $headers, $body);
        $item_id = $response->body->id;

        return $item_id;
    }

    public function get_item($id){
        return $response = Unirest\Request::get($this->api_url . "items/" . $id . '?key=' . $this->api_key);
    }

    public function add_file($item_id, $file){
        $headers = array('Accept' => 'application/json');
        $data = array('data' => '{"item": {"id": ' . $item_id . '}}');
        $files = array('file' => $file);

        $body = Unirest\Request\Body::multipart($data, $files);

        $response = Unirest\Request::post($this->api_url . "files?key=" . $this->api_key, $headers, $body);

        return $response;
    }

    public function add_screenshot($item_id, $url, $title = 'xyz'){
        $img = dirname(__FILE__).'/temp/'.$this->slugify($title).'.jpg';
        file_put_contents($img, file_get_contents($url));
        
        if(file_exists($img)){
            $this->add_file($item_id, $img);
        } 
    }

    public function get_collections()
    {
        return file_get_contents($this->api_url . 'collections?key=' . $this->api_key);
    }
}