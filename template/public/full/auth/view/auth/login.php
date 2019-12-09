<?php
use UserSystemDemo\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?= V::HL('Login'); ?></div>

                <div class="card-body">
                    <form method="POST" action="<?= V::H($url_login); ?>">
                        <?= $csrf_field ?>

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
                                <input id="password" type="password" class="form-control <?php if(isset($errors['password'])){ ?> is-invalid <?php } ?>" name="password" required autocomplete="current-password">

                                <?php if(isset($errors['password'])){ ?>
                                    <span class="invalid-feedback" role="alert">
                                        <strong><?= V::H($errors['password']; ?></strong>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?= $olds['remember']? 'checked' : ''; ?>>

                                    <label class="form-check-label" for="remember">
                                        <?= V::HL('Remember Me'); ?>

                                    </label>
                                </div>
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
                </div>
            </div>
        </div>
    </div>
</div>
<?php V::stopSection(); ?>
<?php V::ShowBlock('layouts/app');?>