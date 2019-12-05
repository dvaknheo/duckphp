<?php
use Project\Base\Helper\ViewHelper as V;

V::startSection('content'); ?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header"><?= V::HL('Confirm Password');?></div>

                <div class="card-body">
                    <?= V::HL('Please confirm your password before continuing.'); ?>


                    <form method="POST" action="<?= V::H($url_password_confirm); ?>">
                        <?= $csrf_field?>

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
                                    <?= V::HL('Confirm Password'); ?>

                                </button>

                                <?php if($has_route_password_request): ?>
                                    <a class="btn btn-link" href="<?= V::H($url_password_request); ?>">
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
<?php V::SectionStop();?>
<?php V::ShowBlock('layout')?>
