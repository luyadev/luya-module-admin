<?php
use luya\web\Svg;
use luya\admin\Module;
use luya\helpers\Url;

$spinner = Svg::widget([
    'folder' => "@admin/resources/svg",
    'cssClass' => 'svg-spinner',
    'file' => 'login/spinner.svg'
]);
?>
<?php if ($backgroundImage): ?>
<style type="text/css">
body {
    background-image: url('<?= $backgroundImage; ?>');
    background-size: cover;
    background-position: 50% 50%;
}
</style>
<?php endif; ?>
<div class="login-frame">
    <div class="login-logo">
        <?= Svg::widget([
            'folder' => "@admin/resources/svg",
            'cssClass' => 'login-logo-svg',
            'file' => 'logo/luya_logo.svg'
        ]) ?>
    </div>
    <!-- normal login form -->
    <form class="login-form" method="post" id="loginForm">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <div class="login-inputs">
            <div class="login-form-field form-group">
                <input class="login-input" id="email" name="login[email]" type="email" autocomplete="email" tabindex="1" required />
                <label for="email" class="login-input-label"><?= Module::t('login_mail'); ?></label>
            </div>
            <div class="login-form-field form-group">
                <input class="login-input" id="password" name="login[password]" type="password" autocomplete="current-password" tabindex="2" required />
                <label for="password" class="login-input-label"><?= Module::t('login_password'); ?></label>
            </div>
            <div class="login-status alert alert-danger" id="errorsContainer" style="display: none"></div>
            <div class="login-buttons login-buttons-right">
                <button class="btn btn-primary login-btn" type="submit"  tabindex="3">
                    <span class="login-spinner"><?= $spinner; ?></span><span class="login-btn-label"><?= Module::t('login_btn_login'); ?></span>
                </button>
            </div>
        </div>
    </form>
    <!-- end of normal login form -->
    <!-- secure login form -->
    <form class="login-form hidden" method="post" id="secureForm">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <div class="login-inputs">
            <div class="login-form-field">
                <label class="input-label login-secure-token-label" for="secure_token"><?= Module::t('login_securetoken_info'); ?></label>
                <input class="login-input" id="secure_token" name="secure_token" type="text" tabindex="1" placeholder="<?= Module::t('login_securetoken'); ?>" />
            </div>
            <div class="login-status alert alert-danger" id="errorsSecureContainer" style="display: none"></div>
            <div class="login-buttons">
                <button class="btn btn-secondary login-btn login-btn-50" id="abortToken" type="button"  tabindex="3">
                    <?= Module::t('button_abort'); ?>
                </button>
                <button class="btn btn-primary login-btn login-btn-50" type="submit"  tabindex="2">
                    <span class="login-spinner"><?= $spinner; ?></span> <span class="login-btn-label"><?= Module::t('button_send'); ?></span>
                </button>
            </div>
        </div>
    </form>
    <!-- end of secure login form -->
    <div class="login-success" style="visibility: hidden;" id="success">
        <i class="material-icons login-success-icon">check_circle</i>
    </div>
</div>

<div class="login-info">
    <h1 class="login-title"><span class="on-white"><?= Yii::$app->siteTitle; ?></span></h1>
    <span class="login-info-text on-white"><?php if (Yii::$app->request->isSecureConnection): ?><i alt="<?= Module::t('login_ssl_info');?>" title="<?= Module::t('login_ssl_info');?>" class="material-icons">verified_user</i><?php endif; ?><?= Url::domain(Yii::$app->request->hostInfo); ?></span>
</div>

<div class="login-links">
    <ul>
        <li>
            <a href="https://luya.io" target="_blank" class="login-link on-white">luya.io</a>
        </li>
    </ul>
</div>

<noscript>
    <div class="login-noscript">
        <p><?= Module::t('login_noscript_error');?></p>
    </div>
</noscript>

<!--[if IE]>
    <div class="login-browsehappy"><p><?= Module::t('login_browsehappy');?></p></div>
<![endif]-->
