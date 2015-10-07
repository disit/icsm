/* Icaro Supervisor & Monitor (ICSM).
   Copyright (C) 2015 DISIT Lab http://www.disit.org - University of Florence

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
   You should have received a copy of the GNU General Public License
   along with this program; if not, write to the Free Software
   Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA. */

(function($){
    
         //plugin name - smTableDataView
        $.smTableDataView=function(table,_options){
        //var $this = $(this);
        var TableDataView = this;
        var defaults={
    	};
        var $table=$(table);
        TableDataView.options={"ajax":false};
        
        TableDataView.init=function()
    	{
    		TableDataView.options=$.extend(defaults, _options);
    		if(TableDataView.options.ajax)
    		{
    			$table.on('click','#pagination a',function(event){
    				event.stopPropagation();
    				var href=$(this).attr("href");
    				if(TableDataView.options.ajax.container != undefined)
    					$(TableDataView.options.ajax.container).load(href);
    				else
    					$table.parent().load(href);
    				return false;
    			});
    			$table.on('submit',"form#TableDataViewFilters",function(event){
    				event.stopPropagation();
    				var url=$('form#TableDataViewFilters').attr("action");
    				var data = $('form#TableDataViewFilters').serializeArray();
    				$.ajax({
    				    type: 'POST',
    				    url: url,
    				    data: data,
    				    success:function(data,status,jqXHR){
    				    	if(TableDataView.options.ajax.container != undefined)
    	    					$(TableDataView.options.ajax.container).load(url);
    	    				else
    	    					$table.parent().load(url);
    				    		},
    				    dataType: "html"
    				  });
    			
    				return false;
    				
    			});
    			$table.on('click','a#tbw-refresh-cmd',function(event){
    				event.stopPropagation();
    				var href=$(this).attr("href");
    				if(TableDataView.options.ajax.container != undefined)
    					$(TableDataView.options.ajax.container).load(href);
    				else
    					$table.parent().load(href);
    				return false;
    			});
    		}
    		$table.on('click','input[name=all]',function(event)
				{
					if(this.checked)	
						$table.find(".tbwchk").each(function() { //loop through each checkbox
				                this.checked = true;  //select all checkboxes with class "checkbox1"               
				            });
					else
						$table.find(".tbwchk").each(function() { //loop through each checkbox
				                this.checked = false; //deselect all checkboxes with class "checkbox1"                       
				            });         
				   
				});
    		$table.find('#header-cmd a.tbw_command').unbind('click');
    		$table.find('#header-cmd a.tbw_command').on('click',function(event)
				{
						var functionName=$(this).attr("name");
						var fn = window[functionName];
						
						if(typeof fn !== "function")
						{
							return true;
						}
						event.stopPropagation();
						items=$table.find(".tbwchk:checked");
						var n = items.length;
						if(n>0)
						{
							//confirm action
							if($(this).attr("data-confirm")!="")
							{
								bootbox.dialog({
								  message: $(this).attr("data-confirm"),
								  title: $(this).attr("title"),
								  buttons: {
								    success: {
								      label: "Ok",
								      className:"button light-gray btn-xs btn btn-primary",
								      callback: function() {
								        	bootbox.hideAll();
								        	TableDataView.doCommand(items,fn);
								      }
								    },
								    main: {
								        label: "Cancel",
								        className:"button light-gray btn-xs btn btn-primary",
								      }
								    }
								  });
							}
							else
								TableDataView.doCommand(items,fn);
						}
						return false;
						
				});
	        	
	        	return this;
	    	};
	    	
	    	TableDataView.doCommand=function(items,command)
	    	{
	    		$('#tbwProgressModal').on('hidden.bs.modal', function () {
					TableDataView.reload();
					});
				$('#tbwProgressModal').modal('show');
				$('.bar').css("width","0%");
				$('.bar').text('0%');
				$('#mText').css("color","#777");
				var done = 0;
				items.each(function()
				{	
					var val=$(this).val();
					command(val,function(msg){
						done++;
						TableDataView.updateProgress((done/items.length)*100,msg);
					});
				});
	    	};
	    	
	    	TableDataView.reload=function(){
	    		location.reload(true);
	    	};
	    	
	    	TableDataView.commandDefault=function(cmd)
	    	{
	    		alert("No command set for "+cmd); 
	    	};
	    	
	    	TableDataView.doAjaxCommand=function(cmd,link,data,datatype,success,done)
	    	{
						$.ajax({	url:link,
									method:cmd,
									data:data,
									dataType:datatype,
									success:success,
									complete:done
								});

	    	};
	    	
	    	TableDataView.updateProgress=function(percentage,message)
        	{
	    		if(percentage > 100) 
	    			percentage = 100;
	    		$(".progress").hide();
	    		$("#progressbar-container #mText").html(message);
	    			var w = $('.bar').width();
	    		$(".progress").show();
	    		if(w<=percentage)
	    				$('.bar').css('width', Math.floor(percentage)+'%');
	    		$('.bar').text(Math.floor(percentage)+'%');
	    	
	    		if(percentage==100)
	    			 setTimeout(function(){$('.progress').removeClass('active');
	    			 $('#tbwProgressModal').modal('hide');
	    			 $('.bar').css('width', '0%');
	    			 },500);
	    		     
	        };
        	
        	TableDataView.init();
        	
    };
        // add the plugin to the jQuery.fn object
        $.fn.smTableDataView = function(options) {

            // iterate through the DOM elements we are attaching the plugin to
            return this.each(function() {

                // if plugin has not already been attached to the element
                if (undefined == $(this).data('smTableDataView')) {

                    // create a new instance of the plugin
                    // pass the DOM element and the user-provided options as arguments
                    var plugin = new $.smTableDataView(this, options);

                    // in the jQuery version of the element
                    // store a reference to the plugin object
                    // you can later access the plugin and its methods and properties like
                    // element.data('pluginName').publicMethod(arg1, arg2, ... argn) or
                    // element.data('pluginName').settings.propertyName
                    $(this).data('smTableDataView', plugin);

                }

            });

        };
 
}( jQuery ));

