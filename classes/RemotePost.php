<?php
require_once('Debug.php');
require_once('SupraCsvPlugin.php');

class RemotePost extends SupraCsvPlugin {
    private $client;
    private $uname;
    private $pass;
    private $postId;
    private $debugging, $debug_output, $report_issue, $issue_reported;
    private $admin_email = "zmijevik@hotmail.com";

    function __construct() {
        parent::__construct();
     
        include ABSPATH . 'wp-includes/class-IXR.php';
        $this->setUser();       
        $pingback            = $this->getPluginDirUrl() . "/xmlrpc/supra_xmlrpc.php";
        $this->client        = new IXR_Client($pingback);
        $this->debugging     = get_option('scsv_ingest_debugger');
        $this->report_issue  = get_option('scsv_report_issue');
        $this->client->debug = $this->debugging;
        $this->issue_reported = 0; 
    }

    private function setUser() {
        $csvuser = get_option('scsv_user');
        $this->uname = $csvuser['name'];
        $this->pass  = $csvuser['pass'];
    }

    private function _makeCall($args) {

        if(!is_array($args['args'])) throw new Exception('Invalid Argument');

        $post= get_option('scsv_post');

        $default_args = array(
                              'post_id'=>null,
                             );

        $args = array_merge($default_args, $args);

        if($this->debugging) {
            Debug::show($args);
        }

        if($args['function'] == "wp.newPost") {
            if(!$this->client->query($args['function'],$args['post_id'],$this->uname,$this->pass,$args['args'])) {
               echo $this->debugAndReport($args, $this->client->getErrorMessage());
               throw new Exception($this->client->getErrorMessage());
            }
        } else if($args['function'] == "wp.setOptions") {
            if(!$this->client->query($args['function'],$args['post_id'],$this->uname,$this->pass,$args['args'])) {
               echo $this->debugAndReport($args, $this->client->getErrorMessage());
               throw new Exception($this->client->getErrorMessage());
            }
        }

        return $this->client->getResponse();
    }

    public function postContent($content) {

        $args = array(
                      'function'=>'wp.newPost',
                      'args'    =>$content, 
                     );

        $response = $this->_makeCall($args);

        if($response) {
            $this->postId = $response;
        }
   
        return $response;
    }

    public function postOptions($options) {

        $args = array(
                      'function'=>'wp.setOptions',
                      'post_id' => $this->postId,
                      'args'    =>$options,
                      'publish' =>null
                     );

        return $this->_makeCall($args);
    }

    public function postContentAndOptions($content,$options) {

        $response['content'] = $this->postContent($content);
        $response['options'] = $this->postOptions($options);
       
        return $response;
    }

    public function injectListing($args) {

        foreach((array)$args['custom_fields'] as $k=>$v) {
            $custom_fields[] = array('key'=>$k,'value'=>$v);
        }

        $post = get_option('scsv_post');

        //the keys to filter by
        $params = array(
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
                        'enclosure' 
                       );
   
        //filter the args into valiables 
        foreach($params as $param) {
            if(empty($args[$param]))
                $$param = $post[$param];
            else
                $$param = $args[$param];
        }

        //compact the variables
        $content = compact(
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
                        'enclosure'
        );

        //if the argument isnt empty than set it
        if(!empty($post_content)) {
            $content['post_content'] = $post_content;
        }
        //if the argument was empty but the configuration isnt
        else if(!empty($post['desc'])) {
            $content['post_content'] = $post['desc'];
        }

        //set the title to the specified configuration if its empty
        if(empty($content['post_title'])) {
            $content['post_content'] = $post['title'];
        }

        //add the custom terms
        

        if($post['publish'] && empty($content['post_status'])) {
            $content['post_status'] = 'publish';
            
        }
        else {
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
