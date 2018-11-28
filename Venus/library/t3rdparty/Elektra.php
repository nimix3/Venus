<?php
// Elektra Class Library V.1 By NIMIX3 for VENUS FRAMEWORK that is Under MIT License.
// NOTE: PLEASE DO NOT EDIT OR SELL THIS CODE FOR COMMERCIAL PURPOSE EXCEPT REFER TO VENUS FRAMEWORK IN YOUR PRODUCT!
namespace Venus\library\t3rdparty;
class Elektra
{
	protected $Package = array();
	
	public function Execute($json=true)
	{
		if(boolval($json))
		{
			return json_encode($this->Package);
		}
		else
		{
			return $this->arrayToXML($this->Package);
		}
	}
	
	public function Response($json=true)
	{
		if(boolval($json))
		{
			ob_start();
			ob_clean();
			echo json_encode($this->Package);
			ob_end_flush();
		}
		else
		{
			ob_start();
			ob_clean();
			echo $this->arrayToXML($this->Package);
			ob_end_flush();
		}
	}
	
	public function Add($element,$data,$type='html')
	{
		if(isset($element) and !empty($element))
			$this->Package[$element] = array('data'=>$data, 'type'=>$type);
		return $this;
	}
	
	public function newCSRF($element,$handler='csrf',$elemname='token')
	{
		if(isset($element) and !empty($element))
		{
			$csrftoken = $this->GenerateCSRFToken($handler);
			$this->Package[$element] = array('data'=>'<input type="hidden" name="'.$elemname.'" value="'.$csrftoken.'">','type'=>'html');
		}
		return $this;
	}
	
	public function reCSRF($element,$handler='csrf')
	{
		if(isset($element) and !empty($element))
		{
			$csrftoken = $this->GenerateCSRFToken($handler);
			$this->Package[$element] = array('data'=>$csrftoken,'type'=>'value');
		}
		return $this;
	}
	
	public function Remove($element)
	{
		if(isset($element) and !empty($element))
			if(isset($this->Package[$element]) and !empty($this->Package[$element]))
				unset($this->Package[$element]);
		return $this;
	}
	
	public function Clear()
	{
		$this->Package = array();
		return $this;
	}
	
	private function generateXML($tag_in,$value_in="",$attribute_in="")
	{
		$return = "";
		$attributes_out = "";
		if (is_array($attribute_in)){
			if (count($attribute_in) != 0){
				foreach($attribute_in as $k=>$v):
					$attributes_out .= " ".$k."=\"".$v."\"";
				endforeach;
			}
		}
		return "<".$tag_in."".$attributes_out.((trim($value_in) == "") ? "/>" : ">".$value_in."</".$tag_in.">" );
	}
	
	private function arrayToXML($array_in)
	{
		$return = "";
		$attributes = array();
		foreach($array_in as $k=>$v):
			if ($k[0] == "@"){
				$attributes[str_replace("@","",$k)] = $v;
			} else {
				if (is_array($v)){
					$return .= $this->generateXML($k,arrayToXML($v),$attributes);
					$attributes = array();
				} else if (is_bool($v)) {
					$return .= $this->generateXML($k,(($v==true)? "true" : "false"),$attributes);
					$attributes = array();
				} else {
					$return .= $this->generateXML($k,$v,$attributes);
					$attributes = array();
				}
			}
		endforeach;
		return $return;
	}
	
