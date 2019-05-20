<?php
namespace Model;

use \Entity\News;

class NewsManagerPDO extends NewsManager
{
    protected $path = __DIR__.'/../../../tmp/cache/datas/';
    protected $fileName = "cache_news.txt";


  protected function add(News $news)
  {
    $requete = $this->dao->prepare('INSERT INTO news SET auteur = :auteur, titre = :titre, contenu = :contenu, dateAjout = NOW(), dateModif = NOW()');
    
    $requete->bindValue(':titre', $news->titre());
    $requete->bindValue(':auteur', $news->auteur());
    $requete->bindValue(':contenu', $news->contenu());
    
    $requete->execute();

      $my_file = $this->path . $this->fileName;
      if(is_file($my_file)){
          unlink($my_file);
      }
  }

  public function count()
  {
    return $this->dao->query('SELECT COUNT(*) FROM news')->fetchColumn();
  }

  public function delete($id)
  {
        $this->dao->exec('DELETE FROM news WHERE id = '.(int) $id);
        $my_file = $this->path . $this->fileName;
        if(is_file($my_file)){
          unlink($my_file);
        }
  }

    public function getList($debut = -1, $limite = -1)
    {
        // First test if file with data is present
        $isStillCache = false;
        $my_file = $this->path . $this->fileName;
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
                $listeNews = unserialize($contentToGet);
            }
        }

        // If no cache, do request
        if(!$isStillCache){
            $sql = 'SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news ORDER BY id DESC';

            if ($debut != -1 || $limite != -1)
            {
                $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
            }

            $requete = $this->dao->query($sql);
            $requete->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\News');

            $listeNews = $requete->fetchAll();

            foreach ($listeNews as $news)
            {
                $news->setDateAjout(new \DateTime($news->dateAjout()));
                $news->setDateModif(new \DateTime($news->dateModif()));
            }

            $requete->closeCursor();
        }

        // If no file, create it
        if(!is_file($my_file) && !$isStillCache){
            $dateExpire = new \DateTime("NOW");
            $dateExpire->modify('+1 day');
            $handle = fopen($my_file, 'w') or die('Cannot open file:  '.$my_file);
            fwrite($handle, $dateExpire->format("Y-m-d"));
            fwrite($handle, "\n");
            fwrite($handle, serialize($listeNews));
            fclose($handle);
        }

        return $listeNews;
    }
  
  public function getUnique($id)
  {
    $requete = $this->dao->prepare('SELECT id, auteur, titre, contenu, dateAjout, dateModif FROM news WHERE id = :id');
    $requete->bindValue(':id', (int) $id, \PDO::PARAM_INT);
    $requete->execute();
    
    $requete->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, '\Entity\News');
    
    if ($news = $requete->fetch())
    {
      $news->setDateAjout(new \DateTime($news->dateAjout()));
      $news->setDateModif(new \DateTime($news->dateModif()));
      
      return $news;
    }
    
    return null;
  }

  protected function modify(News $news)
  {
    $requete = $this->dao->prepare('UPDATE news SET auteur = :auteur, titre = :titre, contenu = :contenu, dateModif = NOW() WHERE id = :id');
    
    $requete->bindValue(':titre', $news->titre());
    $requete->bindValue(':auteur', $news->auteur());
    $requete->bindValue(':contenu', $news->contenu());
    $requete->bindValue(':id', $news->id(), \PDO::PARAM_INT);
    
    $requete->execute();

      $my_file = $this->path . $this->fileName;
      if(is_file($my_file)){
          unlink($my_file);
      }
  }
}