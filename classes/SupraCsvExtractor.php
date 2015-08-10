<?php
namespace SupraCsvFree;

use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;

require_once(dirname(__FILE__) . '/../vendor/autoload.php');
require_once(dirname(__FILE__) . '/SupraCsvPlugin.php'); 
require_once(dirname(__FILE__) . '/../../../../wp-load.php');

class ExtractorArgumentParser extends SupraCsvPlugin {

    private $exporter_properties = array('post_fields','filename');

    function __construct($args) {

        parent::__construct();

        $this->args = $args;
        $this->parseTaxAndMeta();
        $this->parseArrayFields();
        $this->parseWeeksAgo();
        $this->parsePostsPerPage();
        $this->parseRemainingArgs();
        //Debug::show($this->properties);
    }

    protected function parseTaxAndMeta() {

        $toParse = array('post_taxonomies');

        foreach($toParse as $parsing) {

            if(!empty($this->args[$parsing])) {
                $this->properties[$parsing] = explode(',', $this->args[$parsing]);
            }
  
            unset($this->args[$parsing]);
        }
    }

    protected function parseArrayFields() {

        $toParse = array('post_type','post_fields','meta_keys');

        foreach($toParse as $parsing) {

            if(array_key_exists($parsing, $this->args)) {

                $this->properties[$parsing] =  $this->args[$parsing];

                unset($this->args[$parsing]);
 
            }
        }
    }

    protected function parsePostsPerPage() {
        if(empty($this->args['posts_per_page'])) {
            $this->properties['posts_per_page'] = -1;
            unset($this->args['posts_per_page']);
        }
    }
 
    protected function parseWeeksAgo() {
        if(!empty($this->args['weeks_ago']))
            $this->properties['w'] = date('W') - (int) $this->args['weeks_ago'];

        unset($this->args['weeks_ago']);
    }

    protected function parseRemainingArgs() {
        foreach($this->args as $k=>$v) {
            if(!in_array($k,$this->exporter_properties))
                $this->properties[$k] = $v;
        }
    }
}

class SupraCsvExtractor extends ExtractorArgumentParser {

    /**
    * @param: args
    * --posts_per_page (int)
    * --offset (int)
    * --post_type(string)
    * --category(string)
    * --orderby(string)
    * --order(enum: DESC,ASC)
    * --post_status(string) 
    * --meta_query(array)
    * --tax_query(array)
    * --year(int)
    * --w(int)
    * --post_taxonomies(array) 
    * --meta_keys(array)
    **/   

    private function getPosts() {

        return query_posts($this->properties);
    }

    public function getPostsAndDetails() {
 
        $posts = false;
  
        foreach($this->getPosts() as $i=>$this->post) {
            $this->getCustomFields()->getKeywords();
            $posts[$i]['post'] = $this->post;
            $posts[$i]['postinfo'] = @ $this->postinfo;
        }  
 
        return $posts;        
    }

    public function displayExtractedPosts() {

        $posts = (array) $this->getPostsAndDetails();

        if(count($posts) && !empty($posts[0]))
            $string = '<span class="success">Found '.count($posts).' posts matching the criteria.</span>';
        else
            $string = '<span class="error">No results found matching the creteria</span>';

        foreach($posts as $post) {
            $post = $post['post'];
            $string .= '<p id="extracted_post"><a href="'.$post->guid.'">'.$post->post_title.'</a></p>';
        }

        return $string;
    }

    private function getCustomFields() {
        if(!empty($this->properties['meta_keys'])) {
            foreach($this->properties['meta_keys'] as $mk) {
                $post_meta = get_post_meta($this->post->ID,$mk,true);
                if(!empty($post_meta)) $this->postinfo['custom_fields'][$mk] = $post_meta;
            }
        }
        else {
             //$this->post->custom_fields = get_post_custom($this->post->ID);      
        }

        return $this;
    }
 
