
class NxApiResponse
{
  /**
   * Response object from the API
   * @var object
   */
  public $response;
  
  /**
   * HTTP response status code
   * @var int
   */
  public $httpStatus;
  
  /**
   * Indicates successful transport - 200 OK and valid JSON
   * @var bool
   */
  public $OK;
  
  /**
   * HTTP error message, if any
   * @var string
   */
  public $httpErrorMessage = "";
  
  /**
   * Indicates a successful API call
   * @var bool
   */
  public $success;
  
  /**
   * API error code, if any
   * @var int
   */
  public $apiErrorCode;
  
  /**
   * API error message, if any
   * @var string
   */
  public $apiErrorMessage;
  
  /**
   * reference to the resource returned by curl_init
   * @var resource
   */
  public $curl;
  
  /**
   * If the call had some warnings
   * @var bool
   */
  public $hasWarnings;
  
  /**
   * reference to the warnings contained in the response object
   * @var array
   */
  public $warnings;
   /**
    * the url of the call
    * @var string
    */
   public $url;
   /**
    * the post array that was passed to the call
    * @var array
    */
   public $postVars;
  
  public function __construct(&$curlReference,$url,$post_vars)
  {
    $this->curl = $curlReference;
    $this->url = $url;
    $this->postVars = $post_vars;
    $response = curl_exec($this->curl);
    curl_close($this->curl);
    $this->httpStatus = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
    if($this->httpStatus != 200)
    {
      $this->OK = false;
      $this->httpErrorMessage = curl_error($this->curl);
      $this->postVars="[****]";
      $this->response = $response;
    }
    else
    {

      try
      {
        $this->response = json_decode($response);
        
                if(!$this->response){
                   $this->OK = false;
                   $this->httpErrorMessage = "Response Invalid JSON, undecodable";
                   $this->response = $response;
                   $this->responseError='';
                   $this->errorCode=0;
                   return;
                }
        $this->OK = true;
        $this->success = $this->response->success;
        if(!$this->success)
        {
          $this->apiErrorMessage = $this->response->error->message;
          $this->apiErrorCode = $this->response->error->code;
        }
        else
        {
          $this->apiErrorMessage = '';
          $this->apiErrorCode = 0;
        }

        if(isset($this->response->warnings) && $this->response->warnings)
        {
          $this->hasWarnings = true;
          $this->warnings = $this->response->warnings;
        }
        else
        {
          $this->hasWarnings = false;
          $this->warnings = array();
        }
      }
      catch(Exception $x)
      {
        $this->response = null;
        $this->OK = false;
        $this->httpErrorMessage = "Response Invalid JSON, an exception was catched";
        $this->responseError='';
        $this->errorCode=0;

      }
    }

  }

}

class NxApi
{
  
   public static $instance = "";
   public static $key = "";
   public static $url = "";
   public static $token = "";
   public static $filterId = "";
  
  public static function setInstance($instanceName)
  {
    NxApi::$instance = $instanceName;	
  }
  public static function setKey($key)
  {
      NxApi::$key = $key;   
  }
  public static function setToken($token)
  {
      NxApi::$token = $token;  
  }
  public static function setFilterId($filterId)
  {
      NxApi::$filterId = $filterId;
  }
  
  /**
   * @return NexaApiResponse The executed and decoded cUrl in a wrapper;
   */
  public static function call($url,$data = array())
  {
      $ch = curl_init();
      if(NxApi::$token)
      {
         $data["token"] = NxApi::$token;
      }
      else{
         $data["api_key"] = NxApi::$key;
         if($data["api_key"] == "" && defined("NEXA_API_KEY")){
            $data["api_key"] = NEXA_API_KEY;
         }
      }
      
      if(NxApi::$filterId != "" && !isset($data["filterId"])){
        $data["filterId"] = NxApi::$filterId;
      }
      $data["instance"] = NxApi::$instance;
      
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
      curl_setopt($ch, CURLOPT_POST, true);
      curl_setopt($ch, CURLOPT_URL, (NxApi::$url?NxApi::$url:NEXA_API_URL) . $url);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
      
      if(defined("SSL_USERNAME") && defined("SSL_PASSWORD")){
        curl_setopt($ch, CURLOPT_USERPWD, SSL_USERNAME . ":" . SSL_PASSWORD);
      }
      
      return new NxApiResponse($ch,$url,$data);
  }	
}	
    
class NxApiAccount
{        
   
