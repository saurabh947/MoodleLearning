 <link class="include" rel="stylesheet" type="text/css" href="js/jquery.jqplot.min.css" />
    <style type="text/css">

.activeLink
{
	color:red;
}
		.block_header {
			background: url("images/line-pat.png") repeat-y scroll left center transparent;
			padding-left: 15px;
			font-size:14px;
		}	

		/*a.link:link{
		  color:#666;
		}
		
		a.link:visited{
		  color:purple;
		}
		a:hover{
		  color:orange;
		}
		a:active{
		  color:red;
		}
		a.link:focus { color:#000; }*/
			

		
		
		
#Course
{
	font-weight:bold;
	
}
#Count{
	font-weight:bold;
}

.datespan
{
	color:#0CAABF;
}

	</style> 
    <script language="javascript" type="text/javascript" src="js/excanvas.js"></script>
      <script class="include" type="text/javascript" src="js/js/jquery.min.js"></script>
    
  <!-- Don't touch this! -->
  
  
      <script class="include" type="text/javascript" src="js/jquery.jqplot.min.js"></script>
      <script type="text/javascript" src="js/shCore.min.js"></script>
      <script type="text/javascript" src="js/shBrushJScript.min.js"></script>
      <script type="text/javascript" src="js/shBrushXml.min.js"></script>
  <!-- End Don't touch this! -->
  

    <script class="include" language="javascript" type="text/javascript" src="js/jqplot.barRenderer.min.js"></script>
    <script class="include" language="javascript" type="text/javascript" src="js/jqplot.categoryAxisRenderer.min.js"></script>
    <script class="include" language="javascript" type="text/javascript" src="js/jqplot.pointLabels.min.js"></script>
        
  <!-- End additional plugins -->
<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');



$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // vrssregistration instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('vrssregistration', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $vrssregistration  = $DB->get_record('vrssregistration', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $vrssregistration  = $DB->get_record('vrssregistration', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $vrssregistration->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('vrssregistration', $vrssregistration->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context = get_context_instance(CONTEXT_MODULE, $cm->id);

