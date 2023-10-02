<!-- SCRIPT LOGIN PHP RAPHAEL TIPHONET -->
<?php
session_start();
require './bootstrap.php';

$message = "";

if (isset($_POST['login']) && isset($_POST['password'])) {
    if(!empty($_POST['login']) && !empty($_POST['password'])){
        $login = $_POST['login'];
        $password = md5($_POST['password']);
        $sql = "SELECT * FROM  apiculteur WHERE mail = :login AND mot_passe = :password";
        $stmt = $dbh->prepare($sql);
        $stmt->execute([
            'login' => $login,
            'password' => $password
        ]);
        $apiculteur = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($apiculteur) {
            unset($apiculteur['mot_passe']);
            $_SESSION['apiculteur'] = $apiculteur;
            header('Location: ./index.php');
            exit();
        } else {
            $message = "Identifiant ou mot de passe incorrect";
        }
    } else {
        $message = "Veuillez remplir tous les champs";
    }
}

echo head('Connexion');
?>
<style>
    .error-message {
        color: red;
        font-size: 1.2rem;
        text-align: center;
    }
</style>
<body class="scroll-xb">
    <div class="background">
        <img src="./assets/svg/back_1.svg">
        <img src="./assets/svg/back_2.svg">
    </div>
    <div class="login">
        <h1>Connexion</h1>
        <div class="form_connect">
            <img src="./assets/svg/logo_appli.svg" alt="bee"></img>
            <form method="post" class="connect">
                <?php if (!empty($message)): ?>
                    <p class="error-message"><?php echo $message; ?></p>
                <?php endif; ?>
                <div class="input-icon">
                    <i class="icon"><img src="./assets/svg/mail.svg"></img></i>
                    <input type="email" name="login" placeholder="Adresse mail" required="required" />
                </div>
                <div class="input-icon">
                    <i class="icon"><img src="./assets/svg/lock.svg"></img></i>
                    <input type="password" name="password" placeholder="Mot de passe" required="required" />
                </div>
                <a href="forgot.php">Mot de passe oublié ?</a>
                <button type="submit" class="btn btn-primary btn-block btn-large">Se connecter</button>
            </form>
        </div>
    </div>
</body>
</html>