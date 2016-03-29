<?php
use \Phalcon\Mvc\Controller;
use \Phalcon\Config\Adapter\Ini as ConfigIni;
use \Phalcon\Session\Adapter\Files as Session;
use \Phalcon\Paginator\Adapter\NativeArray as Paginator;
use \Phalcon\Mvc\Router;
use \Phalcon\Mvc\Model\Validator;
class IndexController extends Controller
{
	public function indexAction()  //api call
	{	
		$config = new ConfigIni("../app/config/config.ini");
		$start_timeAction = microtime(true);
		$strart_time = $start_timeAction/1000000;
		$page = $this->dispatcher->getParam("page");
		$this->logger->log("[".$config->variables->APP_NAME."]indexaction_start() : page=".$page);
		if (is_null($page) || is_float($page) || is_nan($page) || $page < 1 || $page > 20 || is_array($page)) 
		{
			$page = 1;                                                                  //page validation 
			var_dump("page=".$page);
		}
		$this->view->setVar('page',$page);
		var_dump("page=".$page);
		$mt                 = microtime();
		$rand               = mt_rand();
		$oauth_nonce        = md5($mt . $rand);
		$nonce              = $oauth_nonce;
		$timestamp          = gmdate('UTC');
		$cc_key             = "f1223192eab5461b28cb8a2872c27129"; //consumer key
		$cc_secret          = 'd45dde49cf173525'; //consumer secret key
		$oauth_token        = $this->session->get('values')['oautoken'];
		$oauth_token_secret = $this->session->get('values')['oauth_token_secret'];
		$request_token_url 	= 'https://api.flickr.com/services/rest'; //requesting url
		$sig_method         = "HMAC-SHA1";
		$oversion           = "1.0";
		$page_limit         = $config->variables->ITEMS_PER_PAGE; // 25 pages
		$basestring         = "format=json&method=flickr.interestingness.getList&nojsoncallback=1&oauth_consumer_key=".$cc_key."&oauth_nonce=".$nonce."&oauth_signature_method=".$sig_method."&oauth_timestamp=".$timestamp."&oauth_token=".$oauth_token."&oauth_version=".$oversion;
		$basestring         = "GET&".urlencode($request_token_url)."&".urlencode($basestring);
		$hashkey            = $cc_secret."&".$oauth_token_secret;
		$oauth_signature    = base64_encode(hash_hmac('sha1', $basestring, $hashkey, true));
		$fields = http_build_query(array(                                                 

				'method'                =>'flickr.interestingness.getList',
				'oauth_nonce'           =>$nonce,
				'oauth_timestamp'       =>$timestamp,
				'oauth_consumer_key'    =>$cc_key,
				'oauth_signature_method'=>$sig_method,
				'oauth_version'         =>$oversion,
				'oauth_token'           =>$oauth_token,
				'oauth_signature'       =>$oauth_signature,
				'format'                =>'rest',
				'nojsoncallback'        =>'1',
				'page'                  =>$page,
				'per_page'              =>$page_limit,
				'extras'                =>'date_upload,license,date_taken,owner_name,original_format,last_update,geo,tags,views,media'
		));                                                                                      
		$result = file_get_contents("https://api.flickr.com/services/rest/?".$fields);
		if (!$result) 
		{
			$this->view->pick("error/show105");
		}
		//var_dump($result);
		$file_time = microtime(true);
		$this->logger->log("file time = ".(microtime(true)-$file_time)/1000000); //time calculation
		$this->session->set("url",$result); // send the xml file 
		$this->logger->log("\n Sending request to = ".$request_token_url."?".$fields."\n");
		$end_timeAction = microtime(true)-$start_timeAction;
		$end_time = $end_timeAction/1000000;
		$this->logger->log("Time take in api = ".$end_time);
		return $this->dispatcher->forward([
				'controller'=>'Index',
				'action'=>'interesting'
				]);
	}
	public function interestingAction()
	{
		$config = new ConfigIni("../app/config/config.ini");
		$start_timeAction = microtime(true);
		$strart_time = $start_timeAction/1000000;
		$this->tag->setTitle("Interesting photos on Flickr");
		$photos = $this->session->get('url'); //recevie the xml file
		$RESP = simplexml_load_string($photos); //load the xml file 
		$this->logger->log("[".$config->variables->APP_NAME."]interestingAction_start() \n");
		//var_dump($RESP);
		$images = array();
		foreach($RESP->photos->photo as  $key => $photo)
		{
			$images[] = array(	
								'date_taken'      => date('Y-m-d H:i:s',(int)$photo['datetaken']),
								'date_upload'     => date('Y-m-d H:i:s',"".$photo['dateupload']),
								'owner_name'      => "".htmlentities($photo['ownername']),
								'last_update'     => date('Y-m-d H:i:s',"".$photo['lastupdate']),
								'tags'            => "".$photo['tags'],
								'views'           => "".$photo['views'],
								'media'           => "".$photo['media'],
								'title'           => "".$photo['title'],
								'photo_id'        => "".$photo['id'],
								'url'             => "https://farm2.staticflickr.com/".$photo['server']."/".$photo['id']."_".$photo['secret']."_n.jpg",
								'preview'         => "https://farm2.staticflickr.com/".$photo['server']."/".$photo['id']."_".$photo['secret']."_b.jpg",
								'redirect'        => $this->makeCleanUrl(htmlentities($photo['ownername']))."-".$photo['id']
							);
		}
		$this->session->set('details',$images);
		//$this->view->disable();
		$this->view->setVar('large_images',$images);
		var_dump($images);
		$this->logger->log($config->variables->ITEMS_PER_PAGEs." = urls ");
		$this->logger->log($images['url']);
		$end_timeAction = microtime(true)-$start_timeAction;
		$end_time = $end_timeAction/1000000;
		$this->logger->log("[".$config->variables->APP_NAME."]interestingAction_end() at : time = ".$end_time);
		return $this->view->pick("Index/index");
	}
	public function makeCleanUrl( $string ) 
	{
		$config = new ConfigIni("../app/config/config.ini");
        $this->logger->log("[".$config->variables->APP_NAME."] makeCleanUrl() : START url=[$string]");

        // Trim trailing slashes
        $res = preg_replace("/[^a-zA-Z]/", "", $string);
        var_dump($res);		
		// make lowercase
		$res = strtolower($res);
        $this->logger->log("[".$config->variables->APP_NAME."] makeCleanUrl() : END");
        return $res;
    }
	public function getTokenAction()
	{
		$config = new ConfigIni("../app/config/config.ini");
		$this->logger->log("[".$config->variables->APP_NAME."]gettTokenAction(): action found");
		$mt                = microtime();
		$rand              = mt_rand();
		$oauth_nonce       = md5($mt . $rand);
		$request_token_url = "http://www.flickr.com/services/oauth/request_token";
		$nonce             = $oauth_nonce;
		$timestamp         = gmdate('UTC'); //It must be UTC time
		$cc_key            = "f1223192eab5461b28cb8a2872c27129";
		$cc_secret         = "d45dde49cf173525";
		$sig_method        = "HMAC-SHA1";
		$oversion          = "1.0";
		$callbackURL       = "http://192.168.1.15/training/training_app/flickr/display";
		$basestring 	   = "oauth_callback=".urlencode($callbackURL)."&oauth_consumer_key=".$cc_key."&oauth_nonce=".$nonce."&oauth_signature_method=".$sig_method."&oauth_timestamp=".$timestamp."&oauth_version=".$oversion;
		$baseurl           = "GET&".urlencode($request_token_url)."&".urlencode($basestring);
		$hashkey           = $cc_secret."&";
		$oauth_signature   = base64_encode(hash_hmac('sha1', $baseurl, $hashkey, true));
		$fields = array(
		           'oauth_nonce'            =>$nonce,
		           'oauth_timestamp'        =>$timestamp,
		           'oauth_consumer_key'     =>$cc_key,
		           'oauth_signature_method'	=>$sig_method,
		           'oauth_version'          =>$oversion,
		           'oauth_signature'        =>$oauth_signature,
		           'oauth_callback'         =>$callbackURL
		     );
		$fields_string = "";
		foreach($fields as $key=>$value) {              
			$fields_string .= "$key=".urlencode($value)."&";
		}
		$this->logger->log($fields_string);

		$fields_string = rtrim($fields_string,'&');
		$url = $request_token_url."?".$fields_string;

		$ch         = curl_init(); 
		$timeout    = 5; // set to zero for no timeout 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		
		$file_contents = curl_exec($ch); 
		curl_close($ch); 

		$rsp_arr = explode('&',$file_contents); 
		print "<pre>";
		print_r($rsp_arr); die;
	}
		//user authontication 'http://www.flickr.com/services/oauth/authorize?oauth_token=72157662893239264-7ed0e3d9b36427b4=delete'; 

