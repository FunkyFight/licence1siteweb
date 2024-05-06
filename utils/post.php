<?php
/**
 * Classe Post
 */
class Post {
    private $title;
    private $description;
    private $tags;
    private $files;
    private $images;
    private $handle;

    /**
     * Constructeur de la classe Post
     * @param string $title Titre du post
     * @param string $description Description du post
     * @param array $tags Tags du post
     * @param array $images Images du post
     * @param int $nsfw Contenu sensible
     * @param string $handle Handle de l'utilisateur
     * @param int $postID ID du post
     * @return void
     */
    public function __construct($title, $description, $tags, $images, $nsfw, $handle, $postID) {
        $this->title = $title;
        $this->description = $description;
        $this->tags = $tags;
        $this->images = $images;
        $this->handle = $handle;

        $this->postID = $postID;
        $this->nsfw = $nsfw;
        
        $this->dislikes = 0;
        $this->likes = 0;
    }

    /**
     * getTitle() - Récupère le titre du post
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * getPostID() - Récupère l'ID du post
     * @return int
     */
    public function getPostID() {
        return $this->postID;
    }

    /**
     * getDescription() - Récupère la description du post
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * getTags() - Récupère les tags du post
     * @return array
     */
    public function getTags() {
        return $this->tags;
    }

    /**
     * getFiles() - Récupère les fichiers du post
     * @return array
     */
    public function getFiles() {
        return $this->files;
    }

    /**
     * getImages() - Récupère les images du post
     * @return array
     */
    public function getImages() {
        return $this->images;
    }

    /**
     * getHandle() - Récupère le handle de l'utilisateur
     * @return string
     */
    public function getHandle() {
        return $this->handle;
    }

    /**
     * getLikes() - Récupère le nombre de likes
     * @return int
     */
    public function getNSFW() {
        return ($this->nsfw == null) ? 0 : $this->nsfw;
    }

    /**
     * displayPostMinimized() - Affiche le post de manière minimisée
     * @return void
     */
    public function displayPostMinimized() {
        $info = getInfo($this->handle);
        if($info['profilePicture'] === null) {
            $pfp = "public/default.png";
        } else {
            $pfp = "data:image/jpeg;base64," . base64_encode($info['profilePicture']);
        }

        echo '<div class="post">';
        echo '<h2 class="post-title">' . $this->title . '</h2>';
        echo '<div class="post-header">';
        echo '<img src="' . $pfp . '" alt="User Profile Picture" class="user-profile-picture">';
        echo '</div>';
        echo '<h3 class="username">' . $info['username'] . '</h3>';
        echo '<p class="post-content">' . $this->description . '</p>';

        if(isset($this->images[0])) {
            echo '<img src="data:image/jpeg;base64,' . base64_encode($this->images[0]) . '" alt="Post Image" class="post-image">';
        }
        
        echo "<br><a href='view.php?id=$this->postID' class='show-post-button'>View more</a>";
        echo '<div class="post-footer">';
        echo '<p class="likes">' . $this->likes . ' <a href="?action=like&postID='.$this->getPostID().'"><img src="./public/like.svg" style="width:50px;vertical-align:middle;"></a> · ' . $this->dislikes . ' <a href="?action=dislike&postID='.$this->getPostID().'"><img src="./public/dislike.svg" style="width:40px;vertical-align:middle;"></a></p>';
        echo '</div>';
        echo '</div>';
    }

}
?>