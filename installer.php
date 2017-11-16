<?php
	/* SinTaskFW Installer
	 * (c) 2017 Aditya Wikardiyan & CV.SinTask
	 * ---------------------------------------
	 */

	/* Checker ---
	 */
	session_start();
	session_destroy();
	error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
	 
	$__CHECKER__ 		= 0;
	$__CHECKER_MSG__	= [];

	if (version_compare(phpversion(), '5.5.5', '<')) {
    	$__CHECKER_MSG__[0] = "PHP anda : ".PHP_VERSION;
    	$__CHECKER_MSG__[0] .= "<br>";
    	$__CHECKER_MSG__[0] .= "Versi PHP anda harus lebih atau sama dengan 5.5.5";
    	$__CHECKER__ = $__CHECKER__+1;
	}

	if (!defined('PDO::ATTR_DRIVER_NAME')) {
	    $__CHECKER_MSG__[1] = "Ekstensi PDO tidak tersedia";
	    $__CHECKER_MSG__[1] .= "<br>";
	    $__CHECKER_MSG__[1] .= "PHP anda harus mengaktifkan Ekstensi PDO";
	    $__CHECKER__ = $__CHECKER__+1;
	}

	if(!extension_loaded('openssl')) {
	    $__CHECKER_MSG__[2] = "Ekstensi OpenSSL tidak tersedia";
	    $__CHECKER_MSG__[2] .= "<br>";
	    $__CHECKER_MSG__[2] .= "PHP anda harus mengaktifkan Ekstensi OpenSSL";
	    $__CHECKER__ = $__CHECKER__+1;
	}

	if(OPENSSL_VERSION_NUMBER < 0x009080bf) {
	    $__CHECKER_MSG__[3] = "Versi Ekstensi OpenSSL kurang dari 0.9.8k";
	    $__CHECKER_MSG__[3] .= "<br>";
	    $__CHECKER_MSG__[3] .= "Anda harus mengupdate OpenSSL";
	    $__CHECKER__ = $__CHECKER__+1;
	}

	if($__CHECKER__ > 0) {
		echo "<strong>Persyaratan SinTaskFW</strong>";
		echo "<hr/>";
		$cc = count($__CHECKER_MSG__);
		for($i = 0; $i < $cc; $i++) {
			$printNow = $__CHECKER_MSG__[$i];
			if($printNow != null || $printNow != "") {
				echo $printNow;
				echo "<hr/>";
			}
		}
		die();
	}

	/* Function ---
	 */

	function rrmdir($dir) {
        if (is_dir($dir)) {
            $objects = scandir($dir); 
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir."/".$object)) {
                        rrmdir($dir."/".$object);
                    } else {
                        unlink($dir."/".$object);
                    }
                } 
            }
            rmdir($dir); 
        } 
    }

    /**
	 * Copy a file, or recursively copy a folder and its contents
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.1
	 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @param       int      $permissions New folder creation permissions
	 * @return      bool     Returns true on success, false on failure
	 */
    function rcopy($source, $dest, $permissions = 0755) {
        if (is_link($source)) {
            return symlink(readlink($source), $dest);
        }
        
        if (is_file($source)) {
            return copy($source, $dest);
        }
        
        if (!is_dir($dest)) {
            mkdir($dest, $permissions);
        }
        
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            rcopy("$source/$entry", "$dest/$entry", $permissions);
        }
        
        $dir->close();
        return true;
    }

    /* Function File Size Normalize from B */
	function fileSizeFrByToAll($fileSize, $fileSizeNumFor) {
		/*
			This is manual, for dynamic uses POW or ^ equivalent math function
			then devided by filesize & value approach
		*/
		$fileSizeTwo = $fileSize;
		$fileSizeType = "B";
		if($fileSize>1072668082176) { /* to TB */
			$fileSizeTwo = $fileSize/1099511627776;
			$fileSizeType = "TB";
		} else if($fileSize>1047527424 && $fileSize<=1072668082176) { /* to GB */
			$fileSizeTwo = $fileSize/1073741824;
			$fileSizeType = "GB";
		} else if($fileSize>1022976 && $fileSize<=1047527424) { /* to MB */
			$fileSizeTwo = $fileSize/1048576;
			$fileSizeType = "MB";
		} else if($fileSize>999 && $fileSize<=1022976) { /* to KB */
			$fileSizeTwo = $fileSize/1024;
			$fileSizeType = "KB";
		} else { /* to BYTE */
			$fileSizeTwo = $fileSize;
			$fileSizeType = "B";
		}
		$fileSizeNotFormatedNum = $fileSizeTwo;
		$fileSizeFinal = number_format($fileSizeNotFormatedNum, $fileSizeNumFor)." ".$fileSizeType;
		return $fileSizeFinal;
	}

    /* Running ---
     */

	$__CENTER__		= preg_replace("!${_SERVER['SCRIPT_NAME']}$!", '', $_SERVER['SCRIPT_FILENAME']);
	$__BASE_DIR__	= str_replace("\\", "/", __DIR__);
	$__DOC_ROOT__	= $__BASE_DIR__;
	
	$sintaskGet = $_GET["to"];
	if($sintaskGet == "step1") {
		$local_file     = $__DOC_ROOT__.'/file.zip';
		$download_url1  = 'https://fw.sintask.com/direct/dl/latest';

		$sleepTime  = 1;        // 1 sec
		$usleepTime = 1000000;  // 1 sec

		header('Content-Type: text/octet-stream');
		header('Cache-Control: no-cache');

		function stream_notification_callback($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max) {
		    
		    $sleepTime  = 80000; // 0.008 s

		    switch($notification_code) {
		        case STREAM_NOTIFY_RESOLVE:
		        case STREAM_NOTIFY_AUTH_REQUIRED:
		        case STREAM_NOTIFY_COMPLETED:
		        case STREAM_NOTIFY_FAILURE:
		        case STREAM_NOTIFY_AUTH_RESULT:
		            var_dump($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max);
		            /* Ignore */
		            break;

		        case STREAM_NOTIFY_REDIRECTED:
		            usleep($sleepTime);
		            
		            $messages = "Mengalihkan";
		            
		            $progress = 0;
		            
		            $d = array('message' => $messages , 'progress' => $progress);
		            echo json_encode($d) . PHP_EOL;
		            
		            ob_flush();
		            flush();
		            
		            break;

		        case STREAM_NOTIFY_CONNECT:
		            usleep($sleepTime);
		            
		            $messages = "Terhubung...";
		            
		            $progress = 0;
		            
		            $d = array('message' => $messages , 'progress' => $progress);
		            echo json_encode($d) . PHP_EOL;
		            
		            ob_flush();
		            flush();
		            
		            break;

		        case STREAM_NOTIFY_FILE_SIZE_IS:
		            usleep($sleepTime);
		            
		            $messages = "Mendapatkan ukuran file - ".fileSizeFrByToAll($bytes_max, 2);
		            
		            $progress = 0;
		            
		            $d = array('message' => $messages , 'progress' => $progress);
		            echo json_encode($d) . PHP_EOL;
		            
		            ob_flush();
		            flush();
		            
		            break;

		        case STREAM_NOTIFY_MIME_TYPE_IS:
		            usleep($sleepTime);
		            
		            $messages = "Menemukan tipe file - ".$message;
		            
		            $progress = 0;
		            
		            $d = array('message' => $messages , 'progress' => $progress);
		            echo json_encode($d) . PHP_EOL;
		            
		            ob_flush();
		            flush();
		            
		            break;

		        case STREAM_NOTIFY_PROGRESS:
		            usleep($sleepTime);
		            
		            $messages = "Mengunduh <u>".fileSizeFrByToAll($bytes_transferred, 2)."</u> dari <u>".fileSizeFrByToAll($bytes_max, 2)."</u>";
		            
		            $progress1  = $bytes_transferred/$bytes_max;
		            $progress2  = $progress1*100;
		            $progress   = round($progress2);
		            
		            $d = array('message' => $messages , 'progress' => $progress);
		            echo json_encode($d) . PHP_EOL;
		            
		            ob_flush();
		            flush();
		            
		            break;
		    }
		}

		$ctx = stream_context_create();
		stream_context_set_params($ctx, array("notification" => "stream_notification_callback"));

		$save = file_get_contents($download_url1, false, $ctx);
		file_put_contents($local_file, $save);
	} else if($sintaskGet == "step2") {
		header('Content-Type: text/octet-stream');
	    header('Cache-Control: no-cache');

	    $file   = $__DOC_ROOT__.'/file.zip';

	    $zip    = new ZipArchive;
	    $res    = $zip->open($file);
	    if ($res === TRUE) {
	        $zip->extractTo($__DOC_ROOT__.'/');
	        $zip->close();
	        
	        unlink($file);
	        
	        $d = array('message' => "File telah di ekstrak", 'status' => 200);
	        echo json_encode($d) . PHP_EOL;
	    } else {
	        
	        $d = array('message' => "Gagal mengekstrak file", 'status' => 400);
	        echo json_encode($d) . PHP_EOL;
	    }
	} else if($sintaskGet == "step3") {
		header('Content-Type: text/octet-stream');
	    header('Cache-Control: no-cache');

	    $file   = $__DOC_ROOT__.'/sintaskfw';
	    $dest   = $__DOC_ROOT__;
	    
	    rcopy($file."/", $dest);
	    rrmdir($file);
	    unlink('installer.php');

	    $d = array('message' => "SinTaskFw telah terinstall, silahkan buka web tanpa menggunakan <b>/installer.php</b>", 'status' => 200);
	    echo json_encode($d) . PHP_EOL;
	} else {
		?>
		<html>
			<head>
				<title>SinTaskFW Installer</title>
			</head>
			<body>
				<style>
					body {
						background: #FFF;
					    background-repeat: repeat;
					    background-position: center center;
					    background-attachment: fixed;
					    line-height: 1.4em;
					}
					div.contentArea {
						padding: 20px 200px 20px 200px;
						margin: 0px auto;
						font-size: 16px;
						color: #222;
					}
					div.contentTwo {
						padding: 20px;
					}
					div.borderLine {
						margin-top: 15px;
						padding-top: 15px;
						border-top: 1px solid #DADADA;
					}
					div.borderSpaceMini {
						margin-top: 5px;
						padding-top: 5px;
					}
					div.borderSpaceMiniSuper {
						margin-top: 5px;
					}
					div.borderSpace {
						margin-top: 20px;
						padding-top: 20px;
					}
					span.thisTagging {
						font-size: 13px;
						padding: 1px 5px;
						border: 1px solid #EAEAEA;
						background: #EAEAEA;
						color: #555;
						font-weight: bolder;
						border-radius: 4px;
					}
					.noted {
						padding: 15px;
						border: 1px solid #EA9F2D;
						color: #555;
					}
					.orangeBubble {
						font-size: 13px;
						padding: 1px 5px;
						border: 1px solid #EA9F2D;
						background: #EA9F2D;
						color: #FFF;
						font-weight: bolder;
						border-radius: 2px;
						display: inline-block;
					}
					input[type='submit'] {
						padding: 2px 6px;
						font-size: 13px;
						border: 1px solid #DADADA;
						border-radius: 4px;
						color: #555;
						margin-top: 2px;
						margin-bottom: 2px;
						cursor: pointer;
						transition: all 0.2s linear 0s;
					}
					input[type='submit']:hover {
						box-shadow: 0px 0px 4px #111;
					}
					button {
					    padding: 2px 6px;
						font-size: 13px;
						border: 1px solid #DADADA;
						border-radius: 4px;
						color: #555;
						margin-top: 2px;
						margin-bottom: 2px;
						cursor: pointer;
						transition: all 0.2s linear 0s;
					}
					button:hover {
					    box-shadow: 0px 0px 4px #111;
					}
					button:disabled {
					    box-shadow: 0px 0px 4px #111;
					    opacity: 0.5;
					}
					ul {
						margin-left: 20px;
					}
				</style>

				<div class="contentArea">
				    <b class="fontSize25px">SinTaskFW Installer</b>
				    <div class="borderSpace"></div>
				    Klik tombol Download untuk download & install SinTaskFW terbaru
				    <div class="borderSpaceMini"></div>
				    <button id="checkUpdate" onclick="ajaxStream();">Download</button>
				    <div class="borderLine"></div>
				    <div id="progressor" style="background:#555; width:0%; height:10px;"></div>
				    <div class="borderSpaceMini"></div>
				    <div class="codeNoted" id="divProgress"></div>
				    <div class="borderSpaceMini"></div>
				</div>

				<script>
				    function doClear() {
				        document.getElementById("divProgress").innerHTML = "";
				    }

				    function logMessage(message) {
				        document.getElementById("divProgress").innerHTML += message + '<br />';
				    }
				    
				    function logMessageOne(message) {
				        document.getElementById("divProgress").innerHTML = message + '<br />';
				    }
				    
				    function ajaxFinish() {
				        if (!window.XMLHttpRequest) {
				            logMessage("Browser anda tidak mendukung XMLHttpRequest, silahkan update browser anda");
				            return;
				        }
				        
				        try {
				            var xhr = new XMLHttpRequest();
				            var next = false;
				            
				            xhr.onload = function() {
				                var new_response = JSON.parse( xhr.responseText );
				                var ver     = new_response.version;
				                
				                if(next == true) {
				                    logMessage("Selesai");
				                    
				                    setTimeout(function(){
				                        //NOT
				                    },2000);
				                }
				            };
				            xhr.onerror = function() { 
				                logMessage("Error"); 
				            };
				            xhr.onreadystatechange = function() {
				                try {
				                    if (xhr.readyState == 4) {
				                        var new_response = JSON.parse( xhr.responseText );
				                        var status  = new_response.status;
				                        var message = new_response.message;
				                        
				                        logMessageOne(message);

				                        if(status == 200) {
				                            next = true;
				                        } else {
				                            next = false;
				                        }
				                    }   
				                }
				                catch (e) {
				                    
				                }
				            };

				            var url1 = "?to=step3";

				            xhr.open("GET", url1, true);
				            xhr.send("Making request...");      
				        }
				        catch (e) {
				            logMessage("Error - Exception: " + e);
				        }
				    }
				    
				    function ajaxExtract() {
				        if (!window.XMLHttpRequest) {
				            logMessage("Browser anda tidak mendukung XMLHttpRequest, silahkan update browser anda");
				            return;
				        }
				        
				        try {
				            var xhr = new XMLHttpRequest();
				            var next = false;
				            
				            xhr.onload = function() {
				                var new_response = JSON.parse( xhr.responseText );
				                var ver     = new_response.version;
				                
				                if(next == true) {
				                    logMessage("Memindahkan data file...");
				                    
				                    setTimeout(function(){
				                        ajaxFinish();
				                    },2000);
				                }
				            };
				            xhr.onerror = function() { 
				                logMessage("Error"); 
				            };
				            xhr.onreadystatechange = function() {
				                try {
				                    if (xhr.readyState == 4) {
				                        var new_response = JSON.parse( xhr.responseText );
				                        var status  = new_response.status;
				                        var message = new_response.message;
				                        
				                        logMessageOne(message);

				                        if(status == 200) {
				                            next = true;
				                        } else {
				                            next = false;
				                        }
				                    }   
				                }
				                catch (e) {
				                    
				                }
				            };

				            var url1 = "?to=step2";

				            xhr.open("GET", url1, true);
				            xhr.send("Making request...");      
				        }
				        catch (e) {
				            logMessage("Error - Exception: " + e);
				        }
				    }

				    function ajaxStream() {
				    	document.getElementById("checkUpdate").disabled = true;

				        if (!window.XMLHttpRequest) {
				            logMessage("Browser anda tidak mendukung XMLHttpRequest, silahkan update browser anda");
				            return;
				        }

				        try {
				            var xhr = new XMLHttpRequest();  
				            xhr.previous_text = '';

				            logMessage("Mulai mengunduh SinTaskFW");

				            xhr.onload = function() {
				                document.getElementById('progressor').style.width = "0%";
				                doClear();
				                logMessage("Unduh SinTaskFW berhasil"); 
				                logMessage("Memasang SinTaskFW..."); 
				                
				                setTimeout(function(){
				                    ajaxExtract();
				                },1000);
				            };
				            xhr.onerror = function() { 
				                logMessage("Error"); 
				            };
				            xhr.onreadystatechange = function() {
				                try {
				                    if (xhr.readyState > 2) {
				                        var new_response = xhr.responseText.substring(xhr.previous_text.length);
				                        var result = JSON.parse( new_response );
				                        
				                        logMessageOne(result.message);

				                        document.getElementById('progressor').style.width = result.progress + "%";
				                        xhr.previous_text = xhr.responseText;
				                    }   
				                }
				                catch (e) {
									/* Kosong */
				                }
				            };

				            var url1 = "?to=step1";

				            xhr.open("GET", url1, true);
				            xhr.send("Making request...");      
				        }
				        catch (e) {
				            logMessage("Error - Exception: " + e);
				        }
				    }
				</script>
			</body>
		</html>
		<?php
	}

?>