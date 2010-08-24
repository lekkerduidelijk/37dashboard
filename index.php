<?php

/**
 * Simple Basecamp and Highrise feed page
 * @author: Rutger Laurman
 * @date: 23-07-2010
 */

// Include SimplePie class
include("php/simplepie.inc.php");

// Create new class
$feed = new SimplePie();

// Set Feeds
$rssBasecamp  = "https://a611ec0e956a75efd52941888915460757344399:x@iaspect.basecamphq.com/feed/recent_items_rss";
$atomHighrise = "https://e25a45e4d2e71fee5c6688f8208cafb4e1775e1c:x@iaspect.highrisehq.com/recordings.atom";
$atomGmail    = "https://iaspect.support:internetbureauiaspect@mail.google.com/mail/feed/atom/@actions/";
//https://mail.google.com/mail/feed/atom
$isHighrise = $isBasecamp = $isGmail = false;

// Check if feed is set to Highrise
switch($_GET['feed']){

   case 'highrise':
      $rssUrl = $atomHighrise;
      $listClassname = 'highrise';
      $isHighrise = true;
      break;
      
   case 'gmail':
      $rssUrl = $atomGmail;
      $listClassname = 'gmail';
      $isGmail = true;
      break;
      
   default:
      $rssUrl = $rssBasecamp;
      $listClassname = 'basecamp';
      $isBasecamp = true;      

}

// Set which feed to process.
$feed->set_feed_url($rssUrl);

// Set cache time
$feed->set_cache_duration(120);
$feed->set_cache_duration(0);
 
// Run SimplePie.
$feed->init();
 
// This makes sure that the content is sent to the browser as text/html and the UTF-8 character set (since we didn't change it).
$feed->handle_content_type();


// Custom function to shorten description string
function shorten($string, $length)
{
    // By default, an ellipsis will be appended to the end of the text.
    $suffix = '...';
 
    // Convert 'smart' punctuation to 'dumb' punctuation, strip the HTML tags,
    // and convert all tabs and line-break characters to single spaces.
    $short_desc = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));
 
    // Cut the string to the requested length, and strip any extraneous spaces 
    // from the beginning and end.
    $desc = trim(substr($short_desc, 0, $length));
 
    // Find out what the last displayed character is in the shortened string
    $lastchar = substr($desc, -1, 1);
 
    // If the last character is a period, an exclamation point, or a question 
    // mark, clear out the appended text.
    if ($lastchar == '.' || $lastchar == '!' || $lastchar == '?') $suffix='';
 
    // Append the text.
    $desc .= $suffix;
 
    // Send the new description back to the page.
    return $desc;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
   <title>37 dashboard - A Basecamp, Highrise and Gmail feed reader</title>
   <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
   <meta http-equiv="refresh" content="300" />
   <link rel="stylesheet" type="text/css" href="css/styles.css" />
   <script type="text/javascript">
      // cheap refresh script
      var refreshinterval=120;
      var starttime;
      var nowtime;
      var timer;
      var reloadseconds=0;
      var secondssinceloaded=0;

      function startTimer() {
      	starttime=new Date();
      	starttime=starttime.getTime();
          countdown();
      }

      function countdown() {
      	nowtime= new Date()
      	nowtime=nowtime.getTime()
      	secondssinceloaded=(nowtime-starttime)/1000
      	reloadseconds=Math.round(refreshinterval-secondssinceloaded)
         if (refreshinterval>=secondssinceloaded) {
            timer=setTimeout("countdown()",1000)
            document.getElementById('countSeconds').innerHTML= reloadseconds;
         }
         else {
            clearTimeout(timer)
            window.location.reload(true)
         } 
      }
      
      function stopTimer(){
         clearTimeout(timer);
         document.getElementById('stopButton').innerHTML   = 'stopped';
         document.getElementById('countSeconds').innerHTML = 'reload'
      }

      window.onload=function(){
         document.getElementById('stopButton').onclick=stopTimer;
         startTimer();
      }
      
   </script>
</head>
<body>

<div id="wrapper">

	<div id="header">
		<h1><!-- <a href="<?php echo $feed->get_permalink(); ?>"><?php echo $feed->get_title(); ?></a> - -->
		<?php if($isHighrise) { ?>
		<a href="javascript:void(0);" onclick="window.location='?feed=basecamp'">Basecamp</a> <span style="color:#ccc">|</span> <strong>Highrise</strong> <span style="color:#ccc">|</span> <a href="javascript:void(0);" onclick="window.location='?feed=gmail'">Support</a>
		<?php } elseif($isBasecamp) { ?>
		<strong>Basecamp</strong> <span style="color:#ccc">|</span> <a href="javascript:void(0);" onclick="window.location='?feed=highrise'">Highrise</a> <span style="color:#ccc">|</span> <a href="javascript:void(0);" onclick="window.location='?feed=gmail'">Support</a>
		<?php } elseif($isGmail) { ?>
		<a href="javascript:void(0);" onclick="window.location='?feed=basecamp'">Basecamp</a> <span style="color:#ccc">|</span> <a href="javascript:void(0);" onclick="window.location='?feed=highrise'">Highrise</a> <span style="color:#ccc">|</span> <strong>Support</strong>
		<?php } ?>
		</h1>
		<span id="refreshTimer">
		   <span id="stopButton"><a href="javascript:void(0);">stop timer</a></span>
   		<a onclick="window.location.reload()" href='javascript:void();'><strong id="countSeconds"></strong></a>
      </span>
	</div>

      <ul id="rssFeed" class="<? echo $listClassname; ?>">
   	<?php foreach ($feed->get_items() as $item): ?>
<?php
   $itemTitle = $item->get_title();
   $titlePrefix = str_replace("Comment posted:", "[C]", $itemTitle);
//   $titlePrefix = str_replace("File uploaded:", "[F]", $itemTitle);

?>
         <li class="rssItem">
   			<h2><a href="<?php
   			// gmail uses different authentication URL
   			if($isGmail) { 
   			   echo "https://www.google.com/accounts/ServiceLoginAuth?continue=http://mail.google.com/gmail&amp;service=mail&amp;Email=iaspect.support&amp;Passwd=internetbureauiaspect&amp;null=Sign+in";
   			} else { 
   			 echo $item->get_permalink(); 
   			} ?>"><?php echo $item->get_title(); ?></a></h2>
   			<span class="time"><em>Posted on <?php echo $item->get_date('j F Y | g:i a'); ?> by <?php if ($author = $item->get_author()) { echo $author->get_name(); } ?></em></span>
   			<div class="rssDescription">
   			<?php 
//   			   if($isHighrise)
//   			      echo shorten($item->get_description(), 250); 
//   			   else
   			      echo $item->get_description();
   			   ?>
   			</div>
       </li>
 
 
   	<?php endforeach; ?>
   	</ul>
      </div>

   </body>
</html>               