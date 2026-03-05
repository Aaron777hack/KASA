<?php
/**
 * Page contact - KASA Immobilier
 */
define('KASA_LOADED', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

session_start();

$pageTitle = 'Contact';
$pageDesc  = 'Contactez KASA Immobilier - Nous sommes à votre disposition';

include __DIR__ . '/includes/header.php';
?>

    <!-- BREADCRUMB -->
    <div class="ltn__breadcrumb-area text-left bg-overlay-white-30 bg-image" data-bs-bg="<?= SITE_URL ?>/img/bg/14.jpg">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner">
                        <h1 class="page-title">Nous Contacter</h1>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= SITE_URL ?>/index.php"><span class="ltn__secondary-color"><i class="fas fa-home"></i></span> Accueil</a></li>
                                <li>Contact</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- COORDONNÉES -->
    <div class="ltn__contact-address-area mb-90">
        <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                        <div class="ltn__contact-address-icon">
                            <img src="<?= SITE_URL ?>/img/icons/10.png" alt="Email">
                        </div>
                        <h3>Email</h3>
                        <p><a href="mailto:<?= e(SITE_EMAIL) ?>"><?= e(SITE_EMAIL) ?></a></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                        <div class="ltn__contact-address-icon">
                            <img src="<?= SITE_URL ?>/img/icons/11.png" alt="Téléphone">
                        </div>
                        <h3>Téléphone</h3>
                        <p><a href="tel:<?= e(SITE_PHONE) ?>"><?= e(SITE_PHONE) ?></a></p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="ltn__contact-address-item ltn__contact-address-item-3 box-shadow">
                        <div class="ltn__contact-address-icon">
                            <img src="<?= SITE_URL ?>/img/icons/12.png" alt="Adresse">
                        </div>
                        <h3>Localisation</h3>
                        <p><?= e(SITE_ADDRESS) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FORMULAIRE DE CONTACT -->
    <div class="ltn__contact-message-area mb-120 mb--100">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__form-box contact-form-box box-shadow white-bg">
                        <h4 class="title-2">Obtenir un devis</h4>
                        <form id="contact-form" action="<?= SITE_URL ?>/api/contact.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-item input-item-name ltn__custom-icon">
                                        <input type="text" name="name" placeholder="Entrez votre nom *" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-item input-item-email ltn__custom-icon">
                                        <input type="email" name="email" placeholder="Entrez votre adresse email *" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-item">
                                        <select name="service" class="nice-select">
                                            <option value="">Sélectionner le service</option>
                                            <option value="Achat terrain">Achat terrain</option>
                                            <option value="Achat bien">Achat bien</option>
                                            <option value="Location">Location</option>
                                            <option value="Gestion immobilière">Gestion immobilière</option>
                                            <option value="Permis de construire">Permis de construire</option>
                                            <option value="Protection de terrain">Protection de terrain</option>
                                            <option value="Autre">Autre</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-item input-item-phone ltn__custom-icon">
                                        <input type="text" name="phone" placeholder="Numéro de téléphone">
                                    </div>
                                </div>
                            </div>
                            <div class="input-item input-item-textarea ltn__custom-icon">
                                <textarea name="message" placeholder="Votre message *" required></textarea>
                            </div>
                            <div class="btn-wrapper mt-0">
                                <button class="btn theme-btn-1 btn-effect-1 text-uppercase" type="submit">Envoyer le message</button>
                            </div>
                            <p class="form-messege mb-0 mt-20"></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CARTE -->
    <div class="google-map mb-120">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7944.65000874333!2d-3.986036188735467!3d5.3673009601901605!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xfc1eca461130d95%3A0x2869ee60584a7508!2sAttoban%2C%20Abidjan!5e0!3m2!1sfr!2sci!4v1771586693728!5m2!1sfr!2sci"
            width="100%" height="100%" frameborder="0" allowfullscreen="" loading="lazy"></iframe>
    </div>

<script>
// Formulaire de contact AJAX
$('#contact-form').submit(function(e) {
    e.preventDefault();
    var $form = $(this);
    var $msg  = $form.find('.form-messege');
    var $btn  = $form.find('[type=submit]');
    $btn.prop('disabled', true).text('Envoi en cours...');
    $.post($form.attr('action'), $form.serialize(), function(res) {
        $msg.text(res.message).css('color', res.success ? '#28a745' : '#dc3545');
        if (res.success) {
            $form.find('input:not([type=hidden]), select, textarea').val('');
        }
    }, 'json').fail(function() {
        $msg.text('Une erreur est survenue. Veuillez réessayer.').css('color', '#dc3545');
    }).always(function() {
        $btn.prop('disabled', false).text('Envoyer le message');
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
