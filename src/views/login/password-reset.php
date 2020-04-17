<?php

use luya\admin\Module;
use luya\web\Svg;

$this->title = Yii::$app->siteTitle . " &rsaquo; " . Module::t('reset_form_title');
$spinner = Svg::widget([
    'folder' => "@admin/resources/svg",
    'cssClass' => 'svg-spinner',
    'file' => 'login/spinner.svg'
]);
?>
<p class="lead text-center"><?= Module::t('reset_form_title'); ?></p>
<form class="login-form shadow-lg rounded" method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />
    <p class="text-muted mb-5"><?= Module::t('reset_form_text'); ?></p>
    <div class="login-form-field form-group">
        <input class="login-input" id="email" name="change[password]" type="password" autocomplete="new-password" tabindex="1" required />
        <label for="email" class="login-input-label"><?= Module::t('login_password'); ?></label>
    </div>
    <div class="login-buttons login-buttons-right">
        <button class="btn btn-primary login-btn" type="submit" tabindex="2">
            <span class="login-spinner"><?= $spinner; ?></span><span class="login-btn-label"><?= Module::t('reset_form_submit_btn'); ?></span>
        </button>
    </div>
</form>

<?php if (!empty($model->getErrors())): ?>
<div class="login-status mt-3 mb-0 alert alert-danger shadow">
    <?php foreach ($model->getErrorSummary(true) as $error): ?>
        <p class="my-1"><?= $error; ?></p>
    <?php endforeach; ?>
</div>
<?php endif; ?>