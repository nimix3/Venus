<?php
// File Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\core;
class File
{
	protected $LastError;
	
	public function WriteSafe($fileName, $dataToSave, $bin=false)
	{    
		try{
			if(boolval($bin))
				$fp = fopen($fileName, 'wb');
			else
				$fp = fopen($fileName, 'w');
			if ($fp !== false)
			{
				$startTime = microtime();
				do
				{ 
					$canWrite = flock($fp, LOCK_EX);
					if(!$canWrite) usleep(round(rand(0, 100)*1000));
				} 
				while ((!$canWrite)and((microtime()-$startTime) < 1000));
				if ($canWrite)
				{            
					fwrite($fp, $dataToSave);
					flock($fp, LOCK_UN);
				}
				fclose($fp);
				return true;
			}
			else
				return false;
		}
		catch(Exception $ex)
		{
			$this->LastError[] = $ex->getMessage();
			return null;
		}
	}
	
	public function ReadSafe($filename,$bin=true)
	{
		if(file_exists($filename))
		{
			try{
				if(boolval($bin))
					$handle = fopen($filename, "rb");
				else
					$handle = fopen($filename, "r");
				$contents = null;
				if($handle !== false)
				{
					$startTime = microtime();
					do
					{ 
						$canRead = flock($handle, LOCK_SH);
						if(!$canRead) usleep(round(rand(0, 100)*1000));
					} 
					while ((!$canRead)and((microtime()-$startTime) < 1000));
					$contents = fread($handle, filesize($filename));
					flock($handle, LOCK_UN);
					fclose($handle);	
				}
				return $contents;
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return null;
			}
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetCreation($filename)
	{
		if(file_exists($filename))
		{
			return filectime($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetLastAccess($filename)
	{
		if(file_exists($filename))
		{
			return fileatime($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetLastModify($filename)
	{
		if(file_exists($filename))
		{
			return filemtime($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFileSize($filename)
	{
		if(file_exists($filename))
		{
			return filesize($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFilePermission($filename)
	{
		if(file_exists($filename))
		{
			return fileperms($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFileExtention($filename)
	{
		if(file_exists($filename))
		{
			$info = pathinfo($filename);
			return $info['extension'];
			
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFileDirectory($filename)
	{
		if(file_exists($filename))
		{
			$info = pathinfo($filename);
			return $info['dirname'];
			
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFileFullname($filename)
	{
		if(file_exists($filename))
		{
			$info = pathinfo($filename);
			return $info['basename'];
			
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFileName($filename)
	{
		if(file_exists($filename))
		{
			$info = pathinfo($filename);
			return $info['filename'];
			
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	function GetExeFileVersion($filename)
	{
		if(file_exists($filename))
		{
			try{
				$fpFile = fopen($filename, "rb");
				$strFileContent = fread($fpFile, filesize($filename));
				fclose($fpFile);
				$strTagBefore = 'F\0i\0l\0e\0V\0e\0r\0s\0i\0o\0n\0\0\0\0\0';
				$strTagAfter = '\0\0';
				if (preg_match("/$strTagBefore(.*?)$strTagAfter/", $strFileContent,$arrMatches))
				{
					if(isset($arrMatches[1]) and !empty($arrMatches[1]))
					{
						return str_replace("\0", "", $arrMatches[1]);
					}
					else
					{
						return null;
					}
				}
			}
			catch(Exception $ex)
			{
				$this->LastError[] = $ex->getMessage();
				return null;
			}
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function GetFullPath($path)
	{
		return realpath($path);
	}
	
	public function GetFileNameWithFormat($file)
	{
		return basename($file);
	}
	
	public function CreatePath($path,$mode=0777,$rec=true)
	{
		return mkdir($path,$mode,boolval($rec));
	}
	
	public function CreateFolder($path,$mode=0777,$rec=true)
	{
		$this->CreatePath($path,$mode,$rec);
	}
	
	public static function RemovePath($dir)
	{
		$files = array_diff(scandir($dir), array('.','..')); 
		foreach ($files as $file) {
			(is_dir("$dir/$file") && !is_link($dir)) ? $this->RemovePath("$dir/$file") : unlink("$dir/$file"); 
		}
			return rmdir($dir); 
	}
	
	public function Write($filename,$data,$append=false,$lock=false)
	{
		if(file_exists($filename))
		{
			if($append and $lock)
			{
				return boolval(file_put_contents($filename,$data,FILE_APPEND | LOCK_EX));
			}
			else if($append)
			{
				return boolval(file_put_contents($filename,$data,FILE_APPEND));
			}
			else if($lock)
			{
				return boolval(file_put_contents($filename,$data,LOCK_EX));
			}
			else
			{
				return boolval(file_put_contents($filename,$data));
			}
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function Read($filename,$offset=0,$len='')
	{
		if(file_exists($filename))
		{
			if(isset($offset,$len) and !empty($offset) and !empty($len))
				return file_get_contents($filename,false,null,$offset,$len);
			else
				return file_get_contents($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}

	public function Remove($filename)
	{
		if(file_exists($filename))
		{
			return unlink($filename);
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function RemoveFolder($dir)
	{
		if (! is_dir($dir)) {
			return false;
		}
		array_map('unlink', glob("$dir/*.*"));
		rmdir($dir);
		return true;
	}
	
	public function RemoveFolderTree($dirPath)
	{
		if (! is_dir($dirPath)) {
			return;
		}
		if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
			$dirPath .= '/';
		}
		$files = glob($dirPath . '*', GLOB_MARK);
		foreach ($files as $file) {
			if (is_dir($file)) {
				$this->RemoveFolder($file);
			} else {
				unlink($file);
			}
		}
		rmdir($dirPath);
	}
	
	public function GetMime($filename)
	{
		if(file_exists($filename))
		{
			if (function_exists('finfo_file')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$type = finfo_file($finfo, $filename);
				finfo_close($finfo);
				return $type;
			}
			else if (function_exists('mime_content_type'))
			{
				$type = mime_content_type($filename);
				return $type;
			}
			else if (function_exists('exif_imagetype'))
			{
				$type = exif_imagetype($filename);
				if($type !== false)
				{
					return image_type_to_mime_type($type);
				}
				else 
				{
					return 'application/octet-stream';
				}
			}
			else
			{
				return 'application/octet-stream';
			}
		}
		else
		{
			$this->LastError[] = "specified file not exist";
			return null;
		}
	}
	
	public function getLastError()
	{
		return $this->LastError;
	}
}

class Zip {
	protected $lib;			
	protected $org_files;      
	protected $new_file_path;
	protected $extr_file;	
	protected $extr_dirc;	
	
	public function __construct(){
		$this->lib = 0;
		$this->extr_file = 0;
		$this->new_file_path = 0;
		$this->org_files = array();
	}
	
	public function zip_start($file_path) {
		$this->new_file_path = $file_path;
		if(class_exists("ZipArchive")) $this->lib = 1;
		else $this->lib = 2;
		return true;
	}

	public function zip_add($in){
		if($this->lib === 0 || $this->new_file_path === 0) throw new Exception("PHP-ZIP: must call zip_start before zip_add");
		if(is_string($in)){
			if(file_exists($in)) {
				if(!is_dir($in)) array_push($this->org_files,$in);
				else $this->push_whole_dir($in);
			}
		}
		else foreach($in as $value){
			$this->zip_add($value);
		}
		return true;
	}

	public function zip_end($force_lib = false) {
		if($force_lib === 2) {
			$this->lib = 2;
		}
		elseif ($force_lib === 1) {
			$this->lib = 1;
		}
		if($this->lib === 0 || $this->new_file_path === 0) throw new Exception('PHP-ZIP: zip_start and zip_add haven\'t been called yet');
		if($this->lib === 1) {
			$names = $this->commonPath($this->org_files, true);
			$lib = new ZipArchive();
			if(!$lib->open($this->new_file_path,ZIPARCHIVE::CREATE)) throw new Exception('PHP-ZIP: Permission Denied or zlib can\'t be found');
			
			$count_before = $lib->numFiles;
			foreach ($this->org_files as $index => $org_file_path) {
				$lib->addFile($org_file_path,$names[$index]);
			}
			$count_after = $lib->numFiles;
			$lib->close();
		}
		if($this->lib === 2) {
			if(file_exists("./library/vendor/PCLZip/pclzip.lib.php"))
				require_once "./library/vendor/PCLZip/pclzip.lib.php";
			else
				return false;
			$common = $this->commonPath($this->org_files, false);
			if(!$lib = new PclZip($this->new_file_path)) throw new Exception('PHP-ZIP: Permission Denied or zlib can\'t be found');
			$count_before = count($lib->listContent());
			$lib->add($this->org_files, PCLZIP_OPT_REMOVE_PATH, $common[0]);
			$count_after = count($lib->listContent());
		}
		if(!file_exists($this->new_file_path)) throw new Exception('PHP-ZIP: After doing the zipping file can not be found');
		if(filesize($this->new_file_path) === 0) throw new Exception('PHP-ZIP: After doing the zipping file size is still 0 bytes');
		$this->org_files = array();
		return true;
	}

	public function zip_files($files,$to) {
		
		$this->zip_start($to);
		$this->zip_add($files);
		return $this->zip_end();
		
	}
	
	public function unzip_file($file_path,$target_dir=NULL) {
		if(!file_exists($file_path)) throw new Exception("PHP-ZIP: File doesn't Exist");
		$_FILEINFO = finfo_open(FILEINFO_MIME_TYPE);
		$file_mime_type = finfo_file($_FILEINFO, $file_path);
		if(!array_search($file_mime_type,array(
			'application/x-zip',
			'application/zip',
			'application/x-zip-compressed',
			'application/s-compressed',
			'multipart/x-zip')
		)) throw new Exception("PHP-ZIP: File type is not ZIP");
		$this->extr_file = $file_path;
		if(class_exists("ZipArchive")) $this->lib = 1;
		else $this->lib = 2;
		if($target_dir !== NULL) return $this->unzip_to($target_dir);
		else return true;
	}
	
	public function unzip_to($target_dir) {
		if($this->lib === 0 && $this->extr_file === 0) throw new Exception("PHP-ZIP: unzip_file hasn't been called");
		if(file_exists($target_dir) && (!is_dir($target_dir))) throw new Exception("PHP-ZIP: Target directory exists as a file not a directory");
		if(!file_exists($target_dir)) if(!mkdir($target_dir)) throw new Exception("PHP-ZIP: Directory not found, and unable to create it");
		$this->extr_dirc = $target_dir;
		if($this->lib === 1) {
			$lib = new ZipArchive;
			if(!$lib->open($this->extr_file)) throw new Exception("PHP-ZIP: Unable to open the zip file");
			if(!$lib->extractTo($this->extr_dirc)) throw new Exception("PHP-ZIP: Unable to extract files");
			$lib->close();
		} 
		if($this->lib === 2) {
			if(file_exists("./library/vendor/PCLZip/pclzip.lib.php"))
				require_once "./library/vendor/PCLZip/pclzip.lib.php";
			else
				return false;
			$lib = new PclZip($this->extr_file);
			if(!$lib->extract(PCLZIP_OPT_PATH,$this->extr_dirc)) throw new Exception("PHP-ZIP: Unable to extract files");
		}
		return true;
	}

	private function dir_to_assoc_arr(DirectoryIterator $dir) {
		$data = array();
		foreach ($dir as $node) {
			if ( $node->isDir() && !$node->isDot() ) {
				$data[$node->getFilename()] = $this->dir_to_assoc_arr(new DirectoryIterator($node->getPathname()));
			} else if( $node->isFile() ) {
				$data[] = $node->getFilename();
			}
		}
		return $data;
	}

	private function push_whole_dir($dir){
		$dir_array = $this->dir_to_assoc_arr(new DirectoryIterator($dir));
		foreach($dir_array as $key => $value) {
			if(!is_array($value)) array_push($this->org_files,$this->path($dir,$value));
			else {
				$this->push_whole_dir($this->path($dir,$key));
			}
		}
	}

	private function path() {
		return join(DIRECTORY_SEPARATOR, func_get_args());
	}

	private function commonPath($files, $remove = true) {
		foreach($files as $index => $filesStr) {
			$files[$index] = explode(DIRECTORY_SEPARATOR, $filesStr);
		}
		$toDiff = $files;
		foreach($toDiff as $arr_i => $arr) {
			foreach($arr as $name_i => $name) {
				$toDiff[$arr_i][$name_i] = $name . "___" . $name_i;
			}
		}
		$diff = call_user_func_array("array_diff",$toDiff);
		reset($diff);
		$i = key($diff) - 1;
		if($remove) {
			foreach($files as $index => $arr) {
				$files[$index] = implode(DIRECTORY_SEPARATOR,array_slice($files[$index], $i));
			}
		}
		else {
			foreach($files as $index => $arr) {
				$files[$index] = implode(DIRECTORY_SEPARATOR,array_slice($files[$index], 0, $i));
			}
		}
		return $files;
	}
}