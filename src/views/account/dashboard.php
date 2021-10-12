<?php

use luya\admin\helpers\Angular;
use luya\admin\Module;

?>
<div class="luya-content" ng-controller="AccountController">
    <collapse-container title="<?= Module::t('mode_user_personal_info') ?>" icon="face" class="mb-3">
        <form ng-submit="changePersonData(profile)">
            <zaa-select fieldid="mode_user_title" model="profile.title" label="<?= Module::t('mode_user_title'); ?>" options="[{value:1, label:'<?= Module::t('model_user_title_mr'); ?>'}, {value:2, label:'<?= Module::t('model_user_title_mrs'); ?>'}]"></zaa-select>
            <zaa-text autocomplete="given-name" fieldid="mode_user_firstname" label="<?= Module::t('mode_user_firstname'); ?>" model="profile.firstname"></zaa-text>
            <zaa-text autocomplete="family-name" fieldid="mode_user_lastname" label="<?= Module::t('mode_user_lastname'); ?>" model="profile.lastname"></zaa-text>
            <zaa-text autocomplete="email" fieldid="mode_user_email" label="<?= Module::t('mode_user_email'); ?>" model="profile.email"></zaa-text>
            <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
            
        </form>
        <div ng-show="activities.open_email_validation" class="mt-3">
            <hr />
            <div class="alert alert-warning">
                <p class="mb-0"><?= Module::t('account_changeemail_enterverificationtoken')?></p>
            </div>
            <form ng-submit="changeEmail()">
                <zaa-text fieldid="mode_email_token" label="<?= Module::t('model_user_email_verification_token')?>" model="email.token"></zaa-text>
                <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
            </form>
        </div>
    </collapse-container>

    <collapse-container title="<?= Module::t('settings_general') ?>" class="mb-3" icon="account_circle">
        <div class="form-group form-side-by-side">
            <div class="form-side form-side-label">
                <label for="lang-changer"><?= Module::t('layout_rightbar_languagelabel')?></label>
            </div>
            <div class="form-side">
                <select id="lang-changer" class="form-control" ng-model="settings.luyadminlanguage">
                    <?php foreach ($this->context->module->interfaceLanguageDropdown as $key => $lang): ?>
                        <option value="<?= $key; ?>"><?= $lang;?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group form-side-by-side">
            <div class="form-side form-side-label">
                <label for="userSettings.isDeveloper"><?= Module::t('settings_developer_mode') ?></label>
            </div>
            <div class="form-side">
                <input type="checkbox" ng-model="settings.isDeveloper" id="userSettings.isDeveloper" />
                <label for="userSettings.isDeveloper"></label>
            </div>
        </div>
        <button type="button" class="btn btn-save btn-icon" ng-click="changeSettings(settings)"><?= Module::t('layout_rightbar_savebtn'); ?></button>
    </collapse-container>

    <collapse-container title="<?= Module::t('mode_user_password') ?>" icon="vpn_key" class="mb-3">
        <form ng-submit="changePassword()">
            <zaa-password autocomplete="current-password" fieldid="model_user_oldpassword" label="<?= Module::t('model_user_oldpassword'); ?>" model="pass.oldpass"></zaa-password>
            <hr class="mb-4" />
            <zaa-password autocomplete="new-password" fieldid="aws_changepassword_new_pass" label="<?= Module::t('aws_changepassword_new_pass'); ?>" model="pass.newpass"></zaa-password>
            <zaa-password autocomplete="new-password" fieldid="aws_changepassword_new_pass_retry" label="<?= Module::t('aws_changepassword_new_pass_retry'); ?>" model="pass.newpassrepeat"></zaa-password>
            <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
        </form>
    </collapse-container>

    <collapse-container title="<?= Module::t('settings_devices') ?>" icon="devices" class="mb-3">
        <p><?= Module::t('settings_devices_info'); ?></p>
        <p ng-show="devices.length == 0" class="alert alert-info mb-0"><?= Module::t('settings_devices_empty'); ?></p>
        <table ng-show="devices.length > 0" class="table table-bordered table-striped mb-0">
            <thead>
                <th><?= Module::t('device'); ?></th>
                <th><?= Module::t('last_login'); ?></th>
                <th colspan="2"><?= Module::t('first_login') ;?></th>
            </thead>
            <tr ng-repeat="device in devices">
                <td>
                    {{ device.userAgentName}}
                    <span class="badge badge-success" ng-show="device.isCurrentDevice"><?= Module::t('settings_devices_this'); ?></span>
                </td>
                <td>
                    {{ device.updated_at * 1000 | date:'short' }}
                </td>
                <td>
                    {{ device.created_at * 1000 | date:'short' }}
                </td>
                <td class="text-center">
                    <button type="button" ng-click="removeDevice(device)" class="btn btn-icon btn-delete"></button>
                </td>
            </tr>
        </table>
    </collapse-container>

    <collapse-container title="<?= Module::t('settings_2fa'); ?>" icon="fingerprint" class="mb-3">
         <p><?= Module::t('settings_2fa_intro'); ?></p>
         <div ng-if="twoFaBackupCode" class="alert alert-warning">
            <?= Module::t('settings_2fa_backup_code_hint'); ?>
            <modal is-modal-hidden="false" modal-title="Backup Code">
                <p><?= Module::t('settings_2fa_modal_pretext'); ?></p>
                <h1 class="alert alert-warning mb-3">{{twoFaBackupCode}}</h1>
                <p class="mb-0"><?= Module::t('settings_2fa_modal_after'); ?></p>
            </modal>
         </div>
         <div ng-show="twoFa.enabled" class="alert alert-success">
            <?= Module::t('settings_2fa_success'); ?>
             <button type="button" class="btn btn-danger" ng-click="disableTwoFa()"><?= Module::t('aws_groupauth_th_remove'); ?></button>
         </div>
         <div ng-show="!twoFa.enabled">
            <form ng-submit="registerTwoFa()">
                <p class="lead d-flex align-items-center"><span class="badge badge-pill badge-primary mr-2">1</span> <?= Module::t('settings_2fa_step1'); ?></p>
                <p><img ng-src="{{twoFa.qrcode}}" class="img-fluid" /></p>
                <p class="lead d-flex align-items-center"><span class="badge badge-pill badge-primary mr-2">2</span> <?= Module::t('settings_2fa_step2'); ?></p>
                <?= Angular::text('twoFa.verification', Module::t('settings_2fa_verify_code_label'))->hint(Module::t('settings_2fa_verify_code_hint')); ?>
                <button class="btn btn-save btn-icon" type="submit"><?= Module::t('layout_rightbar_savebtn'); ?></button>
            </form>
         </div>
    </collapse-container>
</div>