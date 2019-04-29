# instagram-class
An instagram class to get the last post made by an user.


## usage
```
<?php
$insta = Instagram::getLast('lucaslealdev');
if($insta){?>
  <blockquote class="instagram-media" data-instgrm-permalink="https://www.instagram.com/p/<?= $insta?>/?utm_source=ig_embed&amp;utm_medium=loading" data-instgrm-version="12" datetime="2019-04-09T14:50:31+00:00">Apr 9, 2019 at 7:50am PDT</time></p></div></blockquote>
  <script async src="https://www.instagram.com/embed.js"></script>
<?php }?>
```
