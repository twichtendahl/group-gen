<?php if(!isset($page_title)) { $page_title = 'Instructor Area'; } ?>
<!doctype html>
<html lang="en">
   <head>
       <title>Team Generator - <?php echo $page_title; ?></title>
       <meta charset="utf-8">
       <link rel="stylesheet" media="all"
            href="<?php echo url_for('/stylesheets/styles.css'); ?>" />
       <link rel="stylesheet"
            href="https://fonts.googleapis.com/css?family=Tangerine" />
   </head>

   <body>
       <h1>Generate Teams App</h1>
       <navigation>
         <ul>
           <li><a href="<?php echo url_for('/index.php'); ?>">Home</a></li>
         </ul>
       </navigation>