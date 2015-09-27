<?php
namespace SupraCsvFree;

require_once("Debug.php");
require_once(dirname(__FILE__) . '/SupraCsvPlugin.php');
require_once(dirname(__FILE__) . '/SupraCsvHookManager.php');
require_once(dirname(__FILE__) . '/SupraXmlrpcServer.php');

class SupraCsvParser extends SupraCsvPlugin {

    private $file;
    private $filename;
    private $columns;
    private $hasHooks;
    protected $mapper;
    protected $remotePost;

    function __construct($filename = null) {

        parent::__construct();

        if($filename) {
            $this->setFile($filename);
        }
    }

    public function init($settings = array())
    {
        $this->settings = $settings;

        $this->hasHooks = (bool) $this->getSetting('scsv_has_hooks');
        /**
         * hook type must occur in sequential order based on 
         * dependency relations, each subsequent element will 
         * gather the previous dependenies
         */
        $hook_types = array('ingestion','row');

        $dependencies = array();

        if($this->hasHooks) { 

            foreach($hook_types as $hook_type) {

                $hookClassName = 'SupraCsv' . ucfirst($hook_type) . 'Hooks';

                $hookNamespacedClassName = '\SupraCsvFree\\' . $hookClassName;

                $dependencies[$hookClassName] = $this->hook_mgrs[$hook_type] = new $hookNamespacedClassName($dependencies);

                $this->hook_mgrs[$hook_type]->setLogger($this->getLogger());

                $this->has_hook[$hook_type] = $this->hook_mgrs[$hook_type]->hasHooks();
            }
        }

        $this->remotePost = new RemotePost($this);

        $this->remotePost->setServer(new SupraXmlrpcServer()); 
        
        $this->_blog_id = get_current_blog_id();

        add_filter( 'wp_revisions_to_keep', array(&$this, 'revisions_iterator'), 10, 2 );
    
        return $this;
    }

    public function revisions_iterator($num, $post)
    {
        $misc_options = $this->getSetting('scsv_misc_options');

        $are_revisions_skipped = $misc_options['are_revisions_skipped'];

        if($are_revisions_skipped)
        {
            return false;
        }
        else
        {
            return $num;
        }
    }

    protected function sanitizeHeaderColumns()
    {
        $columns = $this->columns;

        $errors = array();

        if(count($columns) == 1)
        {
            $errors[] = "there is only one header column $column";
        }

        foreach($columns as $column)
        {
            //if there are more than 3 instance of commas its probbably wrong
            if(strstr($column,','))
            {
                $errors[] = "we found several commas in this column, you may have meant to use commas as a delimiter instead.";
            }

            if(strlen($column)>50)
            {
                $errors[] = "the length of this column is over 50 $column";
            }
        }

        return $errors;
    }

    public function setColumns($columns = array())
    {
        if(!count($columns))
        {
            $this->columns = array_shift($this->csvLines);

            $errors = $this->sanitizeHeaderColumns();

            foreach($errors as $error)
            {
                $this->error_tips[] = "Setting the column headers may have failed because " . $error;
            }

            //if hooks are enabled in the configuration tab
            if($this->hasHooks)
                $this->columns[] = 'last_post_id';
        }
        else
        {
            $this->columns = $columns;
        }
    }


    public function getColumns() {

        if(!$this->columns) 
        {
            $this->setColumns();
        }

        return $this->columns;
    }

    public function setFile($filename, $isAbsolutePath = false) {
        
        $this->filename = $filename;
        
        if(!$isAbsolutePath)
        {
            $this->file = $this->getCsvDir() . $filename;
        }
        else
        {
            $this->file = $filename;
        }

        if(!file_exists($this->file))
        {
            Throw new \Exception("{$this->file} does not exists or has permission issues");
        }
        else
        {
            $this->csvLines = $this->parseLines($this->file);

            $this->setColumns(); 
        }
    }

