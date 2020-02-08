<?php

use luya\admin\Module;
use luya\web\Svg;
$spinner = Svg::widget([
    'folder' => "@admin/resources/svg",
    'cssClass' => 'svg-spinner',
    'file' => 'login/spinner.svg'
]);
?>
<p class="lead text-center">Reset Password</p>
<form class="login-form shadow-lg rounded" method="post">
    <p class="text-muted mb-5 mt-0 pt-0">In order to reset your Password enter your E-Mail-Adresse and an email with a reset link will be sent to your inbox.</p>

    <?php if (Yii::$app->session->getFlash('reset_password_success')): ?>
        <p class="alert alert-success">Password reset link has been sent. <b>Check your mailbox</b> and click the link.</p>
    <?php else: ?>
    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="login-form-field form-group">
        <input class="login-input" id="email" name="reset[email]" type="email" autocomplete="email" tabindex="1" required />
        <label for="email" class="login-input-label"><?= Module::t('login_mail'); ?></label>
    </div>
    <div class="login-buttons login-buttons-right">
        <button class="btn btn-primary login-btn" type="submit" tabindex="2">
            <span class="login-spinner"><?= $spinner; ?></span><span class="login-btn-label">Reset</span>
        </button>
    </div>
    <?php endif; ?>
</form>

<?php if (!empty($model->getErrors())): ?>
<div class="login-status mt-3 mb-0 alert alert-danger shadow">
    <?php foreach($model->getErrorSummary(true) as $error): ?>
        <p class="my-1"><?= $error; ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>