	public function embed($reurnOnly=true)
	{
		$Out = '<script
				src="https://code.jquery.com/jquery-3.3.1.min.js"
				integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
				crossorigin="anonymous">
				</script>';
		$Out .= "<script>
				/*!
				* Venus Framework by NIMIX3 (https://github.com/nimix3/venus)
				* Licensed under the MIT license
				*/
				function Elektra(eob)
				{
					if (typeof jQuery != 'undefined')
					{
						try {
							$(eob).submit(function (e) {
							e.preventDefault();
							$.ajax({
									type: $(eob).attr('method'),
									url: $(eob).attr('action'),
									contentType: \"application/json; charset=utf-8\",
									data: JSON.stringify($(eob).serializeArray()),
									dataType:'json',
									timeout: 60000,
									success: function (data) { 
									$.each(data, function(key, value) {
										if(value.type === \"text\")
											$(key).text(value.data);
										else if(value.type === \"html\")
											$(key).fadeOut(500, function() {
											$(this).html(value.data).fadeIn(500);
											});
										else if(value.type === \"append\")
											$(key).append(value.data);
										else if(value.type === \"code\")
											eval(value.data);
										else if(value.type === \"alert\")
											alert(value.data);
										else if(value.type === \"redirect\")
											$(location).attr('href', value.data);
										else
											$(key).attr(value.type,value.data);
									});
									},
									error: function (data) { 
									$.each(data, function(key, value) {
										if(value.type === \"text\")
											$(key).text(value.data);
										else if(value.type === \"html\")
											$(key).fadeOut(500, function() {
											$(this).html(value.data).fadeIn(500);
											});
										else if(value.type === \"append\")
											$(key).append(value.data);
										else if(value.type === \"code\")
											eval(value.data);
										else if(value.type === \"alert\")
											alert(value.data);
										else if(value.type === \"redirect\")
											$(location).attr('href', value.data);
										else
											$(key).attr(value.type,value.data);
									});
									}
								});
							});
						}
						catch(err) {
							console.log(err.message);
						}
					}
					else
					{
						try {
							/*if(eob.indexOf(\"#\") !== -1)
							{
								eob = eob.replace(\"#\",\"\");
								var form = document.getElementById(eob);
							}
							else if(eob.indexOf(\".\") !== -1)
							{
								eob = eob.replace(\".\",\"\");
								var form = document.getElementsByClassName(eob)[0];
							}
							else
							{
								var form = document.querySelector(eob);
							}*/
							var form = document.querySelector(eob);
							form.addEventListener( \"submit\", function(e) {
							e.preventDefault();
							var obj = {};
							var j = 0;
							var elements = form.querySelectorAll( \"input, select, textarea\" );
							for( var i = 0; i < elements.length; ++i ) 
							{
								var element = elements[i];
								var name = element.name;
								var value = element.value;
								if( name )
								{
									obj[j] = {name:name, value:value};
									j++;
								}
							}
							var data = JSON.stringify(obj);
							var xmlHttp = new XMLHttpRequest();
							xmlHttp.onreadystatechange = function()
							{
								if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
								{
									var Resp = JSON.parse(xmlHttp.responseText);
									for(var key in Resp)
									{
										if(Resp[key].type === \"text\")
											document.querySelector(key).value = new Resp[key].data;
										else if(Resp[key].type === \"html\")
											document.querySelector(key).innerHTML = Resp[key].data;
										else if(Resp[key].type === \"append\")
											document.querySelector(key).innerHTML += Resp[key].data;
										else if(Resp[key].type === \"code\")
											eval(Resp[key].data);
										else if(Resp[key].type === \"alert\")
											alert(Resp[key].data);
										else if(value.type === \"redirect\")
											window.location.replace(value.data);
										else
											document.querySelector(key).Resp[key].type = value.data;
									}
								}
							};
							xmlHttp.open(form.method, form.action, true);
							xmlHttp.send(data);
							});
						}
						catch(err) {
							console.log(err.message);
						}
					}
				}
				</script>";
		if(boolval($reurnOnly))
		{
			return $Out;
		}
		else
		{
			echo $Out;
		}
	}
	
	private function GenerateCSRFToken($handler='csrf')
	{
		if(!isset($handler) or empty($handler))
			return false;
		session_start();
		$_SESSION[$handler] = md5(uniqid(rand(), true));
		session_regenerate_id();
		return true;
	}
}