	public function accessTokenAction()
	{
		$config = new ConfigIni("../app/config/config.ini");
		$this->logger->log("[".$config->variables->APP_NAME."]accessTokenAction(): action found");

		$sess_arr	= array(
							'oautoken'=>'72157665093870062-736796ab8decc5eb',
							'oauth_token_secret'=>'98279c12ca6dd099'
							);
		$this->session->set('values',$sess_arr);
		$passed_values = $this->session->get('values');
		$mt                    = microtime();
		$rand                  = mt_rand();
		$oauth_nonce           = md5($mt . $rand);
		$request_token_url     = "http://www.flickr.com/services/oauth/access_token";
		$nonce                 = $oauth_nonce;
		$timestamp             = gmdate('UTC'); //It must be UTC time
		$cc_key                = "f1223192eab5461b28cb8a2872c27129";
		$cc_secret             = "d45dde49cf173525";
		$sig_method            = "HMAC-SHA1";
		$oversion              = "1.0";
		$oauth_token           = $this->session->get('values')['oautoken'];
		$request_token_url 	   = 'http://www.flickr.com/services/oauth/access_token';
		$oauth_verifier	       = 'a52216d0fb033ac';
		$oauth_token_secret    = $this->session->get('values')['oauth_token_secret'];
		//var_dump($oauth_token);
		$basestring = "oauth_consumer_key=".$cc_key."&oauth_nonce=".$nonce."&oauth_signature_method=".$sig_method."&oauth_timestamp=".$timestamp."&oauth_token=".$oauth_token."&oauth_verifier=".$oauth_verifier."&oauth_version=".$oversion;

		$basestring = "GET&".urlencode($request_token_url)."&".urlencode($basestring);
		$hashkey = $cc_secret."&".$oauth_token_secret;

		$oauth_signature = base64_encode(hash_hmac('sha1', $basestring, $hashkey, true));

		$fields = array(

				'oauth_nonce'			=>$nonce,
				'oauth_timestamp'		=>$timestamp,
				'oauth_verifier'		=>$oauth_verifier,
				'oauth_consumer_key'	=>$cc_key,
				'oauth_signature_method'=>$sig_method,
				'oauth_version'			=>$oversion,
				'oauth_token' 			=> $oauth_token,
				'oauth_signature'		=>$oauth_signature 
		);

		$fields_string = "";
		foreach($fields as $key=>$value)    
			$fields_string .= "$key=".urlencode($value)."&";
		$fields_string = rtrim($fields_string,'&');
		$url = $request_token_url."?".$fields_string;
		$ch  = curl_init(); 
		$timeout  = 5; 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout); 
		$file_contents = curl_exec($ch); 
		$this->logger->log($file_contents);	
		$rsp_arr = explode('&',$file_contents); 
		$this->logger->log("[".$config->variables->APP_NAME."accessTokenAction(): action end");
		$info = curl_getinfo($ch);
		print "<pre>";
		print_r($file_contents);
		die;
		curl_close($ch); 
	}

}