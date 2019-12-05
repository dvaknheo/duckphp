<?php
use Project\Base\Helper\ViewHelper as V;

$status=session('status');

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?= V::HL('Reset Password'); ?></div>

                <div class="card-body">
                    <?php if(): ?>
                        <div class="alert alert-success" role="alert">
                            <?= V::H($status)?>

                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= V::H($url_password_email)?>">
                        <?= $csrf_field; ?>

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

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <?= V::HL('Send Password Reset Link'); ?>

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