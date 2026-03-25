<?php
class VueFormulaire
{
    public function __construct()
    {
        // Constructeur de la classe Formulaire
    }
 
    public function __toString()
    {
        return '
        <form action="index.php?PDOTEST_traitement" method="post" class="inscription-form">
 
            <label for="usernameField">Pseudo :</label>
            <input type="text" id="usernameField" name="username"
                   placeholder="Choisissez un pseudo unique" required>
            <br/>
 
            <label for="displayNameField">Nom affiché :</label>
            <input type="text" id="displayNameField" name="display_name"
                   placeholder="Votre prénom ou nom complet" required>
            <br/>
 
            <label for="emailField">Email :</label>
            <input type="email" id="emailField" name="email"
                   placeholder="Votre adresse email" required>
            <br/>
 
            <label for="passwordField">Mot de passe :</label>
            <input type="password" id="passwordField" name="password"
                   placeholder="Votre mot de passe" required>
            <br/>
 
            <label for="birthDateField">Date de naissance :</label>
            <input type="date" id="birthDateField" name="birth_date" required>
            <br/>
 
            <label>
                <input type="checkbox" name="cgu" required>
                J\'accepte les <a href="index.php?mentions-legales">conditions d\'utilisation</a>
            </label>
            <br/>
 
            <input type="submit" value="S\'inscrire" name="ok">
        </form>
        ';
    }
}
