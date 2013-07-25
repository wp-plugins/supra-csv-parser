<?php
require_once("Debug.php");
require_once(dirname(__FILE__) . '/SupraCsvPlugin.php');
require_once(dirname(__FILE__) . '/SupraCsvPostTaxonomy.php');

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


    private function parseNextLine($handle) {
        $csv_settings = get_option('scsv_csv_settings');
        if (strnatcmp(phpversion(),'5.3') >= 0) {    
            return fgetcsv($handle,1000,stripslashes($csv_settings['delimiter']),stripslashes($csv_settings['enclosure']),stripslashes($csv_settings['escape']));
        
        } 
        else { 
            return fgetcsv($handle,1000,stripslashes($csv_settings['delimiter']),stripslashes($csv_settings['enclosure']));
        }  
    }


    public function ingestContent($mapping) {

        $rp   = new RemotePost();
        $cm   = new SupraCsvMapper($mapping);

        $cols = $this->getColumns();

        $rowCount=0;

        if($cols) {
            while (($data = $this->parseNextLine($this->handle))!== FALSE) {

                //catch an empty line
                if(count($data)==1 && empty($data[0])) continue;

                //loop through the columns
                foreach($data as $i=>$d) {
                    $parsed[$cols[$i]] = $d;
                }

                $row = $cm->retrieveMappedData($parsed);

                $post_args = $this->getPostArgs($row);               

                if($rp->injectListing($post_args)) {
                    echo '<span class="success">Successfully ingested '. $post_args['post_title'] . '</span><br />';
                }
                else {
                    echo '<span class="error">Problem Ingesting '. $post_args['post_title'] . '</span><br />';
                }

                $rowCount++;
            }
        }

    }

    private function getPostArgs($row) {
                $ptax = new SupraCsvPostTaxonomy();

                $csvpost = get_option('scsv_post');

                $post_title = $row['post_title'];
                $post_content =  $row['post_content'];

                $parse_terms = get_option('scsv_parse_terms');

                $wp_parse_cats = false;

                $wp_terms = array();

                if($parse_terms  && $ptax->validTaxonomyByPostType('category')) {
                    $fields = array('term_name','term_slug','term_parent','term_description');
                    foreach($fields as $field)
                        if(!empty($row[$field]))
                            $$field = $row[$field];
                    $wp_parse_cats = compact('term_name','term_slug','term_parent','term_description');
                    foreach($fields as $field)
                        unset($row[$field]);
                    $count = count($wp_parse_cats);
                    if(!empty($count))
                        $wp_terms['category'][] = $wp_parse_cats;
                }

                $post_terms = array();
                $term_names = array();
                $terms = array();

                $parse_terms = get_option('scsv_parse_terms');


                $custom_terms = get_option('scsv_custom_terms');

                if(!empty($custom_terms))
                    $post_terms = explode(',',get_option('scsv_custom_terms'));

                //parse the cutom term to its taxonomy
                foreach((array)$post_terms as $pt) {
                    if($ptax->validTaxonomyByPostType($pt) )
                        if(!empty($row['terms_'.$pt]))
                            $wp_terms[$pt] = explode('|', $row['terms_'.$pt]);
                }


                //categories must be resolved by terms
                if(!empty( $row['category'] )) {
                    if($wp_parse_cats) die('<span class="error">You must either parse complexy or simplistic categoires but not both.</span>');
                    $wp_terms['category'] = explode('|', $row['category']);
                }

                //keywords must be resolved by terms
                if(!empty( $row['post_tag'] ))
                    $wp_terms['post_tag'] = explode('|', $row['post_tag']);

                //parse and load remaining postmeta
                foreach((array)$wp_terms as $k=>$v) {
                    if(!empty($k) && !empty($v)) {
                        if(is_int($v[0]))
                            $terms[$k] = $v;
                        else
                            $terms_names[$k] = $v;
                    }
                }

                //these unsetters confine the array to custom_fields
                foreach((array)$post_terms as $pt) {
                    unset($row['terms_'.$pt]);
                }

                unset($row['category']);
                unset($row['post_tag']);

                $predefined = array(
                    'post_id',
                    'post_title',
                    'post_content',
                    'post_type',
                    'post_status',
                    'post_author',
                    'post_password',
                    'post_excerpt',
                    'post_date',
                    'post_date_gmt',
                    'post_thumbnail',
                    'comment_status',
                    'ping_status',
                    'post_format',
                    'enclosure',
                    'post_parent',
                    'menu_order'
                );

                foreach($predefined as $key) {
                    $$key = $row[$key];
                    unset($row[$key]);
                }

                foreach($row as $k=>$v) {
                    if(!empty($k) && !empty($v)) {
                        $custom_fields[$k] = $v;
                    }
                }

                $post_args = compact(
                    'post_id',
                    'post_title',
                    'post_content',
                    'post_type',
                    'post_status',
                    'post_author',
                    'post_password',
                    'post_excerpt',
                    'post_date',
                    'post_date_gmt',
                    'post_thumbnail',
                    'comment_status',
                    'ping_status',
                    'post_format',
                    'enclosure',
                    'post_parent',
                    'menu_order'
                );

                $post_args['terms'] = $terms;
                $post_args['terms_names'] = $terms_names;
                $post_args['custom_fields'] = $custom_fields;
 
        return $post_args;
    }
   
    private function patchByRow($row) {

        /*
        if(strstr(site_url(),'3dmpekg')) {
            $row['manufacturer_level1_value'] = ucfirst(strtolower($row['manufacturer_level1_value']));
    
            if(empty($row['name_value'])) {

                        $row['name_value'] = $row['manufacturer_level1_value'] . " " .
                                             $row['manufacturer_level2_value'] . " " .
                                             $row['year_value'];
            }
        }

        $row['post_title'] = $row['name_value'];

        */
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

    private $predefined_meta = array(
        'post_id'=>'Post ID',
        'post_title'=>'Title',
        'post_content'=>'Description',
        'category'=>'Categories',
        'post_tag'=>'Tags',
        'post_type'=>'Post Type',
        'post_status'=>'Post Status',
        'post_author'=>'Post Author',
        'post_password'=>'Post Password',
        'post_excerpt'=>'Post Excerpt',
        'post_date'=>'Post Date',
        'post_date_gmt'=>'Post Date GMT',
        'post_thumbnail'=>'Post Thumbnail',
        'comment_status'=>'Comment Status',
        'ping_status'=>'Ping Status',
        'post_format'=>'Post Format',
        'enclosure'=>'Enclosure',
        'post_status'=>'Post Status',
        'post_parent'=>'Post Parent',
        'menu_order'=>'Menu Order'
    );

    function __construct(SupraCsvParser $cp) {
        $rows = $cp->getColumns();
        $this->filename = $cp->getFileName();
        if(!$rows)
            die('Unable to parse csv.');

        $this->rows = $rows;
        $this->setListingFields();
        $this->ptax = new SupraCsvPostTaxonomy();
    }

    public function setListingFields() {

        $postmetas = get_option('scsv_postmeta');

        //Debug::show($postmetas);

        foreach((array)$postmetas['meta_key'] as $i=>$meta_key) {
            $displayname = $postmetas['displayname'][$i];
            if(in_array($i,$postmetas['use_metakey']))
                $meta[$meta_key] = $displayname;
        }

        $this->listing_fields = $meta;
    }

    public function getListingFields() {
        return $this->listing_fields;
    }

    private function displayListingFields() {
        $inputs = null;

        $fields = $this->getListingFields();

        if(count($fields) == 1 && !empty($fields[0]) || count($fields) > 1) {

            $inputs .= '<h3>Custom Postmeta</h3>'; 
            foreach((array)$this->getListingFields() as $k=>$v) {
                $inputs .= self::createInput($k,$v,$this->rows);
            }
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
          $input .= '<div class="clear"></div>';

          return '<div id="input">' . $input . "</div>";
    }


    public function getForm() {

        $inputs .= '<h3>Predefined</h3>';

        $inputs .= '<div class="scsv_predefined_mapper">';

        foreach($this->predefined_meta as $k=>$v) {
                $inputs .= self::createInput($k,$v,$this->rows);
        }

        $inputs .= '</div>';

        $parse_terms = get_option('scsv_parse_terms');
        $custom_terms = get_option('scsv_custom_terms');


        $inputs .= '<div class="scsv_custom_mapper">';

        if($parse_terms || !empty($custom_terms))
              $inputs .= '<h3>Custom Terms</h3>';


        
        if($parse_terms) {
              if ( $this->ptax->validTaxonomyByPostType('category') ) {
                  $inputs .= self::createInput('term_name','Term Name',$this->rows);
                  $inputs .= self::createInput('term_slug','Term Slug',$this->rows);
                  $inputs .= self::createInput('term_parent','Term Parent',$this->rows);
                  $inputs .= self::createInput('term_description','Term Description',$this->rows);
              }
        }

        if(!empty($custom_terms)) {
              $post_terms = explode(',',get_option('scsv_custom_terms'));

              foreach($post_terms as $post_term) {
                  if ( $this->ptax->validTaxonomyByPostType($post_term) )
                      $inputs .= self::createInput('terms_'.$post_term,$post_term,$this->rows);
              }
        }

        $inputs .= $this->displayListingFields();

        $inputs .= '<button id="supra_csv_ingest_csv">Ingest</button>';
        $inputs .= '</div><div class="clear"></div>';
        $inputs .= '</div>';

        $form = '<form id="supra_csv_mapping_form" data-filename="'.$this->filename.'">';
        $form .= $inputs;
        $form .= '</form>';

        return $form;
    }
}
