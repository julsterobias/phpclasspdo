<?php
/*
Author: Juluis Terobias
Version: 1.0
Platform: php
Location: UAE
*/
require_once('config.php');
class phpClasses{
	var $conn;
	var $errors = array();
	
	function __construct(){
		/*$mysqli = new mysqli(JDBHOST,JDBUSERNAME,JDBPASSWORD,JDBNAME);
		if ($mysqli->connect_errno):
		  	echo "Failed to connect to MySQL: " . $mysqli->connect_errno;
		else:
			$this->conn = $mysqli;
		endif;*/
		
		/*
		PDO connection
		*/
		try {
		    $this->conn = new PDO('mysql:host='.JDBHOST.';dbname='.JDBNAME, JDBUSERNAME, JDBPASSWORD);
		} catch (PDOException $e) {
			print "Error!: " . $e->getMessage() . "<br/>";
			die();
		}
	}
	function CloseDB(){
		mysqli_close($this->conn);
	}
	function JInsert($attr){
		$temp = array();
		$querystr = "INSERT INTO ".$attr[0]." SET ";
		foreach($attr[1] as $sqlindex => $sqvalue):
			$temp[] = $sqlindex." = ".$sqvalue;
		endforeach;
		$queryfields = implode(", ",$temp);
		$querystr = $querystr.$queryfields;
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$data = $this->conn->prepare($querystr);
		$data->execute();
		//$this->conn->query($querystr);
		$res = $this->conn->lastInsertId();
		return $res;
	}
	function JUpdate($attr,$con){
		$temp = array();
		$querystr = "UPDATE ".$attr[0]." SET ";
		foreach($attr[1] as $sqlindex => $sqvalue):
			$temp[] = $sqlindex." = ".$sqvalue;
		endforeach;
		$queryfields = implode(", ",$temp);
		$querystr = $querystr.$queryfields." ".$con;
		$res = $this->conn->query($querystr);
		return $res;
	}
	function JGetResult($str){
		$data = NULL;
		if(strlen($str) > 0):
			$data = $this->conn->prepare($str);
			$data->execute();
			$data = $data->fetchAll();
		endif;
		return $data;
	}
	function JGetRow($str){
		$data = NULL;
		if(strlen($str) > 0):
			$data = $this->conn->prepare($str);
			$data->execute();
			$data = $data->fetch();
		endif;
		return $data;
	}
	function DoCleanStr($str){
		$str = htmlspecialchars(filter_var($str,FILTER_SANITIZE_STRING));
		$str = get_magic_quotes_gpc() ? stripslashes($str):$str;	
		$str = trim($str);
		$str = (strlen($str) > 0) ? "$str" : 'null';
		return $str; 
	}
	
	function SendMail($att){
		
			$to = $_POST["to"];
			$to = urldecode($to);
			if (eregi("(\r|\n)", $to)):
			     return false;
			else:
				if (eregi("(\r|\n)", $_POST['repto'])):
					return false;
				else:
					$to      = $att['to'];
					$subject = $att['subject'];
					$message = $att['message'];
					$headers = 'From: ' .$att['repto']. "\r\n" .
						'Reply-To:' .$att['repto']. "\r\n" .
						'X-Mailer: PHP/' . phpversion();
					$res = mail($to, $subject, $message, $headers);
					if($res):
						return true;
					else:
						return false;
					endif;
				endif;
			endif;
	}
	
	function TempAccess($uname,$pass){
		if(md5($uname) == AUSERNAME && md5($pass) == AUSERPASSWORD):
			$_SESSION['isLOG'] = 1;
			return true;
		else:
			return false;
		endif;
	}
	
	function DoLogout(){
		session_destroy();
		$this->CloseDB();
		header('location:index.php');
	}
	
	function RenderCSV($title, $query, $fields){
		$datac = 0;
		$csv_output = "";
		$csv_output .= $fields;
		$get = $this->conn->query($query);
		$datas = array();
		while($data = $get->fetch_array()):
			$datas[] = $data;
		endwhile;
		$cnter = 0;	
		$getcount = explode(",",$fields);
		for($cntx=0;$cntx<count($datas);$cntx++):
			$xcnt = 0;
			for($cnts=0;$cnts<count($getcount);$cnts++):
				$xcnt++;
				$sepa = ($xcnt<count($getcount))? "," : "\n";
				$cleanstr = str_replace(","," ",$datas[$cntx][$cnts]);
				$cleanstr = str_replace("\n"," ",$cleanstr);
				$csv_output .= $cleanstr.$sepa;
			endfor;
		endfor;
		$filename = $title."_".date("Y-m-d_H-i",time());
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header("Content-disposition: filename=".$filename.".csv");
		print $csv_output;
		exit;
	}

