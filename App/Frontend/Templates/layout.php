<!DOCTYPE html>
<html>
  <head>
    <title>
      <?= isset($title) ? $title : 'Mon super site' ?>
    </title>
    
    <meta charset="utf-8" />
    
    <link rel="stylesheet" href="<?= DEFAULT_URL ?>/css/Envision.css" type="text/css" />
  </head>
  
  <body>
    <div id="wrap">
      <header>
        <h1><a href="<?= DEFAULT_URL ?>/">Mon super site</a></h1>
        <p>Comment ça, il n'y a presque rien ?</p>
      </header>
      
      <nav>
        <ul>
          <li><a href="<?= DEFAULT_URL ?>/">Accueil</a></li>
          <?php if ($user->isAuthenticated()) { ?>
          <li><a href="<?= DEFAULT_URL ?>/admin/">Admin</a></li>
          <li><a href="<?= DEFAULT_URL ?>/admin/news-insert.html">Ajouter une news</a></li>
          <?php } ?>
        </ul>
      </nav>
      
      <div id="content-wrap">
        <section id="main">
          <?php if ($user->hasFlash()) echo '<p style="text-align: center;">', $user->getFlash(), '</p>'; ?>
          
          <?= $content ?>
        </section>
      </div>
    
      <footer></footer>
    </div>
  </body>
</html>