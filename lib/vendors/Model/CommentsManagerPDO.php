<?php
namespace Model;

use \Entity\Comment;

class CommentsManagerPDO extends CommentsManager
{
    protected $path = __DIR__.'/../../../tmp/cache/datas/';

  protected function add(Comment $comment)
  {
    $q = $this->dao->prepare('INSERT INTO comments SET news = :news, auteur = :auteur, contenu = :contenu, date = NOW()');
    
    $q->bindValue(':news', $comment->news(), \PDO::PARAM_INT);
    $q->bindValue(':auteur', $comment->auteur());
    $q->bindValue(':contenu', $comment->contenu());
    
    $q->execute();
    
    $comment->setId($this->dao->lastInsertId());

      $my_file = $this->path . "cache_comments_".$comment->news().".txt";
      if(is_file($my_file)){
        unlink($my_file);
      }
  }

  public function delete($id)
  {

      $comment = $this->get($id);
      if($comment){
          $my_file = $this->path . "cache_comments_".$comment->news().".txt";
          if(is_file($my_file)){
              unlink($my_file);
          }
      }


    $this->dao->exec('DELETE FROM comments WHERE id = '.(int) $id);

  }

  public function deleteFromNews($news)
  {
    $this->dao->exec('DELETE FROM comments WHERE news = '.(int) $news);

      $my_file = $this->path . "cache_comments_".$news.".txt";
      if(is_file($my_file)){
          unlink($my_file);
      }
  }
  
  public function getListOf($news)
  {

      if (!ctype_digit($news))
      {
          throw new \InvalidArgumentException('L\'identifiant de la news passé doit être un nombre entier valide');
      }

      // First test if file with data is present
      $isStillCache = false;
      $my_file = $this->path . "cache_comments_".$news.".txt";
      if(is_file($my_file)){
          $contentToGet = "";
          $lines = file($my_file);
          foreach($lines as $n => $line){
              if($n == 0){
                  // Get date
                  $dateFile = new \DateTime($line);
                  $dateToday = new \DateTime("NOW");
                  if($dateToday > $dateFile){
                      // Delete file and recreate it
                      break;
                  }else{
                      // file ok, keep cache
                      $isStillCache = true;
                  }
              }else{
                  // Get content
                  $contentToGet .= $line;
              }
          }
          if(!$isStillCache){
              unlink($my_file);
          }else{
              $comments = unserialize($contentToGet);
          }
      }

      // If no cache, do request
      if(!$isStillCache) {
          $q = $this->dao->prepare('SELECT id, news, auteur, contenu, date FROM comments WHERE news = :news');
          $q->bindValue(':news', $news, \PDO::PARAM_INT);
          $q->execute();

          $q->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\Comment');

          $comments = $q->fetchAll();

          foreach ($comments as $comment) {
              $comment->setDate(new \DateTime($comment->date()));
          }
      }

      // If no file, create it
      if(!is_file($my_file) && !$isStillCache){
          $dateExpire = new \DateTime("NOW");
          $dateExpire->modify('+1 day');
          $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
          fwrite($handle, $dateExpire->format("Y-m-d"));
          fwrite($handle, "\n");
          fwrite($handle, serialize($comments));
          fclose($handle);
      }
    
    return $comments;
  }

  protected function modify(Comment $comment)
  {
      $comment = $this->get($comment->id());
      if($comment){
          $my_file = $this->path . "cache_comments_".$comment->news().".txt";
          if(is_file($my_file)){
              unlink($my_file);
          }
      }

    $q = $this->dao->prepare('UPDATE comments SET auteur = :auteur, contenu = :contenu WHERE id = :id');
    
    $q->bindValue(':auteur', $comment->auteur());
    $q->bindValue(':contenu', $comment->contenu());
    $q->bindValue(':id', $comment->id(), \PDO::PARAM_INT);
    
    $q->execute();


  }
  
  public function get($id)
  {
    $q = $this->dao->prepare('SELECT id, news, auteur, contenu FROM comments WHERE id = :id');
    $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
    $q->execute();
    
    $q->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\Comment');
    
    return $q->fetch();
  }
}