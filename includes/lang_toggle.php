<?php

declare(strict_types=1);

/** Swahili-only UI indicator — pairs with assets/js/mgrid-i18n.js (name="mgridLang"). */
$vanilla = !empty($mgrid_public_vanilla);

if ($vanilla) {
    ?>
<div class="mgrid-lang-vanilla mgrid-lang-toggle" role="group" aria-label="Lugha">
  <input type="radio" name="mgridLang" id="mgridLangEn" value="en" autocomplete="off" />
  <label for="mgridLangEn"><span class="mgrid-lang-label-en">EN</span></label>
  <input type="radio" name="mgridLang" id="mgridLangSw" value="sw" autocomplete="off" checked />
  <label for="mgridLangSw"><span class="mgrid-lang-label-sw">SW</span></label>
</div>
    <?php
    return;
}
?>
<div class="btn-group btn-group-sm mgrid-lang-toggle" role="group" aria-label="Lugha">
  <input type="radio" class="btn-check" name="mgridLang" id="mgridLangEn" value="en" autocomplete="off" />
  <label class="btn btn-outline-secondary px-2" for="mgridLangEn"><span class="mgrid-lang-label-en">EN</span></label>
  <input type="radio" class="btn-check" name="mgridLang" id="mgridLangSw" value="sw" autocomplete="off" checked />
  <label class="btn btn-outline-secondary px-2" for="mgridLangSw"><span class="mgrid-lang-label-sw">SW</span></label>
</div>
