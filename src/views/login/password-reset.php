<?php

use luya\admin\Module;
use luya\web\Svg;
$spinner = Svg::widget([
    'folder' => "@admin/resources/svg",
    'cssClass' => 'svg-spinner',
    'file' => 'login/spinner.svg'
]);
?>
<div class="login-frame">
    <div class="login-logo">
        <?= Svg::widget([
            'folder' => "@admin/resources/svg",
            'cssClass' => 'login-logo-svg',
            'file' => 'logo/luya_logo.svg'
        ]) ?>
    </div>
    <!-- E-Mail & Password Form -->

    <p class="lead text-center">Reset Password</p>
    <form class="login-form shadow-lg rounded" method="post">


<?php var_dump($model->getErrors()); ?>
        <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
        <div class="login-form-field form-group">
            <input class="login-input" id="email" name="change[password]" type="password" autocomplete="new-password" tabindex="1" required />
            <label for="email" class="login-input-label"><?= Module::t('login_password'); ?></label>
        </div>
        <p class="text-muted">Enter a new password for your account.</p>
        <div class="login-buttons login-buttons-right">
            <button class="btn btn-primary login-btn" type="submit" tabindex="2">
                <span class="login-spinner"><?= $spinner; ?></span><span class="login-btn-label">Reset</span>
            </button>
        </div>
    </form>
</div>

<div class="login-links">
    <ul>
        <li>
            <a href="https://luya.io" target="_blank" class="login-link on-white">luya.io</a>
        </li>
    </ul>
</div>