    private function getKeywords() {
        if(!empty($this->properties['post_taxonomies'])) {
            foreach($this->properties['post_taxonomies'] as $pt) {
                $post_terms = get_the_terms($this->post->ID,$pt);
                foreach((array)$post_terms as $post_term) {
                    if(is_object($post_term) && property_exists($post_term,'name')) { 
                        $this->postinfo['terms'][$pt] = $post_term->name;
                    }
                }
            }
        }
 
        return $this;
    }

}

class ExporterArgumentParser extends ExtractorArgumentParser {

    function __construct($posts,$args) {
        $this->posts = $posts;
        $this->args = $args;
        $this->parseTaxAndMeta();
        $this->buildArgs();
    }

    private function buildArgs() {
        
        $post_fields = array(
          'post_fields'=> @ $this->args['post_fields']
        );

        $meta_and_terms = array(
          'custom_fields'=> @ $this->args['meta_keys'],
          'terms'=> @ $this->properties['post_taxonomies']
        );

        $this->parsable_keys = array_merge($post_fields,$meta_and_terms);
        $this->filename = @ $this->args['filename'];
    }

}

class SupraCsvExporter extends ExporterArgumentParser {

    private function parseRecords() {
        $records = false;

        $post = $this->posts[0];

        //Debug::show($this->parsable_keys);
        //Debug::show($post); 
 
        foreach($this->parsable_keys as $key=>$pk) {
  
            if(!in_array($key,array('post_fields','custom_fields','terms')) && !is_array($key) || empty($key)) {
                $this->records[1][$pk] = $post['post']->$pk;

            }
            else if(!in_array($key,array('post_fields'))) { 
                foreach((array)$pk as $p) {
                    if(array_key_exists($p, $post['postinfo'][$key])) { 
                        $this->records[1][$p] = $post['postinfo'][$key][$p];
                    }
                }
            }
            else if($key == 'post_fields') {
                foreach((array)$pk as $p) {
                    if(property_exists($post['post'], $p)) {
                        $this->records[1][$p] = $post['post']->$p;
                    }
                }
            }
        }

        $this->_set_header_column();

        return $this;
    }

    private function _set_header_column()
    {
        foreach($this->records as $record)
            foreach($record as $key=>$val)
                $this->records[0][$key] = $key;

        ksort($this->records); 
    }  

    private function buildCsv($export_settings = array()) {

        foreach($export_settings as $key=>$export_setting)
        {
            $value = html_entity_decode($export_setting);

            $converted[$key] = $value;
        }

        extract($converted); 

        $config = new ExporterConfig();

        $config
            ->setDelimiter($extract_delim) 
            // Customize delimiter. Default value is comma(,)
            ->setEnclosure($extract_enclose)  
            // Customize enclosure. Default value is double quotation(")
            ->setEscape($extract_escape)   
            // Customize escape character. Default value is backslash(\)
            ->setToCharset($to_charset) 
            // Customize file encoding. Default value is null, no converting.
           ->setFromCharset($from_charset); 
            // Customize source encoding. Default value is null.

        $exporter = new Exporter($config);

        ob_start();

        $exporter->export('php://output', $this->records);

        $this->csvstring = ob_get_contents();
                 
        ob_end_clean();

        return $this;
    }

    private function removeHardReturns($val) {

        return str_replace(array("\r\n", "\r", "\n"), null, $val);
    }

    public function download($export_settings = array()) {
        if(empty($this->csvstring)) $this->parseRecords()->buildCsv($export_settings);
        return $this->csvstring;
    }

    public function createFile($export_settings = array()) {
        if(empty($this->csvstring)) $this->parseRecords()->buildCsv($export_settings);
        $my_file = dirname(__FILE__) . '/../../../uploads/supra-csv-parser/csv/'.$this->filename;
        $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
        fwrite($handle, $this->csvstring);
    }

    public function addToSession() {
        if(empty($this->csvstring)) $this->parseRecords()->buildCsv();
        $_SESSION['supra_csv']['filename'] = $this->filename;
        $_SESSION['supra_csv']['content'] = $this->csvstring;
    }
}
