# instagram-class
A class to get the latest posts made by an user on Instagram.


## usage
```
<?php
$insta = Instagram::getLast('lucaslealdev');
$second = Instagram::getNth('lucaslealdev',1); 
if($insta){?>
  <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/<?= $insta?>/?utm_source=ig_embed&amp;utm_medium=loading" data-instgrm-version="12"></blockquote>
  <script async src="https://www.instagram.com/embed.js"></script>
<?php }?>
if($second){?>
  <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/<?= $second?>/?utm_source=ig_embed&amp;utm_medium=loading" data-instgrm-version="12"></blockquote>
  <script async src="https://www.instagram.com/embed.js"></script>
<?php }?>
```
