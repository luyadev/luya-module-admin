<?php

use luya\admin\assets\Main;
use luya\helpers\Url;

Main::register($this);
$this->beginPage()
?>
<!DOCTYPE html>
<html>
  <head>
    <title><?= Yii::$app->siteTitle; ?> - OpenApi Explorer</title>
    <!-- needed for adaptive design -->
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">
    <!--
    ReDoc doesn't change outer page styles
    -->
    <style>
      body {
        margin: 0;
        padding: 0;
      }
    </style>
    <?php $this->head(); ?>
  </head>
  <body>
  <?php $this->beginBody(); ?>
      <div class="mainnav mainnav--horizontal">
          <div class="mainnav-static">
              <ul class="mainnav-list">
                  <li class="mainnav-entry">
                      <a class="mainnav-link" href="<?= Url::toRoute(['/admin/default/index']); ?>">
                          <i class="mainnav-icon material-icons">keyboard_backspace</i>
                          <span class="mainnav-label">Back to Admin</span>
                      </a>
                  </li>
              </ul>
          </div>
          <?php /* Example for right aligned button(s): <div class="mainnav-static mainnav-static--bottom">
              <ul class="mainnav-list">
                  <li class="mainnav-entry">
                      <a class="mainnav-link" href="#">
                          <i class="mainnav-icon material-icons">flare</i>
                          <span class="mainnav-label">Example</span>
                      </a>
                  </li>
              </ul>
          </div> */ ?>
      </div>
    <redoc spec-url='/admin/api-admin-remote/openapi<?php if (Yii::$app->remoteToken): ?>?token=<?= sha1(Yii::$app->remoteToken); endif; ?>'></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"> </script>
    <?php $this->endBody() ?>
  </body>
</html>
<?php $this->endPage() ?>