   public static function create($username, $providerName, $options=array('key'=>''))
   {
      $url = '/account/create';
      
      $post = array(
			'username'=>$username,
			'providerName'=>$providerName,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($accountId, $options=array('username'=>'', 'key'=>''))
   {
      $url = '/account/modify';
      
      $post = array(
			'accountId'=>$accountId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($accountId, $options=array())
   {
      $url = '/account/remove';
      
      $post = array(
			'accountId'=>$accountId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($accountId, $options=array())
   {
      $url = '/account/get';
      
      $post = array(
			'accountId'=>$accountId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('userId'=>'', 'domainGroupId'=>'', 'providerId'=>'', 'username'=>'', 'status'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/account/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getSearches($accountId, $options=array())
   {
      $url = '/account/getSearches';
      
      $post = array(
			'accountId'=>$accountId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAccountOauth
{        
   
   public static function create($accountId, $accountOauthAppId, $userToken, $userSecret, $successUrl, $errorUrl, $options=array())
   {
      $url = '/accountOauth/create';
      
      $post = array(
			'accountId'=>$accountId,
			'accountOauthAppId'=>$accountOauthAppId,
			'userToken'=>$userToken,
			'userSecret'=>$userSecret,
			'successUrl'=>$successUrl,
			'errorUrl'=>$errorUrl,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($accountOauthId, $options=array('accountId'=>'', 'accountOauthAppId'=>'', 'userToken'=>'', 'userSecret'=>'', 'successUrl'=>'', 'errorUrl'=>''))
   {
      $url = '/accountOauth/modify';
      
      $post = array(
			'accountOauthId'=>$accountOauthId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($accountOauthId, $options=array())
   {
      $url = '/accountOauth/get';
      
      $post = array(
			'accountOauthId'=>$accountOauthId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('accountId'=>'', 'accountOauthAppId'=>'', 'userToken'=>'', 'userSecret'=>'', 'successUrl'=>'', 'errorUrl'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/accountOauth/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($accountOauthId, $options=array())
   {
      $url = '/accountOauth/remove';
      
      $post = array(
			'accountOauthId'=>$accountOauthId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/accountOauth/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAccountOauthLogin
{        
   
   public static function create($providerName, $domainId, $callbackUrl, $options=array('userId'=>'', 'instance'=>'', 'creationDate'=>''))
   {
      $url = '/accountOauthLogin/create';
      
      $post = array(
			'providerName'=>$providerName,
			'domainId'=>$domainId,
			'callbackUrl'=>$callbackUrl,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($id, $options=array('userId'=>'', 'providerName'=>'', 'domainId'=>'', 'instance'=>'', 'callbackUrl'=>'', 'creationDate'=>''))
   {
      $url = '/accountOauthLogin/modify';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($id, $options=array())
   {
      $url = '/accountOauthLogin/get';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('userId'=>'', 'providerName'=>'', 'domainId'=>'', 'instance'=>'', 'callbackUrl'=>'', 'creationDate'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/accountOauthLogin/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($id, $options=array())
   {
      $url = '/accountOauthLogin/remove';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/accountOauthLogin/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAccountProvider
{        
   
   public static function create($providerName, $type, $options=array())
   {
      $url = '/accountProvider/create';
      
      $post = array(
			'providerName'=>$providerName,
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($id, $options=array('providerName'=>'', 'type'=>''))
   {
      $url = '/accountProvider/modify';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($id, $options=array())
   {
      $url = '/accountProvider/get';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('providerName'=>'', 'type'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/accountProvider/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($id, $options=array())
   {
      $url = '/accountProvider/remove';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/accountProvider/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAlert
{        
   
   public static function get($projectId, $alertId, $options=array())
   {
      $url = '/alert/get';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/alert/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getRecordedAlerts($projectId, $options=array())
   {
      $url = '/alert/getRecordedAlerts';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getDocumentsForRecordedAlerts($projectId, $detected_alert_id, $options=array())
   {
      $url = '/alert/getDocumentsForRecordedAlerts';
      
      $post = array(
			'projectId'=>$projectId,
			'detected_alert_id'=>$detected_alert_id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($projectId, $alertName, $rule, $options=array('threshold'=>'', 'activated'=>'', 'interval'=>'', 'send_email'=>'', 'search_terms_included'=>'', 'alertEmailList'=>'', 'apply_to_all'=>''))
   {
      $url = '/alert/create';
      
      $post = array(
			'projectId'=>$projectId,
			'alertName'=>$alertName,
			'rule'=>$rule,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($projectId, $alertId, $options=array())
   {
      $url = '/alert/delete';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($projectId, $alertId, $options=array('alertName'=>'', 'rule'=>'', 'threshold'=>'', 'activated'=>'', 'interval'=>'', 'send_email'=>'', 'search_terms_included'=>'', 'alertEmailList'=>'', 'apply_to_all'=>''))
   {
      $url = '/alert/modify';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function check($projectId, $alertId, $options=array())
   {
      $url = '/alert/check';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function testEmail($projectId, $alertId, $options=array())
   {
      $url = '/alert/testEmail';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAutoCapture
{        
   
   public static function run($projectId, $options=array())
   {
      $url = '/autoCapture/run';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function start($projectId, $interval, $options=array())
   {
      $url = '/autoCapture/start';
      
      $post = array(
			'projectId'=>$projectId,
			'interval'=>$interval,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function reschedule($projectId, $interval, $options=array())
   {
      $url = '/autoCapture/reschedule';
      
      $post = array(
			'projectId'=>$projectId,
			'interval'=>$interval,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function stop($projectId, $options=array())
   {
      $url = '/autoCapture/stop';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function stopAll($options=array('apiKeyOfUser'=>'', 'instanceName'=>''))
   {
      $url = '/autoCapture/stopAll';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function status($projectId, $options=array())
   {
      $url = '/autoCapture/status';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiAutomatedReport
{        
   
   public static function addToAlert($projectId, $alertId, $automatedReportTemplateId, $options=array('filterId'=>''))
   {
      $url = '/automatedReport/addToAlert';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
			'automatedReportTemplateId'=>$automatedReportTemplateId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function removeFromAlert($projectId, $alertId, $automatedReportTemplateId, $filterId, $options=array())
   {
      $url = '/automatedReport/removeFromAlert';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
			'automatedReportTemplateId'=>$automatedReportTemplateId,
			'filterId'=>$filterId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getListForAlert($projectId, $alertId, $options=array())
   {
      $url = '/automatedReport/getListForAlert';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/automatedReport/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $id, $options=array())
   {
      $url = '/automatedReport/get';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($projectId, $name, $options=array('intervalEvaluated'=>'', 'scheduleCrontab'=>'', 'scheduleActive'=>'', 'scheduleFilterId'=>'', 'scheduleStartOn'=>'', 'scheduleRecipientList'=>'', 'reportList'=>'', 'reportTypes'=>''))
   {
      $url = '/automatedReport/create';
      
      $post = array(
			'projectId'=>$projectId,
			'name'=>$name,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($projectId, $id, $name, $options=array('intervalEvaluated'=>'', 'scheduleCrontab'=>'', 'scheduleActive'=>'', 'scheduleFilterId'=>'', 'scheduleStartOn'=>'', 'scheduleRecipientList'=>'', 'reportList'=>'', 'reportTypes'=>''))
   {
      $url = '/automatedReport/modify';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
			'name'=>$name,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($projectId, $id, $options=array())
   {
      $url = '/automatedReport/delete';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function generateForAlert($projectId, $alertId, $options=array())
   {
      $url = '/automatedReport/generateForAlert';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function loadRenderObjsFile($projectId, $filename, $options=array())
   {
      $url = '/automatedReport/loadRenderObjsFile';
      
      $post = array(
			'projectId'=>$projectId,
			'filename'=>$filename,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function generateAnalyzeDoc($projectId, $metas, $reportsConfigs, $options=array())
   {
      $url = '/automatedReport/generateAnalyzeDoc';
      
      $post = array(
			'projectId'=>$projectId,
			'metas'=>$metas,
			'reportsConfigs'=>$reportsConfigs,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function generate($projectId, $id, $reason, $filename, $options=array('filterDescription'=>'', 'notify'=>''))
   {
      $url = '/automatedReport/generate';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
			'reason'=>$reason,
			'filename'=>$filename,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getSnapshotList($projectId, $id, $options=array())
   {
      $url = '/automatedReport/getSnapshotList';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getLastSnapshotsForAlert($projectId, $alertId, $options=array())
   {
      $url = '/automatedReport/getLastSnapshotsForAlert';
      
      $post = array(
			'projectId'=>$projectId,
			'alertId'=>$alertId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function downloadSnapshot($projectId, $id, $filename, $options=array())
   {
      $url = '/automatedReport/downloadSnapshot';
      
      $post = array(
			'projectId'=>$projectId,
			'id'=>$id,
			'filename'=>$filename,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/automatedReport/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiBrandwatch
{        
   
   public static function create($hostname, $instance, $projectId, $searchId, $options=array('id'=>'', 'brandwatchQueryId'=>'', 'brandwatchProjectId'=>'', 'brandwatchOptions'=>'', 'status'=>'', 'isHistorical'=>'', 'startDate'=>'', 'endDate'=>''))
   {
      $url = '/brandwatch/create';
      
      $post = array(
			'hostname'=>$hostname,
			'instance'=>$instance,
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($options=array('id'=>'', 'hostname'=>'', 'instance'=>'', 'projectId'=>'', 'searchId'=>'', 'brandwatchQueryId'=>'', 'brandwatchProjectId'=>'', 'brandwatchOptions'=>'', 'status'=>'', 'isHistorical'=>'', 'startDate'=>'', 'endDate'=>''))
   {
      $url = '/brandwatch/modify';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($options=array())
   {
      $url = '/brandwatch/get';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('id'=>'', 'hostname'=>'', 'instance'=>'', 'projectId'=>'', 'searchId'=>'', 'brandwatchQueryId'=>'', 'brandwatchProjectId'=>'', 'brandwatchOptions'=>'', 'status'=>'', 'isHistorical'=>'', 'startDate'=>'', 'endDate'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/brandwatch/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($options=array())
   {
      $url = '/brandwatch/remove';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/brandwatch/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiCaptureApi
{        
   
   public static function subscribeProject($projectId, $options=array('searchId'=>''))
   {
      $url = '/captureApi/subscribeProject';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function unsubscribeProject($projectId, $options=array())
   {
      $url = '/captureApi/unsubscribeProject';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/captureApi/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiCategory
{        
   
   public static function get($categoryId, $options=array())
   {
      $url = '/category/get';
      
      $post = array(
			'categoryId'=>$categoryId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/category/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($categoryName, $words, $options=array())
   {
      $url = '/category/create';
      
      $post = array(
			'categoryName'=>$categoryName,
			'words'=>$words,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($categoryId, $options=array())
   {
      $url = '/category/delete';
      
      $post = array(
			'categoryId'=>$categoryId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($categoryId, $options=array('categoryName'=>'', 'words'=>''))
   {
      $url = '/category/modify';
      
      $post = array(
			'categoryId'=>$categoryId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiDictionary
{        
   
   public static function getWords($partOfWord, $languageTag, $options=array())
   {
      $url = '/dictionary/getWords';
      
      $post = array(
			'partOfWord'=>$partOfWord,
			'languageTag'=>$languageTag,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function autocomplete($partOfWord, $languageTag, $options=array('noStopWords'=>'', 'limit'=>''))
   {
      $url = '/dictionary/autocomplete';
      
      $post = array(
			'partOfWord'=>$partOfWord,
			'languageTag'=>$languageTag,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function addCustom($projectId, $wordId, $options=array('replacement'=>'', 'regroupWithCustomWordId'=>'', 'isStopword'=>''))
   {
      $url = '/dictionary/addCustom';
      
      $post = array(
			'projectId'=>$projectId,
			'wordId'=>$wordId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modifyCustom($projectId, $customWordId, $options=array('wordId'=>'', 'replacement'=>'', 'regroupWithCustomWordId'=>'', 'isStopword'=>''))
   {
      $url = '/dictionary/modifyCustom';
      
      $post = array(
			'projectId'=>$projectId,
			'customWordId'=>$customWordId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function deleteCustom($projectId, $customWordId, $options=array())
   {
      $url = '/dictionary/deleteCustom';
      
      $post = array(
			'projectId'=>$projectId,
			'customWordId'=>$customWordId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getCustomList($projectId, $options=array())
   {
      $url = '/dictionary/getCustomList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function cleanCustom($projectId, $options=array())
   {
      $url = '/dictionary/cleanCustom';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function applyCsvCustom($projectId, $file, $options=array())
   {
      $url = '/dictionary/applyCsvCustom';
      
      $post = array(
			'projectId'=>$projectId,
			'file'=>$file,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function exportCsv($projectId, $options=array())
   {
      $url = '/dictionary/exportCsv';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getWordList($wordId, $options=array('projectId'=>''))
   {
      $url = '/dictionary/getWordList';
      
      $post = array(
			'wordId'=>$wordId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/dictionary/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiDocument
{        
   
   public static function delete($projectId, $fileName, $options=array())
   {
      $url = '/document/delete';
      
      $post = array(
			'projectId'=>$projectId,
			'fileName'=>$fileName,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function renameFile($projectId, $actualFileName, $newFileName, $options=array())
   {
      $url = '/document/renameFile';
      
      $post = array(
			'projectId'=>$projectId,
			'actualFileName'=>$actualFileName,
			'newFileName'=>$newFileName,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $dataId, $options=array('getComment'=>''))
   {
      $url = '/document/get';
      
      $post = array(
			'projectId'=>$projectId,
			'dataId'=>$dataId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('filterDescription'=>'', 'wordIds'=>'', 'dataIds'=>'', 'publisherIds'=>'', 'dimensions'=>'', 'withTotalCount'=>'', 'removeDuplicates'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/document/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function saveMetadata($projectId, $dataId, $status, $comment, $options=array())
   {
      $url = '/document/saveMetadata';
      
      $post = array(
			'projectId'=>$projectId,
			'dataId'=>$dataId,
			'status'=>$status,
			'comment'=>$comment,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiExport
{        
   
   public static function download($projectId, $fileId, $options=array())
   {
      $url = '/export/download';
      
      $post = array(
			'projectId'=>$projectId,
			'fileId'=>$fileId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function adminDownload($projectId, $fileId, $options=array())
   {
      $url = '/export/adminDownload';
      
      $post = array(
			'projectId'=>$projectId,
			'fileId'=>$fileId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function reportAsCsv($projectId, $reportType, $options=array())
   {
      $url = '/export/reportAsCsv';
      
      $post = array(
			'projectId'=>$projectId,
			'reportType'=>$reportType,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function data($projectId, $format, $options=array('start'=>'', 'file'=>'', 'limit'=>'', 'options'=>'', 'filterDescription'=>''))
   {
      $url = '/export/data';
      
      $post = array(
			'projectId'=>$projectId,
			'format'=>$format,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function project($projectId, $options=array())
   {
      $url = '/export/project';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiFeature
{        
   
   public static function changeAccess($instanceId, $featureId, $access, $options=array())
   {
      $url = '/feature/changeAccess';
      
      $post = array(
			'instanceId'=>$instanceId,
			'featureId'=>$featureId,
			'access'=>$access,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/feature/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getInstanceAccess($options=array('instance'=>'', 'instanceId'=>''))
   {
      $url = '/feature/getInstanceAccess';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiFilter
{        
   
   public static function compileFilter($projectId, $options=array('filterId'=>'', 'minDataId'=>'', 'maxDataId'=>'', 'dataIds'=>'', 'publisherIds'=>'', 'generateForAllFilters'=>''))
   {
      $url = '/filter/compileFilter';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($projectId, $filter, $options=array())
   {
      $url = '/filter/create';
      
      $post = array(
			'projectId'=>$projectId,
			'filter'=>$filter,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($projectId, $filter, $options=array())
   {
      $url = '/filter/modify';
      
      $post = array(
			'projectId'=>$projectId,
			'filter'=>$filter,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/filter/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $filterId, $format, $options=array())
   {
      $url = '/filter/get';
      
      $post = array(
			'projectId'=>$projectId,
			'filterId'=>$filterId,
			'format'=>$format,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($projectId, $filterId, $options=array())
   {
      $url = '/filter/delete';
      
      $post = array(
			'projectId'=>$projectId,
			'filterId'=>$filterId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function setForProject($projectId, $filterId, $options=array())
   {
      $url = '/filter/setForProject';
      
      $post = array(
			'projectId'=>$projectId,
			'filterId'=>$filterId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiGeoplaces
{        
   
   public static function create($options=array('name'=>''))
   {
      $url = '/geoplaces/create';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($id, $options=array('name'=>''))
   {
      $url = '/geoplaces/modify';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($id, $options=array())
   {
      $url = '/geoplaces/get';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('name'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/geoplaces/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($id, $options=array())
   {
      $url = '/geoplaces/remove';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function autocomplete($name, $options=array('limit'=>''))
   {
      $url = '/geoplaces/autocomplete';
      
      $post = array(
			'name'=>$name,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/geoplaces/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiImport
{        
   
   public static function search($projectId, $options=array('searchIds'=>'', 'callbackUrl'=>'', 'reset'=>'', 'regenerateLexicon'=>''))
   {
      $url = '/import/search';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function hydrateTweets($projectId, $options=array())
   {
      $url = '/import/hydrateTweets';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function file($projectId, $file, $dataType, $options=array('reset'=>'', 'callbackUrl'=>'', 'regenerateLexicon'=>'', 'waitForPreprocessing'=>'', 'strict'=>''))
   {
      $url = '/import/file';
      
      $post = array(
			'projectId'=>$projectId,
			'file'=>$file,
			'dataType'=>$dataType,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function augmentation($projectId, $data, $options=array())
   {
      $url = '/import/augmentation';
      
      $post = array(
			'projectId'=>$projectId,
			'data'=>$data,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiIngestion
{        
   
   public static function retrieveProjects($options=array('destination'=>'', 'searchHash'=>''))
   {
      $url = '/ingestion/retrieveProjects';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function ingestMessagesInProjects($jsonMessages, $options=array('destination'=>'', 'searchHash'=>''))
   {
      $url = '/ingestion/ingestMessagesInProjects';
      
      $post = array(
			'jsonMessages'=>$jsonMessages,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/ingestion/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiInstance
{        
   
   public static function create($name, $label, $hostname, $options=array())
   {
      $url = '/instance/create';
      
      $post = array(
			'name'=>$name,
			'label'=>$label,
			'hostname'=>$hostname,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function createAsync($name, $label, $options=array('hostname'=>''))
   {
      $url = '/instance/createAsync';
      
      $post = array(
			'name'=>$name,
			'label'=>$label,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function update($name, $label, $options=array('hostname'=>''))
   {
      $url = '/instance/update';
      
      $post = array(
			'name'=>$name,
			'label'=>$label,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function grantAccess($userId, $instanceId, $domainId, $options=array())
   {
      $url = '/instance/grantAccess';
      
      $post = array(
			'userId'=>$userId,
			'instanceId'=>$instanceId,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function removeAccess($userId, $instanceId, $domainId, $options=array())
   {
      $url = '/instance/removeAccess';
      
      $post = array(
			'userId'=>$userId,
			'instanceId'=>$instanceId,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($instanceId, $options=array())
   {
      $url = '/instance/delete';
      
      $post = array(
			'instanceId'=>$instanceId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('domainId'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/instance/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getAccessList($options=array('domainId'=>''))
   {
      $url = '/instance/getAccessList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiJobTemplate
{        
   
   public static function get($jobTemplateId, $options=array())
   {
      $url = '/jobTemplate/get';
      
      $post = array(
			'jobTemplateId'=>$jobTemplateId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('public'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/jobTemplate/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($name, $comment, $jobsDescription, $options=array('public'=>''))
   {
      $url = '/jobTemplate/create';
      
      $post = array(
			'name'=>$name,
			'comment'=>$comment,
			'jobsDescription'=>$jobsDescription,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($jobTemplateId, $name, $comment, $jobsDescription, $options=array('public'=>''))
   {
      $url = '/jobTemplate/modify';
      
      $post = array(
			'jobTemplateId'=>$jobTemplateId,
			'name'=>$name,
			'comment'=>$comment,
			'jobsDescription'=>$jobsDescription,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function execute($jobTemplateId, $variables, $options=array('globalDynamicFilterDescription'=>''))
   {
      $url = '/jobTemplate/execute';
      
      $post = array(
			'jobTemplateId'=>$jobTemplateId,
			'variables'=>$variables,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function start($jobTemplateId, $jobTemplateVariables, $options=array('jobTemplateDynamicFilter'=>'', 'autoCaptureId'=>'', 'projectId'=>'', 'schedule'=>''))
   {
      $url = '/jobTemplate/start';
      
      $post = array(
			'jobTemplateId'=>$jobTemplateId,
			'jobTemplateVariables'=>$jobTemplateVariables,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function stop($options=array('jobTemplateScheduledId'=>'', 'autoCaptureId'=>'', 'projectId'=>''))
   {
      $url = '/jobTemplate/stop';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function status($options=array('jobTemplateScheduledId'=>'', 'autocaptureId'=>'', 'projectId'=>''))
   {
      $url = '/jobTemplate/status';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/jobTemplate/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiJobs
{        
   
   public static function status($jobId, $options=array())
   {
      $url = '/jobs/status';
      
      $post = array(
			'jobId'=>$jobId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getArchivedJob($jobId, $options=array())
   {
      $url = '/jobs/getArchivedJob';
      
      $post = array(
			'jobId'=>$jobId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/jobs/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiJs
{        
   
   public static function getScript($uuid, $options=array())
   {
      $url = '/js/getScript';
      
      $post = array(
			'uuid'=>$uuid,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getToken($uuid, $options=array())
   {
      $url = '/js/getToken';
      
      $post = array(
			'uuid'=>$uuid,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiMetadata
{        
   
   public static function getFields($projectId, $type, $options=array('fieldId'=>''))
   {
      $url = '/metadata/getFields';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function addField($projectId, $type, $fieldName, $fieldDescription, $options=array('orderId'=>''))
   {
      $url = '/metadata/addField';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'fieldName'=>$fieldName,
			'fieldDescription'=>$fieldDescription,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function updateField($projectId, $type, $fieldId, $options=array('fieldName'=>'', 'fieldDescription'=>'', 'fieldDescriptionOldPart'=>'', 'fieldDescriptionNewPart'=>'', 'orderId'=>''))
   {
      $url = '/metadata/updateField';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'fieldId'=>$fieldId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function deleteField($projectId, $type, $fieldId, $options=array())
   {
      $url = '/metadata/deleteField';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'fieldId'=>$fieldId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function clear($projectId, $options=array())
   {
      $url = '/metadata/clear';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function import($projectId, $options=array('file'=>'', 'options'=>''))
   {
      $url = '/metadata/import';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/metadata/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiProcessData
{        
   
   public static function processNERD($projectId, $options=array())
   {
      $url = '/processData/processNERD';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function reprocessAspect($projectId, $options=array())
   {
      $url = '/processData/reprocessAspect';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiProject
{        
   
   public static function testSendEmail($email, $content, $options=array())
   {
      $url = '/project/testSendEmail';
      
      $post = array(
			'email'=>$email,
			'content'=>$content,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function duplicate($projectId, $options=array('name'=>'', 'destinationInstance'=>'', 'fork'=>''))
   {
      $url = '/project/duplicate';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function duplicateWithData($projectId, $options=array('name'=>'', 'destinationInstance'=>''))
   {
      $url = '/project/duplicateWithData';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function copyData($projectId, $options=array('toProjectId'=>'', 'destinationInstance'=>''))
   {
      $url = '/project/copyData';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function cloneWithData($projectId, $options=array('name'=>'', 'destinationInstance'=>''))
   {
      $url = '/project/cloneWithData';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($name, $lang, $type, $options=array('projectLeader'=>'', 'projectManager'=>'', 'client'=>'', 'maxTopWords'=>'', 'maxTopCoWords'=>'', 'maxTopPublishers'=>'', 'isPublic'=>'', 'userDefinedId'=>'', 'userDefinedStatusId'=>'', 'userDefinedFolderId'=>'', 'isStreamingProject'=>''))
   {
      $url = '/project/create';
      
      $post = array(
			'name'=>$name,
			'lang'=>$lang,
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function createFromProjectData($projectId, $projectName, $dataSource, $options=array())
   {
      $url = '/project/createFromProjectData';
      
      $post = array(
			'projectId'=>$projectId,
			'projectName'=>$projectName,
			'dataSource'=>$dataSource,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('status'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/project/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getAutocaptureList($options=array('status'=>''))
   {
      $url = '/project/getAutocaptureList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getPreprocessingState($projectId, $options=array())
   {
      $url = '/project/getPreprocessingState';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function reprocess($projectId, $options=array('reprocessingState'=>''))
   {
      $url = '/project/reprocess';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function waitForPreprocessing($projectId, $options=array('waitType'=>''))
   {
      $url = '/project/waitForPreprocessing';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function resultsByQuery($projectId, $options=array())
   {
      $url = '/project/resultsByQuery';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getProjectDataDates($projectId, $options=array())
   {
      $url = '/project/getProjectDataDates';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $options=array())
   {
      $url = '/project/get';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($projectId, $options=array('name'=>'', 'lang'=>'', 'projectLeader'=>'', 'projectManager'=>'', 'client'=>'', 'maxTopWords'=>'', 'maxTopCoWords'=>'', 'maxTopPublisher'=>'', 'isPublic'=>'', 'userDefinedId'=>'', 'userDefinedStatusId'=>'', 'userDefinedFolderId'=>'', 'kijThreshold'=>'', 'status'=>'', 'defaultGeneralFilterId'=>'', 'alertEmails'=>'', 'isStreamingProject'=>''))
   {
      $url = '/project/modify';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function find($options=array('name'=>'', 'creator'=>'', 'createdMin'=>'', 'createdMax'=>'', 'modifiedMin'=>'', 'modifiedMax'=>''))
   {
      $url = '/project/find';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($projectId, $options=array())
   {
      $url = '/project/remove';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function archive($projectId, $options=array())
   {
      $url = '/project/archive';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function unarchive($projectId, $options=array())
   {
      $url = '/project/unarchive';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function state($options=array('projectId'=>''))
   {
      $url = '/project/state';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function rawReprocess($options=array('projectId'=>''))
   {
      $url = '/project/rawReprocess';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function filterMultipleProjectsIntoOne($fromProjectIds, $fromInstanceName, $toProjectId, $toInstanceName, $filterDescription, $options=array())
   {
      $url = '/project/filterMultipleProjectsIntoOne';
      
      $post = array(
			'fromProjectIds'=>$fromProjectIds,
			'fromInstanceName'=>$fromInstanceName,
			'toProjectId'=>$toProjectId,
			'toInstanceName'=>$toInstanceName,
			'filterDescription'=>$filterDescription,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiProjectFolder
{        
   
   public static function getList($options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/projectFolder/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($folderName, $order, $options=array())
   {
      $url = '/projectFolder/create';
      
      $post = array(
			'folderName'=>$folderName,
			'order'=>$order,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function rename($folderId, $folderName, $order, $options=array())
   {
      $url = '/projectFolder/rename';
      
      $post = array(
			'folderId'=>$folderId,
			'folderName'=>$folderName,
			'order'=>$order,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($folderId, $options=array())
   {
      $url = '/projectFolder/delete';
      
      $post = array(
			'folderId'=>$folderId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiPublisher
{        
   
   public static function getRatedStats($projectId, $options=array())
   {
      $url = '/publisher/getRatedStats';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('withMetadata'=>'', 'options'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/publisher/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $options=array('publisherId'=>'', 'publisherIds'=>''))
   {
      $url = '/publisher/get';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getNb($projectId, $options=array())
   {
      $url = '/publisher/getNb';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function saveMetadata($projectId, $publisherId, $fieldId, $value, $options=array('launchFilterCompilation'=>''))
   {
      $url = '/publisher/saveMetadata';
      
      $post = array(
			'projectId'=>$projectId,
			'publisherId'=>$publisherId,
			'fieldId'=>$fieldId,
			'value'=>$value,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function saveMetadatas($projectId, $jsonArray, $options=array('options'=>''))
   {
      $url = '/publisher/saveMetadatas';
      
      $post = array(
			'projectId'=>$projectId,
			'jsonArray'=>$jsonArray,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function deleteMetadata($projectId, $publisherId, $fieldId, $options=array())
   {
      $url = '/publisher/deleteMetadata';
      
      $post = array(
			'projectId'=>$projectId,
			'publisherId'=>$publisherId,
			'fieldId'=>$fieldId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getMetadata($projectId, $options=array('publisherId'=>''))
   {
      $url = '/publisher/getMetadata';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiReportConfig
{        
   
   public static function set($projectId, $type, $config, $options=array('name'=>''))
   {
      $url = '/reportConfig/set';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'config'=>$config,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $name, $type, $options=array())
   {
      $url = '/reportConfig/get';
      
      $post = array(
			'projectId'=>$projectId,
			'name'=>$name,
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('name'=>'', 'type'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/reportConfig/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($name, $options=array())
   {
      $url = '/reportConfig/remove';
      
      $post = array(
			'name'=>$name,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getSchema($options=array('format'=>''))
   {
      $url = '/reportConfig/getSchema';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/reportConfig/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiReports
{        
   
   public static function update($projectId, $filePath, $content, $options=array())
   {
      $url = '/reports/update';
      
      $post = array(
			'projectId'=>$projectId,
			'filePath'=>$filePath,
			'content'=>$content,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getReportNames($projectId, $type, $options=array())
   {
      $url = '/reports/getReportNames';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $type, $format, $options=array('accessibility'=>'', 'options'=>'', 'reportName'=>'', 'filterDescription'=>''))
   {
      $url = '/reports/get';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'format'=>$format,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getTypes($options=array())
   {
      $url = '/reports/getTypes';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function generate($type, $projectId, $options=array('limit'=>'', 'options'=>'', 'filterDescription'=>'', 'reportName'=>'', 'force'=>''))
   {
      $url = '/reports/generate';
      
      $post = array(
			'type'=>$type,
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiSdaBag
{        
   
   public static function create($title, $options=array('polarity'=>'', 'weight'=>'', 'force'=>''))
   {
      $url = '/sdaBag/create';
      
      $post = array(
			'title'=>$title,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($bagId, $options=array('title'=>'', 'polarity'=>'', 'weight'=>'', 'force'=>''))
   {
      $url = '/sdaBag/modify';
      
      $post = array(
			'bagId'=>$bagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($bagId, $options=array())
   {
      $url = '/sdaBag/get';
      
      $post = array(
			'bagId'=>$bagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('title'=>'', 'polarity'=>'', 'isReadonly'=>'', 'weight'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/sdaBag/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($bagId, $options=array())
   {
      $url = '/sdaBag/remove';
      
      $post = array(
			'bagId'=>$bagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/sdaBag/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiSdaLink
{        
   
   public static function createAllLinks($bagId, $options=array('weight'=>''))
   {
      $url = '/sdaLink/createAllLinks';
      
      $post = array(
			'bagId'=>$bagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($fromBagId, $toBagId, $options=array('weight'=>''))
   {
      $url = '/sdaLink/create';
      
      $post = array(
			'fromBagId'=>$fromBagId,
			'toBagId'=>$toBagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($options=array('fromBagId'=>'', 'toBagId'=>'', 'weight'=>''))
   {
      $url = '/sdaLink/modify';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($options=array())
   {
      $url = '/sdaLink/get';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('fromBagId'=>'', 'toBagId'=>'', 'weight'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/sdaLink/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($options=array())
   {
      $url = '/sdaLink/remove';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/sdaLink/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiSdaWord
{        
   
   public static function create($bagId, $word, $wordId, $options=array('childId'=>'', 'isFirstWord'=>'', 'wordLabel'=>''))
   {
      $url = '/sdaWord/create';
      
      $post = array(
			'bagId'=>$bagId,
			'word'=>$word,
			'wordId'=>$wordId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($id, $options=array('bagId'=>'', 'word'=>'', 'wordId'=>'', 'childId'=>'', 'isFirstWord'=>'', 'wordLabel'=>''))
   {
      $url = '/sdaWord/modify';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($id, $options=array())
   {
      $url = '/sdaWord/get';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('bagId'=>'', 'word'=>'', 'wordId'=>'', 'weight'=>'', 'childId'=>'', 'isReadonly'=>'', 'isFirstWord'=>'', 'wordLabel'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/sdaWord/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($id, $options=array())
   {
      $url = '/sdaWord/remove';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function resetBag($bagId, $options=array())
   {
      $url = '/sdaWord/resetBag';
      
      $post = array(
			'bagId'=>$bagId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/sdaWord/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiSearch
{        
   
   public static function create($projectId, $type, $name, $options=array('accountId'=>'', 'exactPhrase'=>'', 'ands'=>'', 'ors'=>'', 'nots'=>'', 'latitude'=>'', 'longitude'=>'', 'city'=>'', 'radius'=>'', 'url'=>'', 'metric'=>'', 'tag'=>'', 'rule'=>'', 'slices'=>'', 'siteType'=>'', 'siteCategory'=>'', 'site'=>'', 'polygons'=>'', 'hasVideo'=>'', 'seeds'=>'', 'dos'=>''))
   {
      $url = '/search/create';
      
      $post = array(
			'projectId'=>$projectId,
			'type'=>$type,
			'name'=>$name,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($projectId, $searchId, $options=array('name'=>'', 'accountId'=>'', 'exactPhrase'=>'', 'ands'=>'', 'ors'=>'', 'nots'=>'', 'latitude'=>'', 'longitude'=>'', 'city'=>'', 'url'=>'', 'radius'=>'', 'tag'=>'', 'rule'=>'', 'metric'=>'', 'slices'=>'', 'siteType'=>'', 'siteCategory'=>'', 'site'=>'', 'polygons'=>'', 'hasVideo'=>'', 'seeds'=>'', 'dos'=>''))
   {
      $url = '/search/modify';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($projectId, $searchId, $options=array())
   {
      $url = '/search/get';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($projectId, $options=array('sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/search/getList';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function delete($projectId, $searchId, $options=array())
   {
      $url = '/search/delete';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function duplicate($projectId, $searchId, $options=array())
   {
      $url = '/search/duplicate';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function preview($projectId, $searchId, $options=array())
   {
      $url = '/search/preview';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function test($type, $options=array('accountId'=>'', 'exactPhrase'=>'', 'ands'=>'', 'ors'=>'', 'nots'=>'', 'language'=>'', 'latitude'=>'', 'longitude'=>'', 'city'=>'', 'radius'=>'', 'url'=>'', 'metric'=>'', 'tag'=>'', 'rule'=>'', 'slices'=>'', 'langTag'=>'', 'siteType'=>'', 'siteCategory'=>'', 'site'=>'', 'hasVideo'=>'', 'polygons'=>'', 'startFrom'=>''))
   {
      $url = '/search/test';
      
      $post = array(
			'type'=>$type,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function capture($projectId, $searchId, $options=array('isHistorical'=>'', 'startDate'=>'', 'endDate'=>'', 'limit'=>''))
   {
      $url = '/search/capture';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getDownload($projectId, $searchId, $options=array())
   {
      $url = '/search/getDownload';
      
      $post = array(
			'projectId'=>$projectId,
			'searchId'=>$searchId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getSuggestedInterval($options=array('projectId'=>'', 'showValues'=>''))
   {
      $url = '/search/getSuggestedInterval';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiStream
{        
   
         
}
      
class NxApiToad
{        
   
   public static function getStatus($projectId, $options=array())
   {
      $url = '/toad/getStatus';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getPublisherSeedsFromActorByQuery($projectId, $options=array())
   {
      $url = '/toad/getPublisherSeedsFromActorByQuery';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/toad/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiTranslation
{        
   
   public static function translateText($service, $toLang, $textArray, $options=array('fromLang'=>''))
   {
      $url = '/translation/translateText';
      
      $post = array(
			'service'=>$service,
			'toLang'=>$toLang,
			'textArray'=>$textArray,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function translateData($service, $toLang, $projectId, $dataIds, $options=array('fromLang'=>''))
   {
      $url = '/translation/translateData';
      
      $post = array(
			'service'=>$service,
			'toLang'=>$toLang,
			'projectId'=>$projectId,
			'dataIds'=>$dataIds,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getAvailableLangs($options=array())
   {
      $url = '/translation/getAvailableLangs';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/translation/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiTransmit
{        
   
   public static function data($destination, $calls, $options=array())
   {
      $url = '/transmit/data';
      
      $post = array(
			'destination'=>$destination,
			'calls'=>$calls,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/transmit/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiTwitter
{        
   
   public static function callApi($method, $url, $params, $options=array())
   {
      $url = '/twitter/callApi';
      
      $post = array(
			'method'=>$method,
			'url'=>$url,
			'params'=>$params,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/twitter/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiUnitTest
{        
   
   public static function resetTestInstance($options=array())
   {
      $url = '/unitTest/resetTestInstance';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/unitTest/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiUpload
{        
   
   public static function getToken($options=array())
   {
      $url = '/upload/getToken';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function progress($token, $options=array())
   {
      $url = '/upload/progress';
      
      $post = array(
			'token'=>$token,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      
class NxApiUsers
{        
   
   public static function login($email, $password, $domainId, $options=array())
   {
      $url = '/users/login';
      
      $post = array(
			'email'=>$email,
			'password'=>$password,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function create($email, $role, $options=array('socialMediaPlatform'=>'', 'password'=>'', 'lang'=>'', 'userName'=>'', 'company'=>'', 'phone'=>'', 'domainId'=>''))
   {
      $url = '/users/create';
      
      $post = array(
			'email'=>$email,
			'role'=>$role,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function remove($userId, $options=array())
   {
      $url = '/users/remove';
      
      $post = array(
			'userId'=>$userId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function createApiKey($userId, $domainId, $options=array('status'=>''))
   {
      $url = '/users/createApiKey';
      
      $post = array(
			'userId'=>$userId,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modifyApiKey($userId, $domainId, $status, $options=array())
   {
      $url = '/users/modifyApiKey';
      
      $post = array(
			'userId'=>$userId,
			'domainId'=>$domainId,
			'status'=>$status,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function removeApiKey($userId, $domainId, $options=array())
   {
      $url = '/users/removeApiKey';
      
      $post = array(
			'userId'=>$userId,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getApiKey($userId, $domainId, $options=array())
   {
      $url = '/users/getApiKey';
      
      $post = array(
			'userId'=>$userId,
			'domainId'=>$domainId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function get($options=array('id'=>'', 'email'=>''))
   {
      $url = '/users/get';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getList($options=array('domainId'=>'', 'all'=>'', 'sort'=>'', 'fields'=>'', 'desc'=>'', 'offset'=>'', 'limit'=>'', 'page'=>''))
   {
      $url = '/users/getList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function modify($id, $options=array('email'=>'', 'password'=>'', 'role'=>'', 'lang'=>'', 'userName'=>'', 'company'=>'', 'phone'=>'', 'tos'=>''))
   {
      $url = '/users/modify';
      
      $post = array(
			'id'=>$id,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function addToGroup($userId, $groupId, $options=array())
   {
      $url = '/users/addToGroup';
      
      $post = array(
			'userId'=>$userId,
			'groupId'=>$groupId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function removeFromGroup($userId, $groupId, $options=array())
   {
      $url = '/users/removeFromGroup';
      
      $post = array(
			'userId'=>$userId,
			'groupId'=>$groupId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function addPermission($projectId, $options=array('userId'=>'', 'groupId'=>''))
   {
      $url = '/users/addPermission';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function removePermission($projectId, $options=array('userId'=>'', 'groupId'=>''))
   {
      $url = '/users/removePermission';
      
      $post = array(
			'projectId'=>$projectId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function createGroup($groupName, $options=array())
   {
      $url = '/users/createGroup';
      
      $post = array(
			'groupName'=>$groupName,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function deleteGroup($groupId, $options=array())
   {
      $url = '/users/deleteGroup';
      
      $post = array(
			'groupId'=>$groupId,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getPermissionList($options=array())
   {
      $url = '/users/getPermissionList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getGroupList($options=array())
   {
      $url = '/users/getGroupList';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getUsersGroups($options=array())
   {
      $url = '/users/getUsersGroups';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function renameGroup($groupId, $groupName, $options=array())
   {
      $url = '/users/renameGroup';
      
      $post = array(
			'groupId'=>$groupId,
			'groupName'=>$groupName,
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
   public static function getForm($options=array('id'=>'', 'idPrefix'=>'', 'hidden'=>'', 'exclude'=>'', 'autocomplete'=>''))
   {
      $url = '/users/getForm';
      
      $post = array(
		);
      if(@$options && is_array($options))
      $post = array_merge($post,$options);
      return NxApi::call($url,$post);
   }          
              
         
}
      

