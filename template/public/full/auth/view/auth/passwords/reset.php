<?php
use UserSystemDemo\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?= V::HL('Reset Password'); ?></div>

                <div class="card-body">
                    <form method="POST" action="<?= $url_password_update; ?>">
                        <?= $csrf_field?>

                        <input type="hidden" name="token" value="<?= V::H($token) ?>">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right"><?= V::HL('E-Mail Address'); ?></label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control <?php if(isset($errors['email'])){ ?> is-invalid <?php } ?>" name="email" value="<?= V::H($olds['email']??''); ?>" required autocomplete="email" autofocus>

                                <?php if(isset($errors['email'])){ ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?= V::H($errors['email']); ?></strong>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right"><?= V::HL('Password'); ?></label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control <?php if(isset($errors['password'])){ ?> is-invalid <?php } ?>" name="password" required autocomplete="new-password">

                                <?php if(isset($errors['password'])){ ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?= V::H($errors['password']); ?></strong>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right"><?= V::HL('Confirm Password'); ?></label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <?= V::HL('Reset Password'); ?>

                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>