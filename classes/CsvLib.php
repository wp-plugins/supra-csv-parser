<?php
require_once("Debug.php");
require_once(dirname(__FILE__) . '/SupraCsvPlugin.php');

class SupraCsvParser extends SupraCsvPlugin {
    private $file;
    private $filename;
    private $handle;
    private $columns;

    function __construct($filename = null) {

        parent::__construct();

        if($filename) {
            if(empty($this->handle)){
                $this->setFile($filename);
                $this->setHandle(); 
            }
            $this->setColumns();
        }
    }

    private function setHandle() {
        $this->handle = fopen($this->file, "r");
    }

    private function setColumns() {
        $this->columns = fgetcsv($this->handle);
    }

    public function getColumns() {
        
        if(!$this->columns && $this->handle) {
            $this->setColumns();
        }
        return $this->columns;
    }

    public function setFile($filename) {
        $this->filename = $filename;
        $this->file = $this->getCsvDir() . $filename;
    }

    public function getFile() {
        return $this->file;
    }

    public function getFileName() {
        return $this->filename;
    }

    public function ingestContent($mapping) {

        $rp = new RemotePost();
        $cm = new SupraCsvMapper($mapping);

        $cols = $this->getColumns();

        //Debug::describe($this);
        //Debug::describe(fgetcsv($this->handle));

        //die();

        if($cols) {
            while (($data = fgetcsv($this->handle)) !== FALSE) {

                //loop through the columns
                foreach($data as $i=>$d) {
                    $parsed[$cols[$i]] = $d;
                }

                $row = $cm->retrieveMappedData($parsed);

                if(strstr(site_url(),'3dmpekg')) 
                    $row = $this->patchByRow($row);

                $title = $row['post_title'];
                $desc =  $row['post_content'];
                $categories =  explode('|', $row['categories']);
                $tags =  explode('|', $row['tags']);

                $scsv_terms = get_option('scsv_custom_terms');

                if(!empty($scsv_terms))
                $post_terms = explode(',',$scsv_terms);

                $terms = array();

                foreach((array)$post_terms as $pt) {
                    $wp_terms[$pt] = explode('|', $row['terms_'.$pt]);
                }
               
                if(!empty($row['tags']))
                $wp_terms['post_tag'] = $tags;
                if(!empty($row['categories']))
                $wp_terms['category'] = $categories;
  
                foreach((array)$wp_terms as $k=>$values) {
                    if(is_numeric($values[0])) {
                        $terms[$k] = $values;
                    }
                    else {
                        foreach($values as $v) {
                            $wp_term = get_term_by('name',$v,$k);
                            if($wp_term)
                                $terms[$k][] = $wp_term->term_id;
                        }
                    }
                }      
         
                foreach((array)$post_terms as $pt) {
                    unset($row['terms_'.$pt]);
                }

                unset($row['post_title']);
                unset($row['post_content']);
                unset($row['categories']);
                unset($row['tags']);

                if($rp->injectListing(array('title'=>$title,'desc'=>$desc,'cats'=>$categories,'tags'=>$tags,'terms'=>$terms,'meta'=>$row)))
                    echo '<span class="success">Successfully ingested '. $title . '</span><br />';
                else
                    echo '<span class="error">Problem Ingesting '. $title . '</span><br />';
            }
        }

    }

    private function patchByRow($row) {

        if(strstr(site_url(),'3dmpekg')) {
            $row['manufacturer_level1_value'] = ucfirst(strtolower($row['manufacturer_level1_value']));
    
            if(empty($row['name_value'])) {

                        $row['name_value'] = $row['manufacturer_level1_value'] . " " .
                                             $row['manufacturer_level2_value'] . " " .
                                             $row['year_value'];
            }
        }

        $row['post_title'] = $row['name_value'];

        return $row;
    }

}

class SupraCsvMapper {

    private $mapping = array();
    private $contents;

    function __construct($mapping) {
        $this->setMapping($mapping);        
    }

    public function setMapping($mapping) {
        $this->mapping = array_filter($mapping);
    }

    public function getMapping() {
        return $this->mapping;
    }

    public function retrieveMappedData($data) {

        //map the data with csv named keys to wp_keys
        foreach((array)$this->mapping as $wp_name=>$csv_name) {
            $row[$wp_name] = $data[$csv_name];
        }
           
        return $row;
    }

}

class SupraCsvMapperForm {

    private $filename;
    private $rows;
    private $listing_fields;

    private $predefined_meta = array('post_title'=>'Title','post_content'=>'Description','categories'=>'Categories','tags'=>'Tags');

    function __construct(SupraCsvParser $cp) {
        $rows = $cp->getColumns();
        $this->filename = $cp->getFileName();
        if(!$rows)
            die('Unable to parse csv.');

        $this->rows = $rows;
        $this->setListingFields();
    }

    public function setListingFields() {

        $postmetas = get_option('scsv_postmeta');

        foreach((array)$postmetas['meta_key'] as $i=>$metakey) {
            $displayname = $postmetas['displayname'][$i];
            $meta[$metakey] = $displayname;
        }

        $this->listing_fields = $meta;
    }

    public function getListingFields() {
        return $this->listing_fields;
    }

    private function displayListingFields() {

        $inputs = null;

        foreach((array)$this->getListingFields() as $k=>$v) {

            $inputs .= self::createInput($k,$v,$this->rows);

        }

        return $inputs;
    }

    public static function createInput($name,$value,$rows) {
          $input = '<span id="label">'.$value.'</span>';
          $input .= '<select id="supra_csv_'.$name.'" name="'.$name.'">';

          $input .= '<option value=""> </option>';

          foreach((array)$rows as $row) {
              $input .= '<option value="'.$row.'">'.$row.'</option>';
          }
 
          $input .= '</select>';

          return '<div id="input">' . $input . "</div>";
    }

    public function getForm() {

        $inputs .= '<h3>Predefined</h3>'; 

        foreach($this->predefined_meta as $k=>$v) {
            $inputs .= self::createInput($k,$v,$this->rows);
        }

        $post_terms = array();

        $scsv_terms = get_option('scsv_custom_terms');

        if(!empty($scsv_terms)) {
            $inputs .= '<h3>Custom Terms</h3>'; 

            $post_terms = explode(',',$scsv_terms);

            foreach($post_terms as $post_term) {
                $inputs .= self::createInput('terms_'.$post_term,$post_term,$this->rows);
            }
        } 

        $inputs .= '<h3>Custom Postmeta</h3>'; 

        $inputs .= $this->displayListingFields();

        $form = '<form id="supra_csv_mapping_form" data-filename="'.$this->filename.'">';
        $form .= $inputs;
        $form .= '<button id="supra_csv_ingest_csv">Ingest</button></form>';

        return $form;
    }

}
