/******************************
**	PHP Login Ajax JQuery
**	programmer@chazzuka.com
**	http://www.chazzuka.com/
**      Edited to work with phpuserclass
**      ( phpuserclass.com )
*******************************/
// GLOBAL PARAMS
var file		=	'login/form';
var file2		=	'login/register';
var submission		=	'login';
var placeholder		=	'#wrapper';
var waitholder		=	'#err';
var waitnote		=	'<img alt="" src="img/wait.gif" /><br />Please Wait ...';
			
// DOM READY
$(document).ready(function()
{ 
	loginform();
});

function loginform()
{
	// FIRST LOAD FORM
	$(waitholder).html(waitnote);
	$(placeholder).fadeOut('fast').html($.ajax({url: file,async: false}).responseText).fadeIn('slow');
	$(waitholder).fadeOut('slow').hide();
	$(placeholder).find('a#register').click(
			function(){registration();return false;});	
	// AJAX SUBMIT OPTIONS /
	var options = { 
		beforeSubmit:	FilterForm,
		success:		ShowResult,
		//target:		target,
		url:			submission,
		type:      		'post',
		dataType:  		'json',
		clearForm: 		false,
		resetForm: 		false,
		timeout:   		60000, 
		error:			ShowError
	}; 
	// ON SUBMIT FORM
	$('#ajaxform').submit(function(){$(this).ajaxSubmit(options);return false;}); 
	//*/
}

function registration()
{
	$(waitholder).html(waitnote);
	$(placeholder).fadeOut('fast').html($.ajax({url: file2,async: false}).responseText).fadeIn('slow');
	$(waitholder).fadeOut('slow').hide();
	$(placeholder).find('a#login').click(
			function(){loginform();return false;});
	// AJAX SUBMIT OPTIONS /
	var options = { 
		beforeSubmit:	FilterForm,
		success:		ShowResult,
		//target:		target,
		url:			file2,
		type:      		'post',
		dataType:  		'json',
		clearForm: 		false,
		resetForm: 		false,
		timeout:   		60000,
		error:			ShowError
	}; 
	// ON SUBMIT FORM
	$('#ajaxform').submit(function(){$(this).ajaxSubmit(options);return false;});
}
			
// SHOW RESULT
function ShowResult(data)
{
	$(waitholder).html(data.title).slideDown('slow');
	if(data.success)
	{
		$(waitholder).addClass("green");
		window.location=data.redirect;
	}
	//{$(placeholder).html(data.content).slideDown('slow');}
}		

function ShowError(xhr, type, e)
{
	if(e=="timeout")
		$(waitholder).html("<b>Login Failed: Sever does not respond for timout exceded</b>").slideDown('slow');
}
			
// WAIT MESSAGE
function wait()
{
	$(waitholder).html(waitnote).fadeIn('fast');
}
			
// CLEAR WAIT MESSAGE
function wipe()
{
	$(waitholder).fadeOut('fast').html('');
}
			
// VALIDATION
function FilterForm(formData, jqForm, options)
{ 
	$(waitholder).html(waitnote).fadeIn('fast');
	for (var i=0; i < formData.length; i++)
	{ 
		wait();
		switch(formData[i].name)
		{
			case 'nameuser':
				if(!formData[i].value.length)
				{
					$(waitholder).html('Username required').slideDown('slow');
					return false;
				}
				break;
			case 'passuser':
				var len = formData[i].value.length;
				if(len<4||len>50)
				{
					$(waitholder).html('Password required').slideDown('slow');
					return false;
				}
				break;															
		}
	}
}
