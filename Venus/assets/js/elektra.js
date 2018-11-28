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
					contentType: "application/json; charset=utf-8",
					data: JSON.stringify($(eob).serializeArray()),
					dataType:'json',
					timeout: 60000,
					success: function (data) { 
					$.each(data, function(key, value) {
						if(value.type === "text")
							$(key).text(value.data);
						else if(value.type === "html")
							$(key).fadeOut(500, function() {
							$(this).html(value.data).fadeIn(500);
							});
						else if(value.type === "append")
							$(key).append(value.data);
						else if(value.type === "code")
							eval(value.data);
						else if(value.type === "alert")
							alert(value.data);
						else if(value.type === "redirect")
							$(location).attr('href', value.data);
						else
							$(key).attr(value.type,value.data);
					});
					},
					error: function (data) { 
					$.each(data, function(key, value) {
						if(value.type === "text")
							$(key).text(value.data);
						else if(value.type === "html")
							$(key).fadeOut(500, function() {
							$(this).html(value.data).fadeIn(500);
							});
						else if(value.type === "append")
							$(key).append(value.data);
						else if(value.type === "code")
							eval(value.data);
						else if(value.type === "alert")
							alert(value.data);
						else if(value.type === "redirect")
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
			/*if(eob.indexOf("#") !== -1)
			{
				eob = eob.replace("#","");
				var form = document.getElementById(eob);
			}
			else if(eob.indexOf(".") !== -1)
			{
				eob = eob.replace(".","");
				var form = document.getElementsByClassName(eob)[0];
			}
			else
			{
				var form = document.querySelector(eob);
			}*/
			var form = document.querySelector(eob);
			form.addEventListener( "submit", function(e) {
			e.preventDefault();
			var obj = {};
			var j = 0;
			var elements = form.querySelectorAll( "input, select, textarea" );
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
						if(Resp[key].type === "text")
							document.querySelector(key).value = new Resp[key].data;
						else if(Resp[key].type === "html")
							document.querySelector(key).innerHTML = Resp[key].data;
						else if(Resp[key].type === "append")
							document.querySelector(key).innerHTML += Resp[key].data;
						else if(Resp[key].type === "code")
							eval(Resp[key].data);
						else if(Resp[key].type === "alert")
							alert(Resp[key].data);
						else if(value.type === "redirect")
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