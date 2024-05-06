<?php
    // Connexion à la base de données
    $host = 'localhost';
    $db   = 'img2share';
    $user = 'root';
    $pass = '';

    $dsn = "mysql:host=$host;dbname=$db";

    try {
        $GLOBALS['conn'] = new PDO($dsn, $user, $pass);
    } catch(PDOException $e) {
        die("ERROR: Could not connect. " . $e->getMessage());
    }

    /**
     * handleAlreadyExists() - Vérifie si le handle existe déjà
     * @param string $handle - Le handle à vérifier
     */
    function handleAlreadyExists($handle) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM user WHERE handle = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->execute();

        $result = $q->fetch(PDO::FETCH_ASSOC);
        
        return $result['nb'] > 0;
    }

    /**
     * registerNewUser() - Enregistre un nouvel utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $username - Le nom d'utilisateur
     * @param string $hashed_password - Le mot de passe hashé
     * @param string $interests - Les centres d'intérêts
     */
    function registerNewUser($handle, $username, $hashed_password, $interests) {
        $q = $GLOBALS['conn']->prepare("INSERT INTO user VALUES (?, ?, ?, ?, '', 'USER', null)");

        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->bindParam(2, $username, PDO::PARAM_STR);
        $q->bindParam(3, $hashed_password, PDO::PARAM_STR);
        $q->bindParam(4, $interests, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * verifyPassword() - Vérifie si le mot de passe est correct
     * @param string $handle - Le handle de l'utilisateur
     * @param string $password - Le mot de passe à vérifier
     */
    function verifyPassword($handle, $password) {
        $q = $GLOBALS['conn']->prepare("SELECT * FROM user WHERE handle = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->execute();

        $result = $q->fetch(PDO::FETCH_ASSOC);

        $storedPsd = $result['password'];

        return password_verify($password, $storedPsd);
    }

    /**
     * getUsernameFromHandle() - Récupère le nom d'utilisateur à partir du handle
     * @param string $handle - Le handle de l'utilisateur
     */
    function getUsernameFromHandle($handle) {
        $q = $GLOBALS['conn']->prepare("SELECT username FROM user WHERE handle = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->execute();

        $result = $q->fetch(PDO::FETCH_ASSOC);

        return $result['username'];
    }

    /**
     * getProfilePicture() - Récupère la photo de profil de l'utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $image - L'image (binaire) de la photo de profil
     */
    function changePfp($handle, $image) {
        $q = $GLOBALS['conn']->prepare("UPDATE user SET profilePicture = ? WHERE handle = ?");
        $q->bindParam(1, $image, PDO::PARAM_LOB);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * getInfo() - Récupère les informations de l'utilisateur
     * @param string $handle - Le handle de l'utilisateur
     */
    function getInfo($handle) {
        $q = $GLOBALS['conn']->prepare("SELECT * FROM user WHERE handle = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * getFollowersCount() - Récupère le nombre de followers
     * @param string $handle - Le handle de l'utilisateur
     */
    function getFollowersCount($handle) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM follows WHERE followedID = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['nb'];
    }

    /**
     * isFollowing() - Vérifie si l'utilisateur suit un autre utilisateur
     * @param string $userHandle - Le handle de l'utilisateur
     * @param string $targetProfileHandle - Le handle de l'utilisateur cible
     */
    function isFollowing($userHandle, $targetProfileHandle) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM follows WHERE followerID = ? AND followedID = ?");
        $q->bindParam(1, $userHandle, PDO::PARAM_STR);
        $q->bindParam(2, $targetProfileHandle, PDO::PARAM_STR);
        $q->execute();


        return $q->fetch(PDO::FETCH_ASSOC)['nb'] > 0;
    }

    /**
     * likePost() - Like un post
     * @param string $handle - Le handle de l'utilisateur
     * @param int $postID - L'ID du post
     */
    function likePost($handle, $postID) {
        if(getPostAuthor($postID) === $handle) {
            return;
        }

        if(isLiked($handle, $postID)) {
            return;
        }

        if(isDisliked($handle, $postID)) {
            $q = $GLOBALS['conn']->prepare("DELETE FROM appreciation WHERE postID = ? AND userID = ? AND action = false");
            $q->bindParam(1, $postID, PDO::PARAM_INT);
            $q->bindParam(2, $handle, PDO::PARAM_STR);
            $q->execute();
        }

        $q = $GLOBALS['conn']->prepare("INSERT INTO appreciation (postID, userID, `action`) VALUES (?, ?, true)");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * isLiked() - Vérifie si l'utilisateur a liké un post
     * @param string $handle - Le handle de l'utilisateur
     * @param int $postID - L'ID du post à vérifier
     */
    function isLiked($handle, $postID) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM appreciation WHERE postID = ? AND userID = ? AND action = true");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['nb'] > 0;
    }

    /**
     * dislikePost() - Dislike un post
     * @param string $handle - Le handle de l'utilisateur
     * @param int $postID - L'ID du post
     */
    function dislikePost($handle, $postID) {
        if(getPostAuthor($postID) === $handle) {
            return;
        }

        if(isLiked($handle, $postID)) {
            $q = $GLOBALS['conn']->prepare("DELETE FROM appreciation WHERE postID = ? AND userID = ? AND action = true");
            $q->bindParam(1, $postID, PDO::PARAM_INT);
            $q->bindParam(2, $handle, PDO::PARAM_STR);
            $q->execute();
        }

        if(isDisliked($handle, $postID)) {
            return;
        }

        $q = $GLOBALS['conn']->prepare("INSERT INTO appreciation (postID, userID, action) VALUES (?, ?, false)");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * isDisliked() - Vérifie si l'utilisateur a disliké un post
     * @param string $handle - Le handle de l'utilisateur
     * @param int $postID - L'ID du post à vérifier
     */
    function isDisliked($handle, $postID) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM appreciation WHERE postID = ? AND userID = ? AND action = false");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['nb'] > 0;
    }

    /**
     * getPostAuthor() - Récupère l'auteur d'un post
     * @param int $postID - L'ID du post
     */
    function getPostAuthor($postID) {
        $q = $GLOBALS['conn']->prepare("SELECT authorID FROM posts WHERE postID = ?");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['authorID'];
    }

    /**
     * getLikes() - Récupère le nombre de likes d'un post
     * @param int $postID - L'ID du post
     */
    function getLikes($postID) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM appreciation WHERE postID = ? AND action = true");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['nb'];
    }

    /**
     * getDislikes() - Récupère le nombre de dislikes d'un post
     * @param int $postID - L'ID du post
     */
    function getDislikes($postID) {
        $q = $GLOBALS['conn']->prepare("SELECT COUNT(*) AS nb FROM appreciation WHERE postID = ? AND action = false");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->execute();

        return $q->fetch(PDO::FETCH_ASSOC)['nb'];
    }

    /**
     * uploadPost() - Enregistre un post dans la base de données
     * @param Post $post - Le post à enregistrer
     */
    function uploadPost($post) {
        // Insert post data into posts table
        $q = $GLOBALS['conn']->prepare("INSERT INTO posts (authorID, title, description, tags, creationDate, isNSFW) VALUES (?, ?, ?, ?, NOW(), ?)");

        // On utilise tous les getters de la classe Post pour récupérer les données
        $handle = $post->getHandle();
        $title = $post->getTitle();
        $description = $post->getDescription();
        $tags = implode(',', $post->getTags());
        $nsfw = $post->getNSFW();
        $files = $post->getFiles();
        $images = $post->getImages();
        
        // On lie les paramètres
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->bindParam(2, $title, PDO::PARAM_STR);
        $q->bindParam(3, $description, PDO::PARAM_STR);
        $q->bindParam(4, $tags, PDO::PARAM_STR);
        $q->bindParam(5, $nsfw, PDO::PARAM_INT);
        $q->execute();

        // On récupère l'ID du post qu'on vient d'insérer
        $postID = $GLOBALS['conn']->lastInsertId();

        // On insère les images dans la table postscontent
        foreach ($images as $image) {
            $q = $GLOBALS['conn']->prepare("INSERT INTO postscontent (postID, type, image) VALUES (?, 'image', ?)");
            $q->bindParam(1, $postID, PDO::PARAM_INT);
            $q->bindParam(2, $image, PDO::PARAM_LOB);
            $q->execute();
        }

        return $postID;
    }

    /**
     * getNewlyPosts() - Récupère les derniers posts
     * @param int $from - L'ID du post à partir duquel on veut récupérer les posts
     * @param int $to - L'ID du post jusqu'auquel on veut récupérer les posts
     * @return array
     */
    function getNewlyPosts($from = null, $to = null) {
        // Si from est null, on récupère le dernier post
        if ($from === null) {
            $q = $GLOBALS['conn']->prepare("SELECT MAX(postID) AS maxID FROM posts");
            $q->execute();
            $result = $q->fetch(PDO::FETCH_ASSOC);
            $from = $result['maxID'];
        }

        // Si to est null, on récupère les 25 posts précédents
        if ($to === null) {
            $to = $from - 25;
        }

        // On récupère les posts
        $q = $GLOBALS['conn']->prepare("SELECT * FROM posts WHERE postID <= ? AND postID > ? ORDER BY postID DESC");
        $q->bindParam(1, $from, PDO::PARAM_INT);
        $q->bindParam(2, $to, PDO::PARAM_INT);
        $q->execute();

        $posts = array();
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = convertRowToPost($row); // On convertit chaque ligne en objet Post
        }

        return $posts;
    }

    /**
     * convertRowToPost() - Convertit une ligne de la table posts en objet Post
     * @param array $row - La ligne à convertir
     * @return Post
     */
    function convertRowToPost($row) {
        // On récupère les données de la ligne
        $postID = $row['postID'];
        $authorHandle = $row['authorID'];
        $title = $row['title'];
        $description = $row['description'];
        $tags = explode(',', $row['tags']);
        $creationDate = $row['creationDate'];

        // On récupère les images du post
        $images = array();

        // On récupère les images du post
        $q2 = $GLOBALS['conn']->prepare("SELECT * FROM postscontent WHERE postID = ?");
        $q2->bindParam(1, $postID, PDO::PARAM_INT);
        $q2->execute();

        // On ajoute chaque image à un tableau
        while ($row2 = $q2->fetch(PDO::FETCH_ASSOC)) {
            $images[] = $row2['image'];
        }

        
        $isNSFW = $row['isNSFW'];

        // On crée un objet Post
        $post = new Post($title, $description, $tags, $images, $isNSFW, $authorHandle, $postID);
        $post->likes = getLikes($postID); // On récupère le nombre de likes
        $post->dislikes = getDislikes($postID); // On récupère le nombre de dislikes
        return $post;
    }

    /**
     * follow() - Suit un utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $targetHandle - Le handle de l'utilisateur à suivre
     * @return void
     */
    function follow($handle, $targetHandle) {
        // Si l'utilisateur suit déjà la cible, on ne fait rien
        if(isFollowing($handle, $targetHandle)) {
            return;
        }

        $q = $GLOBALS['conn']->prepare("INSERT INTO follows (followerID, followedID, date) VALUES (?, ?, NOW())");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->bindParam(2, $targetHandle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * unfollow() - Désabonne un utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $targetHandle - Le handle de l'utilisateur à désabonner
     * @return void
     */
    function unfollow($handle, $targetHandle) {
        $q = $GLOBALS['conn']->prepare("DELETE FROM follows WHERE followerID = ? AND followedID = ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->bindParam(2, $targetHandle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * getFollowedPosts() - Récupère les posts des utilisateurs suivis
     * @param int $count - Le nombre de posts à récupérer
     * @param string $handle - Le handle de l'utilisateur
     * @return array
     */
    function getPostsFromUser($count, $handle) {
        $q = $GLOBALS['conn']->prepare("SELECT * FROM posts WHERE authorID = ? ORDER BY postID DESC LIMIT ?");
        $q->bindParam(1, $handle, PDO::PARAM_STR);
        $q->bindParam(2, $count, PDO::PARAM_INT);
        $q->execute();

        $posts = array();
        while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = convertRowToPost($row);
        }

        return $posts;
    }

    /**
     * getFollowedPosts() - Récupère les posts des utilisateurs suivis
     * @param int $count - Le nombre de posts à récupérer
     * @param string $handle - Le handle de l'utilisateur
     * @return array
     */
    function getPostByID($postID) {
        $q = $GLOBALS['conn']->prepare("SELECT * FROM posts WHERE postID = ?");
        $q->bindParam(1, $postID, PDO::PARAM_INT);
        $q->execute();

        if ($row = $q->fetch(PDO::FETCH_ASSOC)) {
            return convertRowToPost($row);
        }

        return null;
    }

    /**
     * changeBio() - Change la biographie de l'utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $newBio - La nouvelle biographie
     */
    function changeBio($handle, $newBio) {
        $q = $GLOBALS['conn']->prepare("UPDATE user SET biography = ? WHERE handle = ?");
        $q->bindParam(1, $newBio, PDO::PARAM_STR);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();
    }

    /**
     * changeUsername() - Change le nom d'utilisateur
     * @param string $handle - Le handle de l'utilisateur
     * @param string $newUsername - Le nouveau nom d'utilisateur
     * @return void
     */
    function changeUsername($handle, $newUsername) {
        $q = $GLOBALS['conn']->prepare("UPDATE user SET username = ? WHERE handle = ?");
        $q->bindParam(1, $newUsername, PDO::PARAM_STR);
        $q->bindParam(2, $handle, PDO::PARAM_STR);
        $q->execute();
    }
?>

