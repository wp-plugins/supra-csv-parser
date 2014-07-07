<?php

require_once(dirname(__FILE__) . '/../../../../wp-load.php');

class ExtractorArgumentParser {

    private $exporter_properties = array('post_fields','filename');

    function __construct($args) {
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
        $this->settings = $this->getSettings();
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
                $this->records[0][$pk] = $post['post']->$pk;

            }
            else if(!in_array($key,array('post_fields'))) { 
                foreach((array)$pk as $p) {
                    if(array_key_exists($p, $post['postinfo'][$key])) { 
                        $this->records[0][$p] = $post['postinfo'][$key][$p];
                    }
                }
            }
            else if($key == 'post_fields') {
                foreach((array)$pk as $p) {
                    if(property_exists($post['post'], $p)) {
                        $this->records[0][$p] = $post['post']->$p;
                    }
                }
            }
        }

        return $this;
    }

    protected function getSettings() {

        $settings = get_option('scsv_csv_settings');
 
        $defaultSettings = array(
                                 'delimiter'=>',',
                                 'enclosure'=>'"'
                                );

        return array_merge($defaultSettings,(array)$settings);
    }

    private function buildCsv() {

        extract($this->getSettings());

        $record = $this->records[0];
        $val_array = array();
        $key_array = array();

        foreach($record AS $key => $val) {

                if(is_array($val)){
                    //Debug::show($val);
                    $oldval = $val;
                    $val="";
                    foreach($oldval as $arr) {
                        if(!is_array($arr)) {
                            //if its not an array implode it
                            $val .= implode('|',$this->removeHardReturns($arr));
                        }
                        else {
                            //if it is an array then barf
                            $val .= "NESTED ARRAYS UNSUPPORTED";
                            break;
                        }
                    }
                }
                else {
                    $val = $this->removeHardReturns($val);
                }
            
                //store the header columns
                $key_array[] = $key;

                //escape encolusre characters
                $val = str_replace($enclosure, $escape.$enclosure, $val);
                //escape delimiter character
                $val = str_replace($delimiter, $escape.$delimiter, $val);

                $val_array[] = $val; //$enclosure.$val.$enclosure;
        }

        $this->csvstring = implode($delimiter, $key_array)."\n";
        $this->csvstring .= implode($delimiter, $val_array)."\n";
        return $this;
    }

    private function removeHardReturns($val) {

        return str_replace(array("\r\n", "\r", "\n"), null, $val);
    }

    public function download() {
        if(empty($this->csvstring)) $this->parseRecords()->buildCsv();
        return $this->csvstring;
    }

    public function createFile() {
        if(empty($this->csvstring)) $this->parseRecords()->buildCsv();
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
