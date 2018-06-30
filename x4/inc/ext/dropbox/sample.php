<?php
/** 
 * DropPHP sample
 *
 * http://fabi.me/en/php-projects/dropphp-dropbox-api-client/
 *
 * @author     Fabian Schlieper <fabian@fabi.me>
 * @copyright  Fabian Schlieper 2012
 * @version    1.1
 * @license    See license.txt
 *
 */
 
 // you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
set_time_limit(0);
require_once("DropboxClient.php");
 
 class dropBoxLayer 

 {
     public  $dropbox;
     public  $syncFolder;
     public  $lastDelta;
     public  $filesSynchronized;
     private $dropBoxFolder;
     public  $defaultFileRights=755;
     
        public function __construct($appKey,$appSecret)        
        {
                $this->dropbox = new DropboxClient(array(
                    'app_key' => $appKey, 
                    'app_secret' => $appSecret,
                    'app_full_access' => true,
                    ),'en');
                    
                    
        
        }   
        
        public function setSyncFolders($syncFolderDropBox,$syncFolder)
        {                    
            $this->dropBoxFolder=$syncFolderDropBox;
            
            
            if(file_exists($syncFolder)&&is_dir($syncFolder))
            {
                $this->syncFolder=$syncFolder;    
            }
            
        }
        
        
        public function getAccess()
        {
            $accessToken = $this->loadToken("access");
            if(!empty($accessToken)) 
            {
                $this->dropbox->SetAccessToken($accessToken);                    
            }
            
            if($this->dropbox->IsAuthorized())
            {
                return true;
            }                    
            
        }
        
        //$_GET['oauth_token']
        public function recieveToken($oauthToken)
        {
            $requestToken = load_token($oauthToken);
            if(empty($requestToken)) die('Request token not found!'); 
            
            $accessToken = $this->dropbox->GetAccessToken($requestToken);    
            $this->storeToken($accessToken, "access");
            $this->deleteToken($_GET['oauth_token']);                
        }
        
        

    
        public function getNewTokenUrl()
        {
            
            $returnUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
            $authUrl = $this->dropbox->BuildAuthorizeUrl($returnUrl);
            $requestToken = $this->dropbox->GetRequestToken();
            $this->storeToken($requestToken, $requestToken['t']);
            return $authUrl;
            
        }
        
        public function getDeltaSync($delta)
        {
            $delta=$this->dropbox->Delta($delta);               
            $x=time();
            $this->getFiles($delta->entries);    
            $y=time();
            echo ($x-$y);
            
            
        }    
        
        
        public function getLatestDelta()
        {
            return  $this->lastDelta=$this->dropbox->DeltaLate();               
        }
        
        
        
        public function getFullSync()    
        {     
             
           if($files=$this->dropbox->GetFiles($this->dropBoxFolder,true))
           {
                $this->getFiles($files);    
           }
         
        }
        
        private function tokenize($path)
        {
            
            $v=explode('/',$path);unset($v[0]);unset($v[1]);            
            return implode('/',$v);
        }
        
        public function getFiles($files)
        {

           if(isset($files)&&is_array($files))
           {     
                foreach($files as $file)
                {
                    if($file->is_dir)
                    {                    
                        $filePath=$this->tokenize($file->path);
                        if(!file_exists($this->syncFolder.$filePath))
                        {
                            mkdir($this->syncFolder.$filePath,$this->defaultFileRights,true);
                        }
                    }
                }
                        
                foreach($files as $file)
                {                            
                    if(!$file->is_dir)
                    {                    
                        $this->dropbox->DownloadFile($file,$this->syncFolder.$this->tokenize($file->path));
                        $this->filesSynchronized[]=$file;                    
                    }
                }
           }
        
        
        }
        
        
        private function storeToken($token, $name)
        {
            if(!file_put_contents("tokens/$name.token", serialize($token)))
            die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
        }

       private  function loadToken($name)
        {
            if(!file_exists("tokens/$name.token")) return null;
            return @unserialize(@file_get_contents("tokens/$name.token"));
        }

       private  function deleteToken($name)
        {
            @unlink("tokens/$name.token");
        }

 }
 
 

 
$dpl=new dropBoxLayer('rou9p2n72qvscii','xs2qnblthbzlurh');

    if($dpl->getAccess())
    {
        $dpl->setSyncFolders('x4',$_SERVER['DOCUMENT_ROOT'].'/media/box/');     
        
        $dpl->getFullSync();
        
    }






                
 












/*
echo "<pre>";
echo "<b>Account:</b>\r\n";
print_r($dropbox->GetAccountInfo());
       
//$files = $dropbox->GetFiles("",false);

  



echo "\r\n\r\n<b>Files:</b>\r\n";
print_r(array_keys($files));

 die();
if(!empty($files)) {
    $file = reset($files);
    $test_file = "test_download_".basename($file->path);
    
    echo "\r\n\r\n<b>DELTA:</b>\r\n";
        
   //     $dlt=$dropbox->Delta(false);
     //       debugbreak();
        //$dlt2=$dropbox->Delta('AAEl0Z2FJrtGNTeI-Fqr70h3Xw8i4_H_FP_9XgapWco3i-xSIQupuAr3wHIpYMIG_WA3PZjWmMQbhWYis8Hf1YUfdxsR-B5rFE2k6YbUJ3pExS9g8diCXJstrdhMGhIS38sT9mb1CYOtUw_Qfx3B6n1U');
          //   echo $dlt2->cursor;

        
    echo "\r\n\r\n<b>Meta data of <a href='".$dropbox->GetLink($file)."'>$file->path</a>:</b>\r\n";
    print_r($dropbox->GetMetadata($file->path));
    
    echo "\r\n\r\n<b>Downloading $file->path:</b>\r\n";
    
    
  print_r($dropbox->DownloadFile($file, $test_file));
        
    
        
    echo "\r\n\r\n<b>Uploading $test_file:</b>\r\n";
    print_r($dropbox->UploadFile($test_file));
    echo "\r\n done!";    
    
    echo "\r\n\r\n<b>Revisions of $test_file:</b>\r\n";    
    print_r($dropbox->GetRevisions($test_file));
}
    
echo "\r\n\r\n<b>Searching for JPG files:</b>\r\n";    
$jpg_files = $dropbox->Search("/", ".jpg", 5);
if(empty($jpg_files))
    echo "Nothing found.";
else {
    print_r($jpg_files);
    $jpg_file = reset($jpg_files);

    echo "\r\n\r\n<b>Thumbnail of $jpg_file->path:</b>\r\n";    
    $img_data = base64_encode($dropbox->GetThumbnail($jpg_file->path));
    echo "<img src=\"data:image/jpeg;base64,$img_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" />";
}


function store_token($token, $name)
{
    if(!file_put_contents("tokens/$name.token", serialize($token)))
        die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}

function load_token($name)
{
    if(!file_exists("tokens/$name.token")) return null;
    return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
    @unlink("tokens/$name.token");
}





*/