    public function getFile() {
        return $this->file;
    }

    public function getFileName() {
        return $this->filename;
    }

    public function setMapping($mapping) 
    {
        $this->mapper = new SupraCsvMapper($mapping);    

        return $this->mapper;
    }

    public function appendToProgressBuffer($msg)
    {
        $this->progressBuffer .= $msg;
    }


    public function ingestContent($mapping = array()) 
    {
        $this->progressBuffer = '';

        $max_exec_time = ini_get('max_execution_time');

        if((int) $max_exec_time > 0) {
            $this->error_tips[] = "Max execution time of your current php handler is not unlimtied but instead: " . $max_exec_time;
        }

        if(!empty($mapping))
        {
            $this->setMapping($mapping);
        }

        $cols = $this->getColumns();

        //$this->getLogger()->info(__METHOD__ . var_export(compact('cols'), true));
        $rowCount=0;

        if($cols) {
            foreach($this->csvLines as $data) {

                //catch an empty line
                if(count($data)==1 && empty($data[0])) continue;


                if($this->hasHooks && $this->has_hook['row'])
                    $data = $this->hook_mgrs['row']->callHooks($data);

                //loop through the columns
                foreach($data as $i=>$d) {
                    $parsed[$cols[$i]] = $d;
                }

                $row = $this->mapper->retrieveMappedData($parsed);

                $post_args = $this->getPostArgs($row);               

                //use this to troubleshoot correct mapping
                //$this->getLogger()->info(__METHOD__ . var_export(compact('cols','post_args'), true));
                $post_args['blog_id'] = $this->_blog_id;

                $rowCount++;

                if($rp_result = $this->remotePost->injectListing($post_args)) 
                {
                    if($this->hasHooks && $this->has_hook['ingestion'])
                    {
                        $this->hook_mgrs['ingestion']->callHooks($post_args,$rp_result);
                    }

                    $this->progressBuffer .= $this->rowIngestionSuccess($post_args, $rowCount, $rp_result);
                }
                else 
                {
                    $this->progressBuffer .= $this->rowIngestionFailure($post_args, $rowCount);
                }
            }
        }

        return $this->progressBuffer;
    }

    private function rowIngestionSuccess($post_args, $rowCount, $rowId = 0)
    {
        $msg = '<span class="success">Successfully ingested %s at line %d of %s with postId: %s</span><br />';

        $msg = sprintf(
            $msg, 
            $post_args['post_title'], 
            $rowCount, 
            $this->getFileName(), 
            $this->linkToPost($rowId)
        );

        return $msg;
    }

    private function rowIngestionFailure($post_args, $rowCount)
    {
        $msg = '<span class="error">Problem ingesting %s at line %d of %s</span><br />';

        $msg = sprintf(
            $msg, 
            $post_args['post_title'], 
            $rowCount, 
            $this->getFileName()
        );

        return $msg;
    }

    public function linkToPost($post_id) 
    {
        return sprintf('<a href="%s" target="_blank">%d (click to edit)</a>', 
            get_admin_url( $this->_blog_id, "post.php?post=".$post_id."&action=edit"),
            $post_id
        );
    }

