<?php
require_once('Debug.php');
require_once('SupraCsvPlugin.php');

class RemotePost extends SupraCsvPlugin 
{
    private $uname;
    private $pass;
    private $postId;
    private $debugging, $debug_output, $report_issue, $issue_reported;
    private $admin_email = "zmijevik@hotmail.com";

    function __construct() 
    {
        parent::__construct();

        $this->setUser();       

        $this->debugging     = get_option('scsv_ingest_debugger');

        $this->report_issue  = get_option('scsv_report_issue');

        $this->issue_reported = 0; 
    }

    private function setUser() 
    {
        $csvuser = get_option('scsv_user');

        $this->uname = $csvuser['name'];

        $this->pass  = $csvuser['pass'];
    }

    public function setServer(SupraXmlrpcServer $server)
    {
        $this->server = $server;
    }

    private function _makeCall($args) 
    {

        if(!is_array($args['args'])) 
        {
            Throw new Exception('Invalid Argument');
        }

        $post= get_option('scsv_post');

        $default_args = array(
            'post_id'=>null,
        );

        $args = array_merge($default_args, $args);

        if($this->debugging) {
            Debug::show($args);
        }

        if($args['function'] == "wp.editPost") 
        {
            $args = array(
                'blog_id'=>$args['blog_id'],
                'username'=>$this->uname,
                'password'=>$this->pass,
                'content_struct'=>$args['args'],
                'post_id'=>$args['post_id']
            );

            $response = $this->server->wp_editPost($args);

            if($response instanceof XMLRPC_Error)
            {
                $this->debugAndReport($args, $response);
            }
            else if(!is_numeric($response))
            {
                $this->debugAndReport($args, new XMLRPC_Error(500, _('Response did not return a postId')));
            }
        }
        else if($args['function'] == "wp.newPost") 
        {
            $args = array(
                'blog_id'=>$args['blog_id'],
                'username'=>$this->uname,
                'password'=>$this->pass,
                'content_struct'=>$args['args']
            );

            $response = $this->server->wp_newPost($args);

            if($response instanceof XMLRPC_Error)
            {
                $this->debugAndReport($args, $response);
            }
            else if(!is_numeric($response))
            {
                $this->debugAndReport($args, new XMLRPC_Error(500, _('Response did not return a postId')));
            }
        }

        return $response;
    }


    public function postContent($content) {

        $post_id = null;

        if($content['post_id']) {

            $function = 'wp.editPost';
            $post_id = $content['post_id'];
            unset($content['post_id']);

        } else{
            $function = 'wp.newPost';
        }

        $args = array(
                      'function'=>$function,
                      'args'    =>$content,
        );

        if(!is_null($post_id)) {
            $args['post_id'] = $post_id;
        }

        $response = $this->_makeCall($args);

        if($response) {
            $this->postId = $response;
        }

        return $response;
    }


    public function injectListing($args) {

        foreach((array)$args['custom_fields'] as $k=>$v) {
            $custom_fields[] = array('key'=>$k,'value'=>$v);
        }

        $post = get_option('scsv_post');

        //the keys to filter by
        $params = array(
                        'post_id',
                        'post_title',
                        'post_type',
                        'post_content',
                        'terms_names',
                        'terms',
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
                        'menu_order',
                        'attachments'
                       );
   
        //filter the args into valiables 
        foreach($params as $param) {
            if(empty($args[$param]))
                $$param = @ $post[$param];
            else
                $$param = @ $args[$param];
        }

        //compact the variables
        $content = compact(
                        'post_id',
                        'post_title',
                        'post_type',
                        'terms_names',
                        'terms',
                        'custom_fields',
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
                        'menu_order',
                        'attachments'
        );

        foreach($params as $var_name)
        {
            if(empty($$var_name))
            {
                unset($content[$var_name]);
            }
        }

        if($this->debugging) {
                ini_set('display_errors', 1); // set to 0 when not debugging
                error_reporting(E_ALL ^ E_NOTICE);
        }

        //if the argument isnt empty than set it
        if(!empty($post_content)) {
            $content['post_content'] = $this->xmlencode($post_content);
        }
        //if the argument was empty but the configuration isnt
        else if(!empty($post['desc'])) {
            $content['post_content'] = $this->xmlencode($post['desc']);
        }

        //set the title to the specified configuration if its empty
        if(empty($content['post_title'])) {
            $content['post_title'] = $this->xmlencode($post['title']);
        }
 
        //add the custom terms
        if($post['publish'] && empty($content['post_status'])) {
            $content['post_status'] = 'publish';
        }
        else if(empty($content['post_status'])) {
            $content['post_status'] = 'pending';
        }

        if(empty($content['post_type'])) {
            $content['post_type'] = $post['type'];
        }

        //set the IXR Dates
        $date_keys = array('post_date','post_date_key');
        foreach($date_keys as $date_key) { 
            if(!empty($content[$date_key])) {
                $content[$date_key] = new IXR_Date($content[$date_key]);
            }
        }

        try {
            $success = $this->postContent($content);

        } catch( Exception $e ) {
            echo '<span class="error">'.$e->getMessage().'</span>';
            $success = false;;
        }
         
        return $success;
    }

    private function debugAndReport($args, XMLRPC_Error $error) 
    {
        throw new Exception($error);


        if($this->debugging) 
        {
            $this->debug_output = $error . ' ' .  Debug::returnShow($args);

            if($this->report_issue) 
            {
                $this->reportIssue();
            }
        }

        throw new Exception($error);
    }

    private function xmlencode($data) {

        if(get_option('scsv_encode_special_chars')) {

            $data = utf8_encode($data);
            $data = htmlentities($data);
        }
 
        return $data;
    }

    private function reportIssue() {}
}
