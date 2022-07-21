<?php
use luya\admin\Module;
use luya\helpers\Html;
use luya\web\Svg;

$this->title = Yii::$app->siteTitle . " &rsaquo; " . Module::t('login_btn_login');

$spinner = Svg::widget([
    'folder' => "@admin/resources/svg",
    'cssClass' => 'svg-spinner',
    'file' => 'login/spinner.svg'
]);
?>
    <!-- E-Mail & Password Form -->
    <form class="login-form shadow-lg rounded" method="post" id="loginForm">

        <?php if ($disableLogin): ?>
            <p class="alert alert-light">
                <?= $disableLoginMessage; ?>
            </p>
        <?php else: ?>
            <?php if (Yii::$app->session->getFlash('invalid_reset_token')): ?>
                <p class="alert alert-warning mb-5"><?= Module::t('login_invalid_reset_token'); ?></p>
            <?php endif; ?>

            <?php if (Yii::$app->session->getFlash('reset_password_success')): ?>
                <p class="alert alert-success mb-5"><?= Module::t('login_reset_password_success'); ?></p>
            <?php endif; ?>

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
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" name="login[autologin]" id="autologin">
                    <label class="form-check-label" for="autologin"><?= Module::t('login_autologin_label'); ?></label>
                </div>
                <div class="login-buttons login-buttons-right">
                    <button class="btn btn-primary login-btn" type="submit" tabindex="3">
                        <span class="login-spinner"><?= $spinner; ?></span><span class="login-btn-label"><?= Module::t('login_btn_login'); ?></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </form>

    <!-- Admin Secure Token -->
    <form class="login-form hidden shadow-lg rounded" method="post" id="secureForm">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <div class="login-inputs">
            <div class="login-form-field">
                <label class="input-label login-secure-token-label" for="secure_token"><?= Module::t('login_securetoken_info'); ?></label>
                <input class="login-input" id="secure_token" name="secure_token" type="text" tabindex="1" placeholder="<?= Module::t('login_securetoken'); ?>" />
            </div>
            <div class="login-buttons">
                <button class="btn btn-secondary login-btn login-btn-50" id="abortToken" type="button" tabindex="3">
                    <?= Module::t('button_abort'); ?>
                </button>
                <button class="btn btn-primary login-btn login-btn-50" type="submit"  tabindex="2">
                    <span class="login-spinner"><?= $spinner; ?></span> <span class="login-btn-label"><?= Module::t('button_send'); ?></span>
                </button>
            </div>
        </div>
    </form>

    <!-- User 2FA -->
    <form class="login-form hidden shadow-lg rounded" method="post" id="twofaForm">
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <div class="login-inputs">
            <div class="login-form-field">
                <label class="input-label login-secure-token-label" for="verfiy_code"><?= Module::t('login_otp_label'); ?></label>
                <input class="login-input" id="verfiy_code" name="verfiy_code" autocomplete="one-time-code" type="text" tabindex="1" placeholder="<?= Module::t('login_otp_placeholder'); ?>" />
            </div>
            <div>
                <p class="text-muted"><small><?= Module::t('login_otp_help'); ?> <a href="javascript:$('#backupcode').toggleClass('hidden')"><?= Module::t('login_otp_help_toggler'); ?></a>.</small></p>
            </div>
            <div class="login-form-field hidden" id="backupcode">
                <label class="input-label login-secure-backup-label" for="backup_code"><?= Module::t('login_otp_backup'); ?></label>
                <input class="login-input" id="backup_code" name="backup_code" type="text" tabindex="1" placeholder="" />
            </div>
            <div class="login-buttons">
                <button class="btn btn-secondary login-btn login-btn-50" id="abortTwoFa" type="button" tabindex="3">
                    <?= Module::t('button_abort'); ?>
                </button>
                <button class="btn btn-primary login-btn login-btn-50" type="submit" tabindex="2">
                    <span class="login-spinner"><?= $spinner; ?></span> <span class="login-btn-label"><?= Module::t('button_send'); ?></span>
                </button>
            </div>
        </div>
    </form>

    <div class="login-status mt-3 mb-0 alert alert-danger shadow" id="errorsContainer" style="display: none"></div>

    <div class="login-success" style="visibility: hidden;" id="success">
        <i class="material-icons login-success-icon">check_circle</i>
    </div>

    <?php if ($resetPassword): ?>
        <p id="forgot" class="text-muted mt-2 text-center"><small><?= Html::a(Module::t('login_forgot_password'), ['reset']); ?></small></p>
    <?php endif; ?>
