<?php
use UserSystemDemo\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<div class="container">

<form method="POST" action="<?= V::H($url_login); ?>">
    <?= $csrf_field ?>

    <div class="form-group row">
        <label for="name" class="col-md-4 col-form-label text-md-right"><?= V::HL('User name'); ?></label>
        <div class="col-md-6">
            <input id="name" class="form-control <?php if(isset($errors['email'])){ ?> is-invalid <?php } ?>" name="name" value="<?= V::H($olds['email']??''); ?>" required autocomplete="email" autofocus>
        </div>
    </div>

    <div class="form-group row">
        <label for="password" class="col-md-4 col-form-label text-md-right"><?= V::HL('Password'); ?></label>

        <div class="col-md-6">
            <input id="password" type="password" class="form-control <?php if(isset($errors['password'])){ ?> is-invalid <?php } ?>" name="password" required autocomplete="current-password">
            <?php if(isset($errors['password'])){ ?>
                <span class="invalid-feedback" role="alert">
                    <strong><?= V::H($errors['password']); ?></strong>
                </span>
            <?php } ?>
        </div>
    </div>


    <div class="form-group row mb-0">
        <div class="col-md-8 offset-md-4">
            <button type="submit" class="btn btn-primary">
                <?= V::HL('Login'); ?>

            </button>

            <?php if($has_route_password_request): ?>
                <a class="btn btn-link" href="<?= V::H($url_password_request);?>">
                    <?= V::HL('Forgot Your Password?'); ?>

                </a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>