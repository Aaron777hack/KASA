<?php
/**
 * Pied de page commun du site KASA
 */
?>

    <!-- CALL TO ACTION -->
    <div class="ltn__call-to-action-area call-to-action-6 before-bg-bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="call-to-action-inner call-to-action-inner-6 ltn__secondary-bg position-relative text-center---">
                        <div class="coll-to-info text-color-white">
                            <h1>Vous recherchez une maison de rêve ?</h1>
                            <p>Nous pouvons vous aider</p>
                        </div>
                        <div class="btn-wrapper">
                            <a class="btn btn-effect-3 btn-white" href="<?= SITE_URL ?>/contact.php">Explorer <i class="icon-next"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <footer class="ltn__footer-area">
        <div class="footer-top-area section-bg-2 plr--5">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xl-3 col-md-6 col-sm-6 col-12">
                        <div class="footer-widget footer-about-widget">
                            <div class="footer-logo">
                                <div class="site-logo">
                                    <img src="<?= SITE_URL ?>/img/logo-3.png" height="80" alt="KASA Logo">
                                </div>
                            </div>
                            <p>Le leader de l'immobilier<br>en Côte d'Ivoire</p>
                            <div class="footer-address">
                                <ul>
                                    <li>
                                        <div class="footer-address-icon"><i class="icon-placeholder"></i></div>
                                        <div class="footer-address-info"><p><?= e(SITE_ADDRESS) ?></p></div>
                                    </li>
                                    <li>
                                        <div class="footer-address-icon"><i class="icon-call"></i></div>
                                        <div class="footer-address-info"><p><a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a></p></div>
                                    </li>
                                    <li>
                                        <div class="footer-address-icon"><i class="icon-mail"></i></div>
                                        <div class="footer-address-info"><p><a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a></p></div>
                                    </li>
                                </ul>
                            </div>
                            <div class="ltn__social-media mt-20">
                                <ul>
                                    <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                                    <li><a href="#" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-2 col-md-6 col-sm-6 col-12">
                        <div class="footer-widget footer-menu-widget clearfix">
                            <h4 class="footer-title">Entreprise</h4>
                            <div class="footer-menu">
                                <ul>
                                    <li><a href="<?= SITE_URL ?>/about.php">À propos</a></li>
                                    <li><a href="<?= SITE_URL ?>/shop.php">Propriétés</a></li>
                                    <li><a href="<?= SITE_URL ?>/blog.php">Actualités</a></li>
                                    <li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-sm-12 col-12">
                        <div class="footer-widget footer-newsletter-widget">
                            <h4 class="footer-title">Newsletter</h4>
                            <p>Abonnez-vous et recevez nos dernières offres immobilières.</p>
                            <div class="footer-newsletter">
                                <form id="newsletter-form" action="<?= SITE_URL ?>/api/newsletter.php" method="post">
                                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                                    <input type="email" name="email" placeholder="Votre email*" required>
                                    <div class="btn-wrapper">
                                        <button class="theme-btn-1 btn" type="submit"><i class="fas fa-location-arrow"></i></button>
                                    </div>
                                </form>
                                <p id="newsletter-message" class="mt-10"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="ltn__copyright-area ltn__copyright-2 section-bg-7 plr--5">
            <div class="container-fluid ltn__border-top-2">
                <div class="row">
                    <div class="col-md-6 col-12">
                        <div class="ltn__copyright-design clearfix">
                            <p>© <?= date('Y') ?> <?= e(SITE_NAME) ?> - Tous droits réservés</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-12 align-self-center">
                        <div class="ltn__copyright-menu text-end">
                            <ul>
                                <li><a href="#">Mentions légales</a></li>
                                <li><a href="#">Politique de confidentialité</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

</div><!-- body-wrapper end -->

<!-- Scripts -->
<script src="<?= SITE_URL ?>/js/plugins.js"></script>
<script src="<?= SITE_URL ?>/js/main.js"></script>

<!-- Newsletter AJAX -->
<script>
$('#newsletter-form').submit(function(e) {
    e.preventDefault();
    var $form = $(this);
    var $msg  = $('#newsletter-message');
    $.post($form.attr('action'), $form.serialize(), function(res) {
        $msg.text(res.message).css('color', res.success ? '#28a745' : '#dc3545');
        if (res.success) $form[0].reset();
    }, 'json').fail(function() {
        $msg.text('Une erreur est survenue.').css('color', '#dc3545');
    });
});
</script>
</body>
</html>
