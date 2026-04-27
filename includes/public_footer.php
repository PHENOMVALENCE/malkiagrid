<?php

declare(strict_types=1);

$vanilla = !empty($mgrid_public_vanilla);

if ($vanilla) {
    ?>
<footer class="mgrid-footer mgrid-footer--public">
  <div class="mgrid-container">
    <div class="mgrid-footer-grid">
      <div>
        <div class="mgrid-footer-brand mgrid-mb-2">M GRID</div>
        <p class="mgrid-text-small mgrid-mb-0 mgrid-opacity-80 mgrid-footer-lead" data-i18n-html="footer.brand_lead">
          Jukwaa la uanachama kwa wanawake wajasiriamali Tanzania — lenye <strong>M-ID</strong> iliyothibitishwa, <strong>M-Profile</strong>, na upatikanaji wa fursa kwa mpangilio.
        </p>
      </div>
      <div>
        <div class="mgrid-footer-heading" data-i18n="footer.partners_heading">Washirika wa programu</div>
        <p class="mgrid-text-small mgrid-opacity-80 mgrid-mb-0" data-i18n="footer.partners_text">Malkia wa Nguvu · Clouds Media Group — usimamizi wa programu, ufikivu wa vyombo vya habari, na uratibu wa kitaasisi.</p>
      </div>
      <div>
        <div class="mgrid-footer-heading" data-i18n="footer.resources_heading">Rasilimali</div>
        <ul class="mgrid-list-plain mgrid-text-small">
          <li><a href="<?= e(url('register.php')) ?>" data-i18n="footer.link_register">Jisajili kwa M-ID</a></li>
          <li><a href="<?= e(url('login.php')) ?>" data-i18n="footer.link_signin">Ingia kwa mwanachama</a></li>
          <li><a href="#faq" data-i18n="footer.link_faq">Maswali ya kawaida</a></li>
        </ul>
      </div>
    </div>
    <hr class="mgrid-footer-hr">
    <p class="mgrid-text-small mgrid-text-center mgrid-mb-0 mgrid-opacity-75">&copy; <?= (int) date('Y') ?> <span data-i18n="footer.copyright">Malkia Grid. Haki zote zimehifadhiwa.</span></p>
  </div>
</footer>
    <?php
    return;
}
?>
<footer class="mgrid-footer pt-5 pb-4 mt-5">
  <div class="container">
    <div class="row g-4">
      <div class="col-md-4">
        <div class="mgrid-footer-brand mb-2">M GRID</div>
        <p class="small mb-0 opacity-80 mgrid-footer-lead" data-i18n-html="footer.brand_lead">
          Jukwaa la uanachama kwa wanawake wajasiriamali Tanzania — lenye <strong>M-ID</strong> iliyothibitishwa, <strong>M-Profile</strong>, na upatikanaji wa fursa kwa mpangilio.
        </p>
      </div>
      <div class="col-md-4">
        <div class="mgrid-footer-heading mb-2" data-i18n="footer.partners_heading">Washirika wa programu</div>
        <p class="small opacity-80 mb-0" data-i18n="footer.partners_text">Malkia wa Nguvu · Clouds Media Group — usimamizi wa programu, ufikivu wa vyombo vya habari, na uratibu wa kitaasisi.</p>
      </div>
      <div class="col-md-4">
        <div class="mgrid-footer-heading mb-2" data-i18n="footer.resources_heading">Rasilimali</div>
        <ul class="list-unstyled small">
          <li><a href="<?= e(url('register.php')) ?>" data-i18n="footer.link_register">Jisajili kwa M-ID</a></li>
          <li><a href="<?= e(url('login.php')) ?>" data-i18n="footer.link_signin">Ingia kwa mwanachama</a></li>
          <li><a href="#faq" data-i18n="footer.link_faq">Maswali ya kawaida</a></li>
        </ul>
      </div>
    </div>
    <hr class="border-secondary opacity-25 my-4">
    <p class="small text-center mb-0 opacity-75">&copy; <?= (int) date('Y') ?> <span data-i18n="footer.copyright">Malkia Grid. Haki zote zimehifadhiwa.</span></p>
  </div>
</footer>