	function PrepareCSV($table,$query){
		$get = $this->conn->query("SHOW COLUMNS FROM $table");
		$fields = array();
		while($data = $get->fetch_array()):
			$fields[] = $data;
		endwhile;
		$field = "";
		$xcn = 0;
		foreach($fields as $colraw):
			$xcn++;
			$sepa = ($xcn < count($fields))? "," : "\n";
			$field .= ucfirst($colraw['Field']).$sepa;
		endforeach;
		$this->RenderCSV($table,$query,$field);
	}
	
	function Countries($selected = NULL){
		$country_array = array("Afghanistan", "Aland Islands", "Albania", "Algeria", "American Samoa", "Andorra", "Angola", "Anguilla", "Antarctica", "Antigua", "Argentina", "Armenia", "Aruba", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Barbuda", "Belarus", "Belgium", "Belize", "Benin", "Bermuda", "Bhutan", "Bolivia", "Bosnia", "Botswana", "Bouvet Island", "Brazil", "British Indian Ocean Trty.", "Brunei Darussalam", "Bulgaria", "Burkina Faso", "Burundi", "Caicos Islands", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Cayman Islands", "Central African Republic", "Chad", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Colombia", "Comoros", "Congo", "Congo, Democratic Republic of the", "Cook Islands", "Costa Rica", "Cote d'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Falkland Islands (Malvinas)", "Faroe Islands", "Fiji", "Finland", "France", "French Guiana", "French Polynesia", "French Southern Territories", "Futuna Islands", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Gibraltar", "Greece", "Greenland", "Grenada", "Guadeloupe", "Guam", "Guatemala", "Guernsey", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard", "Herzegovina", "Holy See", "Honduras", "Hong Kong", "Hungary", "Iceland", "India", "Indonesia", "Iran (Islamic Republic of)", "Iraq", "Ireland", "Isle of Man", "Israel", "Italy", "Jamaica", "Jan Mayen Islands", "Japan", "Jersey", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea", "Korea (Democratic)", "Kuwait", "Kyrgyzstan", "Lao", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libyan Arab Jamahiriya", "Liechtenstein", "Lithuania", "Luxembourg", "Macao", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Martinique", "Mauritania", "Mauritius", "Mayotte", "McDonald Islands", "Mexico", "Micronesia", "Miquelon", "Moldova", "Monaco", "Mongolia", "Montenegro", "Montserrat", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "Netherlands Antilles", "Nevis", "New Caledonia", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Niue", "Norfolk Island", "Northern Mariana Islands", "Norway", "Oman", "Pakistan", "Palau", "Palestinian Territory, Occupied", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Pitcairn", "Poland", "Portugal", "Principe", "Puerto Rico", "Qatar", "Reunion", "Romania", "Russian Federation", "Rwanda", "Saint Barthelemy", "Saint Helena", "Saint Kitts", "Saint Lucia", "Saint Martin (French part)", "Saint Pierre", "Saint Vincent", "Samoa", "San Marino", "Sao Tome", "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Georgia", "South Sandwich Islands", "Spain", "Sri Lanka", "Sudan", "Suriname", "Svalbard", "Swaziland", "Sweden", "Switzerland", "Syrian Arab Republic", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "The Grenadines", "Timor-Leste", "Tobago", "Togo", "Tokelau", "Tonga", "Trinidad", "Tunisia", "Turkey", "Turkmenistan", "Turks Islands", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States", "Uruguay", "US Minor Outlying Islands", "Uzbekistan", "Vanuatu", "Vatican City State", "Venezuela", "Vietnam", "Virgin Islands (British)", "Virgin Islands (US)", "Wallis", "Western Sahara", "Yemen", "Zambia", "Zimbabwe");
		
		$htmls = NULL;
		foreach($country_array as $index => $value):
			$select = ($selected == $value)? "selected=\"selected\"" : NULL;
			$htmls .= "<option value=\"$value\" $select >$value</option>";
		endforeach;
		return $htmls;
	}
	
	
	function Validate($param){
		$errholder = array();
		foreach($param as $index => $value):
			switch($value):
				case 'required':
						if($this->CheckRequired(@$_POST[$index])):
							$errholder[] = $this->CheckRequired(@$_POST[$index],$index);
						endif;
				break;
				case 'email':
						if($this->CheckEmailAddress(@$_POST[$index]))
							$errholder[] = $this->CheckEmailAddress(@$_POST[$index], $index);
				break;
			endswitch;
			if($index == 'group'):
					foreach($value as $indexchild => $vals):
						if($this->CheckGroup($indexchild, $vals))
							$errholder[] = $this->CheckGroup($indexchild, $vals);
					endforeach;
			endif;
			if($index == 'file'):
					foreach($value as $indexf => $valsf):
							
								if(@$_FILES[$indexf]['name']):
									if(@$valsf['required']):
										$errholder[] = $this->CheckRequired(@$_FILES[$indexf]['name'],$indexf);
									endif;
									if(strlen(@$valsf['size']) > 0):
										if($this->CheckFileSize($valsf['size'],@$_FILES[$indexf]['size'],$indexf))
											$errholder[] = $this->CheckFileSize($valsf['size'],@$_FILES[$indexf]['size'],$indexf);
									endif;
									if(@$_FILES[$indexf]['error'] == 0):
										if(strlen(@$valsf['format']) > 0):
											if($this->CheckFileFormat($valsf['format'],@$_FILES[$indexf]['type'],$indexf))
												$errholder[] = $this->CheckFileFormat($valsf['format'],@$_FILES[$indexf]['type'],$indexf);
										endif;
									else:
										$this->StoreErrors($indexf,"File should too large");
									endif;
								endif;
							
					endforeach;
			endif;
			
		endforeach;
		if(count($errholder) > 0)
			return true;
		else
			return false;
	}
	
	
	function CheckFileSize($size,$value,$targ = false){
		if($value < $size):
			return false;
		else:
			$this->StoreErrors($targ,"File should not exceed to ".$size." bytes");
			return true;
		endif;
	}
	
	function CheckFileFormat($type,$value,$targ = false){
		$gettype = explode('/',$value);
		$comtype = explode(",",$type);
		if(in_array($gettype[1],$comtype)):
			return false;
		else:
			$this->StoreErrors($targ,"Invalid file type");
			return true;
		endif;
	}
	
	function CheckRequired($value,$targ = false){
		if(strlen(trim($value)) > 0):
				return false;
		else:
				$this->StoreErrors($targ,"Field is required");
				return true;
		endif;
	}
	
	function CheckEmailAddress($email,$targ = false){
		if(filter_var($email, FILTER_VALIDATE_EMAIL)):
				return false;
		else:
				$this->StoreErrors($targ,"Invalid email address");
				return true;
		endif;
	}
	
	function CheckGroup($pindex,$arr){
		$gettars = explode("|",$arr['targets']);
		$err = 0;
		if($arr['rule'] == 'OR'):
			foreach($gettars as $vals):
				if(@$_POST[$vals]):
					return false;
					$err = 0;
					break;
				else:
					$err++;
				endif;
			endforeach;
			if($err > 0):
				$this->StoreErrors($pindex,"Please select one from the options");
				return true;
			else:
				return false;
			endif;
		else:
			$err = 0;
			foreach($gettars as $vals):
				if(!@$_POST[$vals]):
					$err++;
					break;
				endif;
			endforeach;
			if($err > 0):
				return true;
				$this->StoreErrors($pindex,"All options are required");
			else:
				return false;
			endif;
		endif;
	}
	
	function StoreErrors($index, $errtext){
		$this->errors[$index] = $errtext;
	}
	
	function Errors($index = NULL){

		if(count($this->errors) > 0)
			return @$this->errors[$index];
	}

	function DoUpload($file,$key,$path){
		$getext = explode(".",$file['name']);
		$type = explode("/",$file['type']);
		$filename = "file".$key.'-'.md5($file['name']).".".$type[1];
		$res = move_uploaded_file($file["tmp_name"], $path.$filename);
		if($res)
			return $path.$filename;
		else
			return NULL;
	}

	function Dubai(){
		$areas = "Abu Hail,Al Awir,Al Barsha,Al Garhoud,Al Hamriya,Al Karama,Al Mamzar,Al Muntasa,Al Muraqqabat,Al Quoz,Al Qusais,Al Rashidiya,Al Rigga,Al Safouh,Al Satwa,Al Shindagha,Al Wasl,Baniyas,Burj Khalifa,Corniche Deira,Deira,Dubai Investment Park,Dubai Marina,Emirates Hill,Hatta,Hor Al Anz,Jebel Ali,Jumeira,Jumeirah Garden City,Jumeirah Lake Towers,Jumeira Palm,Mankhool,Mirdiff,Nadd Al Hamar,Nadd Al Shiba,Naif,Oud Metha,Port Saeed,Ranches,Ras Al Khor,Satwa,Sofouh,Sports City,Trade Center,Umm Al Sheif,Umm Hurair,Umm Ramool,Umm Suqeim,Za'abeel";
		return explode(",", $areas);
	}

	function ListCommunity($default){
		$communities = $this->Dubai();
		$list = NULL;
		foreach($communities as $index => $vas):
			$selected = ($vas == $default)? "selected" : NULL;
			$list .= "<option value=\"$vas\" $selected >$vas</option>";
		endforeach;
		return $list;
	}
		
}


$japp = new phpClasses();
?>