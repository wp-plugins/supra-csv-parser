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

        if(!is_array($args['args'])) throw new Exception('Invalid Argument');

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

            if (!$response) 
            {
                echo $this->debugAndReport($args, $this->server->getErrorMessage());

                throw new Exception($this->server->getErrorMessage());
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
        
            if (!$response) 
            {
                echo $this->debugAndReport($args, $this->server->getErrorMessage());

                throw new Exception($this->server->getErrorMessage());
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
 
        $scac = new SupraCsvAttachmentCreator();
 
        if(!empty($content['post_thumbnail'])) {
            $content['post_thumbnail'] = $scac->processAttachment($content['post_thumbnail']);
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

            if(!empty($content['attachments']))
                $scac->processAttachment($content['attachments'],$success);                

        } catch( Exception $e ) {
            echo '<span class="error">'.$e->getMessage().'</span>';
            $success = false;;
        }
         
        return $success;
    }

    private function debugAndReport($args, $error) {
        if($this->debugging) {
            $this->debug_output = $error . ' ' .  Debug::returnShow($args);
            if($this->report_issue) {
                if($this->reportIssue())
                    $result = '<span class="success">Issue successfully reported!</span>';
                else
                    $result = '<span class="error">Problem reporting issue, check your SMTP configuration.</span>';
            }
        }

        return $result;
    }

    private function xmlencode($data) {

        if(get_option('scsv_encode_special_chars')) {

            $data = utf8_encode($data);
            $data = htmlentities($data);
        }
 
        return $data;
    }

    private function reportIssue() {

        $this->issue_reported++;

        if($this->issue_reported<=3){
            $admin_email = get_option('admin_email');
            $header = 'From: "Blog Admin" <'.$admin_email.'>';
            return wp_mail( $this->admin_email, 'Supra CSV issue', $this->debug_output,$header);
        }
        else {
            return true; 
        }
    }
}
