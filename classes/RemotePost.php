<?php
namespace SupraCsvFree;
require_once('Debug.php');

class RemotePost
{
    private $uname;
    private $pass;
    private $postId;
    private $debugging, $debug_output, $report_issue, $issue_reported;
    
    function __construct(SupraCsvParser $scp) 
    {
        $this->scp = $scp;
        
        $this->setUser();       

        $this->debugging     = $this->scp->getSetting('scsv_ingest_debugger');

        $this->report_issue  = $this->scp->getSetting('scsv_report_issue');

        $this->issue_reported = 0; 
    }

    private function setUser() 
    {
        $csvuser = $this->scp->getSetting('scsv_user');

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
            Throw new \Exception('Invalid Argument');
        }

        $post= $this->scp->getSetting('scsv_post');

        $default_args = array(
            'post_id'=>null,
        );

        $args = array_merge($default_args, $args);

        if($this->debugging) {
            $this->scp->appendToProgressBuffer(Debug::returnShow($args));
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
            else if(!$response)
            {
                $this->debugAndReport($args, new XMLRPC_Error(500, _('There was an issue ingesting post of ID: ' . $this->scp->linkToPost($args['post_id']))));
            }
            else 
            {
                $response = $args['post_id'];
            }
        }
        else if($args['function'] == "wp.newPost") 
        {
            $args = array(
                'blog_id'=> @ $args['blog_id'],
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

        if(@ $content['post_id']) {

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

        $post = $this->scp->getSetting('scsv_post');

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
                $content[$date_key] = new \IXR_Date($content[$date_key]);
            }
        }

        try {
            $success = $this->postContent($content);

        } catch( Exception $e ) {
            
            $err = '<span class="error">'.$e->getMessage().'</span>';
            
            $this->scp->appendToProgressBuffer($err);

            $success = false;;
        }
         
        return $success;
    }

    private function debugAndReport($args, XMLRPC_Error $error) 
    {
        if($this->debugging) 
        {
            $this->debug_output = $error . ' ' .  Debug::returnShow($args);

            $this->scp->appendToProgressBuffer($this->debug_output);

            if($this->report_issue) 
            {
                $this->reportIssue();
            }
        }
    }

    private function xmlencode($data) {

        if($this->scp->getSetting('scsv_encode_special_chars')) {

            $data = utf8_encode($data);
            $data = htmlentities($data);
        }
 
        return $data;
    }

    private function reportIssue() {}
}