    private function getPostArgs($row) {

        $csvpost = $this->getSetting('scsv_post');

        $post_title = @ $row['post_title'];

        $post_content = @ $row['post_content'];

        $parse_terms = $this->getSetting('scsv_parse_terms');

        $wp_parse_cats = false;

        $wp_terms = array();

        if($parse_terms  && $this->validTaxonomyByPostType('category')) {
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
        $terms_names = array();
        $terms = array();
        $custom_fields = array();

        $parse_terms = $this->getSetting('scsv_parse_terms');

        $custom_terms = $this->getSetting('scsv_custom_terms');

        if(!empty($custom_terms))
            $post_terms = explode(',', $custom_terms);

        //parse the cutom term to its taxonomy
        foreach((array)$post_terms as $pt) {
            if($this->validTaxonomyByPostType($pt) )
                if(!empty($row['terms_'.$pt]))
                    $wp_terms[$pt] = explode('|', $row['terms_'.$pt]);
        }


        //categories must be resolved by terms
        if(!empty( $row['category'] )) 
        {
            if($wp_parse_cats) 
            {
                Throw new \Exception('<span class="error">You must either parse complexy or simplistic categoires but not both.</span>');
            }
            
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
            $$key = @ $row[$key];
            unset($row[$key]);
        }

        foreach((array)$row as $k=>$v) {
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

    public function validTaxonomyByPostType($tax) {

        $isValid = false;

        $post_type_taxonomies = $this->getPostTypeTaxonomies();

        if($post_type_taxonomies == "*")
        {
            $isValid = true;
        }
        else
        {
            $isValid = array_key_exists($tax, $post_type_taxonomies);
        }

        return $isValid;
    }
}

class SupraCsvMapper {

    private $mapping = array();
    private $contents;

    function __construct($mapping) {
        $this->setMapping($mapping);        
    }

    public function setMapping($mapping = array()) {
        $this->mapping = array_filter($mapping);
    }

    public function getMapping() {
        return $this->mapping;
    }

    public function retrieveMappedData($data) {

        $row = array(); 

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

    function __construct(SupraCsvParser $scp) {

        $rows = $scp->getColumns();

        $this->filename = $scp->getFileName();

        $this->scp = $scp;

        if(!$rows) 
        {
            Throw new \Exception('Unable to parse csv.');
        }

        $this->rows = $rows;
        $this->setListingFields();
    }

    public function setListingFields() {

        $postmetas = $this->scp->getSetting('scsv_postmeta');

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

        if(count($fields) > 0) {

            $inputs .= '<h3><span id="custompostmeta_tt" class="tooltip"></span>Custom Postmeta</h3>'; 
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

        $inputs = null;

        $inputs .= '<h3><span id="ingestionpredefined_tt" class="tooltip"></span>Predefined</h3>';

        $inputs .= '<div class="scsv_predefined_mapper">';

        foreach($this->predefined_meta as $k=>$v) {
            $inputs .= self::createInput($k,$v,$this->rows);
        }

        $inputs .= '</div>';

        $parse_terms = $this->scp->getSetting('scsv_parse_terms');
        $custom_terms = $this->scp->getSetting('scsv_custom_terms');


        $inputs .= '<div class="scsv_custom_mapper">';

        if($parse_terms || !empty($custom_terms))
            $inputs .= '<h3><span id="customterms_tt" class="tooltip"></span>Custom Terms</h3>';



        if($parse_terms) {
            if ( $this->scp->validTaxonomyByPostType('category') ) {
                $inputs .= self::createInput('term_name','Term Name',$this->rows);
                $inputs .= self::createInput('term_slug','Term Slug',$this->rows);
                $inputs .= self::createInput('term_parent','Term Parent',$this->rows);
                $inputs .= self::createInput('term_description','Term Description',$this->rows);
            }
        }

        if(!empty($custom_terms)) {
            $post_terms = explode(',', $custom_terms);

            foreach($post_terms as $post_term) {
                if ( $this->scp->validTaxonomyByPostType($post_term) )
                    $inputs .= self::createInput('terms_'.$post_term,$post_term,$this->rows);
            }
        }

        $inputs .= $this->displayListingFields();

        $inputs .= '<span id="ingest_tt" class="tooltip"></span><button id="supra_csv_ingest_csv">Ingest</button>';
        $inputs .= '<p><img id="patience" /></p>';
        $inputs .= '</div><div class="clear"></div>';
        $inputs .= '</div>';

        $form = '<form id="supra_csv_mapping_form" data-filename="'.$this->filename.'">';
        $form .= $inputs;
        $form .= '</form>';

        return $form;
    }
}
