<?php
namespace blog\views;
require_once 'GlobalLayout.php';
class HomepageView {

    function show() : void {
        ob_start();?>
        <main>
            <h3>Ceci est la page d'accueil</h3>
        </main>
<?php
        (new GlobalLayout('Accueil', ob_get_clean()))->show();
    }
}
?>