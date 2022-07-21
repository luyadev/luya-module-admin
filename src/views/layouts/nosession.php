<?php

use luya\admin\Module;
use luya\helpers\Url;
use luya\web\Svg;

$backgroundImage = $this->context->backgroundImage;

$this->registerJs("
$('#email').focus();
checkInputLabels();
$('.login-logo').addClass('login-logo-loaded');
$('.login-form').addClass('login-form-loaded');
");
?>
<?php $this->beginPage(); ?>
    <!DOCTYPE html>
    <html>
        <head>
            <meta charset="utf-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?= $this->title; ?></title>
            <?php $this->head() ?>
        </head>
        <body class="login-screen">
            <?php $this->beginBody() ?>
                <div class="login-frame">
                    <div class="login-logo">
                        <?= Svg::widget([
                            'folder' => "@admin/resources/svg",
                            'cssClass' => 'login-logo-svg',
                            'file' => 'logo/luya_logo.svg'
                        ]) ?>
                    </div>
                    <?php if ($backgroundImage): ?>
                    <style type="text/css">
                    body {
                        background-image: url('<?= $backgroundImage; ?>');
                        background-size: cover;
                        background-position: 50% 50%;
                    }
                    </style>
                    <?php endif; ?>
                    <?= $content; ?>
                </div>


                <?php if (!$backgroundImage): ?>
                <div class="login-info d-none d-sm-block">
                    <h1 class="login-title"><span class="on-white"><?= Yii::$app->siteTitle; ?></span></h1>
                    <span class="login-info-text on-white"><?php if (Yii::$app->request->isSecureConnection): ?><i alt="<?= Module::t('login_ssl_info');?>" title="<?= Module::t('login_ssl_info');?>" class="material-icons">verified_user</i><?php endif; ?><?= Url::domain(Yii::$app->request->hostInfo); ?></span>
                </div>
                <?php endif; ?>

                <div class="login-links d-none d-sm-block">
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
            <?php $this->endBody() ?>
        </body>
    </html>
<?php $this->endPage() ?>