if (has_capability('mod/vrssregistration/view.php:read', $context)) 
{
	require_login($course, true, $cm);
}
add_to_log($course->id, 'vrssregistration', 'view', "view.php?id={$cm->id}", $vrssregistration->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/vrssregistration/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($vrssregistration->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// other things you may want to set - remove if not needed
//$PAGE->set_cacheable(false);
//$PAGE->set_focuscontrol('some-html-id');
//$PAGE->add_body_class('vrssregistration-'.$somevar);

// Output starts here
echo $OUTPUT->header();
/*
if ($vrssregistration->intro) { // Conditions to show the intro can change to look for own settings or whatever
    echo $OUTPUT->box(format_module_intro('vrssregistration', $vrssregistration, $cm->id), 'generalbox mod_introbox', 'vrssregistrationintro');
}
*/
if ($course->guest )
{
	echo "Not Allowed";
}
?>

    
    

<?php
      include("getjsondata.php");
?>
  
  <script type="text/javascript">
  var currenttype=2;
  var CurrentView='day';

   
  $(document).ready(function(){ 
   
	 
			
	 	showjqplot(currenttype);
		 	//$(".jqplot-axis jqplot-yaxis").css("left","-10px");
	    //$(".jqplot-axis jqplot-xaxis").css("bottom","-10px");
//		$("#chartdiv").children(".jqplot-axis jqplot-yaxis").click('javascript:alert("hello")');
		
				$("#chartdiv").find(".jqplot-yaxis-label").css({"-moz-transform": "rotate(270deg)","-webkit-transform":"rotate(270deg)","-ms-transform": "rotate(270deg)","-o-transform":"rotate(270deg)"});
						$("#chartdiv").find(".jqplot-xaxis-label").css({"top":"30px"});
						$("#chartdiv").find(".jqplot-yaxis-tick").css({"left":"65px"});
						$("#chartdiv").find(".jqplot-xaxis-tick").css({"top":"8px"});	
						$("#chartdiv").find(".jqplot-title").css({"top":"-12px"});	
						showregisteredUsers();
		coursedata('day');
	  		 
		setInterval(varName, 20000);  
	
	  
          
 });

	var varName = function(){
		 
		 showjqplot(currenttype);
		 
		$("#chartdiv").find(".jqplot-yaxis-label").css({"-moz-transform": "rotate(270deg)","-webkit-transform":"rotate(270deg)","-ms-transform": "rotate(270deg)","-o-transform":"rotate(270deg)"});
		$("#chartdiv").find(".jqplot-xaxis-label").css({"top":"30px"});
		$("#chartdiv").find(".jqplot-yaxis-tick").css({"left":"65px"});
		$("#chartdiv").find(".jqplot-xaxis-tick").css({"top":"8px"});		 
		$("#chartdiv").find(".jqplot-title").css({"top":"-12px"});
		 showregisteredUsers();	
		coursedata(CurrentView);
						
		 
	};
	  
  var isreplot=false;
  function drawchart(type, data1, ticks)
  {
	  if(isreplot)
	  {
		$.jqplot('chartdiv', [data1],getoptions(type, [ticks])).replot();

	  }
	  isreplot=true;
	  
	   $.jqplot('chartdiv', [data1], getoptions(type, [ticks]));
	   
  }
  
  function getoptions(type, ticks)  
	{
		var tickData= ticks.toString().split(",");
		var m_names = new Array("January", "February", "March", 
		"April", "May", "June", "July", "August", "September", 
		"October", "November", "December");
	  	var d = new Date();
		var curr_date = d.getDate();
		var curr_month = d.getMonth();
		var curr_year = d.getFullYear();	  
		 			
	var str={	
		series:[{color:'#1B6CA6'}]		,		   
			title: (type==1?"Weekly Registrations":(type==2?"Daily Registrations":"Monthly Registrations")),
			seriesDefaults:{
				renderer:$.jqplot.BarRenderer,
				pointLabels: { show: true,
				hideZeros:true },
				rendererOptions: {
					//barPadding: 8,      // number of pixels between adjacent bars in the same
										// group (same category or bin).
					//barMargin: 10,      // number of pixels between adjacent groups of bars.
					//barDirection: 'vertical', // vertical or horizontal.
					barWidth: 20    // width of the bars.  null to calculate automatically.
					//shadowOffset: 2,    // offset from the bar edge to stroke the shadow.
					//shadowDepth: 5,     // nuber of strokes to make for the shadow.
					//shadowAlpha: 0.8,   // transparency of the shadow.
				}
								
			},			
			axesDefaults: {
				show: false, 
				tickRenderer: $.jqplot.CanvasAxisTickRenderer,								
				tickOptions: {
					show:true,
					angle: 0,
					showGridline: false,
					markSize: 10
				}
			},							
			rendererOptions: {
				barMargin: 8,
				barWidth:10,
				highlightMouseDown: true   
			},
			dataRendererOptions: {
				unusedOptionalUrl: "getjsondata.php"
			},
			resetAxes:{yaxis:true, y2axis:true},
			
			axes: {
			  xaxis: 
			  {
				  label:(type==1?("Weeks of " +  m_names[curr_month] + " " + curr_year ):(type==2?  m_names[curr_month] + " " + curr_year  :(type=="3"?"Year " + curr_year:""))),
				  renderer: $.jqplot.CategoryAxisRenderer,					
				  ticks: tickData,
				  tickOptions: 
				  {
					 angle: 0
				  }		 				  
			  },
			yaxis: {
			  label:'Registrations',
			  padMin: 0,
			  left:"-10px",
			  width:100,	
			  align:"right"	  
			}                                        
		},
		grid: {
			drawGridLines: true,        // wether to draw lines across the grid or not.
			gridLineColor: '#cccccc',   // *Color of the grid lines.
			background: '#fff',      // CSS color spec for background color of grid.
			borderColor: '#cbcbcb',     // CSS color spec for border around grid.
			borderWidth: 2.0,           // pixel width of border around grid.
			shadow: true,               // draw a shadow for grid.
			shadowAngle: 45,            // angle of the shadow.  Clockwise from x axis.
			shadowOffset: 1.5,          // offset from the line of the shadow.
			shadowWidth: 3,             // width of the stroke for the shadow.
			shadowDepth: 3,             // Number of strokes to make when drawing shadow.
										// Each stroke offset by shadowOffset from the last.
			shadowAlpha: 0.07,          // Opacity of the shadow
			renderer: $.jqplot.CanvasGridRenderer,  // renderer to use to draw the grid.
			rendererOptions: {}         // options to pass to the renderer.  Note, the default
										// CanvasGridRenderer takes no additional options.
		}	
				
	   };

	   return str;
	   	}  
  
  function showjqplot(type)
  {			 	 
          var ticks = ['1','2'];

          $.jqplot.config.enablePlugins = true;
          $.ajax(
                  {
                   type: "GET",   
                   url: "getjsondata.php",  
                   async:false,
                   data:"type=" + type,
                   dataType:"json",
                   //contentType: "application/json; charset=utf-8",
                   success : function(data)
                   {
					   	 
					   currenttype=type;
                       ticks =  data[1];                      
					  	drawchart(type, data[0], ticks);
							$("#chartdiv").find(".jqplot-yaxis-label").css({"-moz-transform": "rotate(270deg)","-webkit-transform":"rotate(270deg)","-ms-transform": "rotate(270deg)","-o-transform":"rotate(270deg)"});
						$("#chartdiv").find(".jqplot-xaxis-label").css({"top":"30px"});
						$("#chartdiv").find(".jqplot-yaxis-tick").css({"left":"65px"});
						$("#chartdiv").find(".jqplot-xaxis-tick").css({"top":"8px"});	
						$("#chartdiv").find(".jqplot-title").css({"top":"-12px"});
               },
               error : function()
               {
                   alert("error");
                      
               }
              });	  
  }
  
  function showregisteredUsers()
  {
	 
	  var str="No data found";
	  $.ajax({
			type: "GET",   
		   url: "getjsondata.php",  
		   async:false,
		   data:"type=" + 101,
		   dataType:"json",
		   success : function(data)
		   {
			   
			   if(data.length>0)
				{
					
					document.getElementById("reglabel").innerHTML="Registered all time" ;
					document.getElementById("registeredUsers").innerHTML= data[0].alltime;
					
					document.getElementById("todaylabel").innerHTML="Registered today" ;
					document.getElementById("registeredtoday").innerHTML= data[0].today;
					
					document.getElementById("highestlabel").innerHTML="Highest registration on a day :" ;
					
					document.getElementById("highest").innerHTML= data[0].highest;

					dv=document.createElement('span');
					dv.setAttribute('class', 'datespan');

					
					txt=document.createTextNode(data[0].highestdate);
					dv.appendChild(txt);
					document.getElementById('highestlabel').appendChild(dv);
					
				}
		   },
		   error : function()
		   {
			   alert("error");				  
		   }
					   		  
	  });
  } 
  
	
	function coursedata(viewname)
	{		
		 
		//alert("in");  
		CurrentView=viewname;
		
		$.ajax(
				{
				 type: "POST",   
				 url: "getjsondata.php",  
				 async:false,
				 data:"view=" + viewname,
				 dataType:"json",
				 //contentType: "application/json; charset=utf-8",
				 success : function(data)
				 {
					 $("#Course").empty();
					 $("#Count").empty();
						//alert('hello');
						for(i=0;i<data.length;i++)
						{
							
						
							$("#Course").append(data[i].coursename);
							$("#Course").append("<br/>");
						
							
								$("#Count").append(data[i].count);	
								$("#Count").append("<br/>");	
							
													
						}
						//$("#Course").html()
						//alert(data[0].count);
				 }
				});
				
			
	}
	$("#chartdiv").children(".jqplot-axis jqplot-yaxis").css("left","-100px");
	
  </script>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Untitled Document</title>
  </head>
  
  <body>
  
  <div>
  
  <a href="javascript:showjqplot(2)" style="margin-right:20px; text-decoration:none;" onClick="changeColor();">Days</a>
  <a href="javascript:showjqplot(1)" style="margin-right:20px;">Weeks</a>
  <a href="javascript:showjqplot(3)">Months</a>
  
  </div>
  
  <div id="chartdiv" style="height:400px;width:818px;margin-top:10px; margin-left:-30px; ">
  </div>  
  
  <div>
  <a href="<?php echo "detailview.php?id=" . $cm->id  ?> " style="margin-right:20px; text-decoration:none;">Detailed View</a>
  </div>
  <br>
<br>

  <div>  
      <div id="div1" style="float:left;width:152px;"> 
          <div id="registeredUsers" style="float:left;font-size:30px; width:152px; text-align:center;"> 
          </div>
          
          <div id="reglabel"  style="float:left;font-size:15px; width:152px; text-align:center;">            
          </div>            
      </div>

      <div id="div2" style="float:left;width:128px;"> 
          <div id="registeredtoday" style="float:left;font-size:30px; width:128px; text-align:center;"> 
          </div>
          <div id="todaylabel"  style="float:left;font-size:15px;  width:128px; text-align:center;">            
          </div>            
      </div>
 
       <div id="div3" style="float:left;width:280px;"> 
          <div id="highest" style="float:left;font-size:30px; text-align:center;width:280px;"> 
          </div>
          <div id="highestlabel"  style="float:left;font-size:15px; text-align:center;width:290px;">            
          </div>            
      </div>
  </div>
  
  <div style="margin-top:80px;">
	<div class="block_header" style="width:300px;">
    	<b>Top Courses</b>
    </div>
	<div style="width:300px; margin-top:5px; margin-bottom:5px; font-weight:bold;">
    	<a class="link" href="javascript:coursedata('day');" style="margin-right:28px;">Today</a>
       <a class="link" href="javascript:coursedata('7day');" style="margin-right:28px; margin-top:5px; margin-bottom:5px;">Last 7 Days</a>
        <a  class="link"  href="javascript:coursedata('30day');" style="margin-right:28px; margin-top:5px; margin-bottom:5px;">Last 30 Days</a>
    </div>
    
    <!--courses-->
    <div id="data" style="width:250px;" >
    	<div id="Course" style="float:left;padding-right:10px;">        	
        </div>
        <div  id="Count" style="float:right;width:10px;">
        	
        </div>
    	
    </div>
</div>


<?php
// Finish the page
echo $OUTPUT->footer();
?>
