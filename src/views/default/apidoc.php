<?php

use luya\helpers\Url;

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
  </head>
  <body>
  <div style="padding:10px; background-color:#F0F0F0">
      <a href="<?= Url::toRoute(['/admin/default/index']); ?>">&laquo; Back to Admin</a>
  </div>
    <redoc spec-url='/admin/api-admin-remote/openapi'></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@next/bundles/redoc.standalone.js"> </script>
  </body>
</html>