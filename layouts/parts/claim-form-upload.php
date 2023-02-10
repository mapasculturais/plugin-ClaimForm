<?php

use MapasCulturais\i;

$app = MapasCulturais\App::i();
$file = $entity->getFiles('formClaimUpload');
$filesSample = $entity->opportunity->getFiles('formClaimUploadSample');
$url = $app->createUrl("registration", "upload/{$entity->id}");
$claim_from = $entity->opportunity->claimFrom->format('d/m/Y');
$claim_to = $entity->opportunity->claimTo->format('d/m/Y H:i');
$accept_claim_url = $app->createUrl("registration", 'acceptClaim');

?>

<div class="widget claim-form" ng-controller="ClaimFormController">
    <h3> <?php i::_e("Solicitação de recurso") ?> </h3>
    <small><strong><i><?php i::_e("Período de recurso {$claim_from} a $claim_to") ?></strong></i></small>

    <div class="claim-form-buttons">
        <?php if ($canManipulate) : ?>
            <a class="add btn btn-default" ng-click="open(editbox, 'form-claim', $event)" rel="noopener noreferrer"><?= i::_e('Formulário de recurso') ?></a>
            <?php if ($filesSample) : ?>
                <a class="add btn btn-default download" download href="<?= $filesSample->url ?>"><?= i::_e('Baixar arquivo de exemplo') ?></a>
            <?php endif ?>
        <?php endif ?>

        <br>
    </div>

    <edit-box id="form-claim" position="bottom" cancel-label="<?php i::esc_attr_e("Cancelar"); ?>" submit-label="<?php i::esc_attr_e("Enviar recurso"); ?>" loading-label="<?php i::esc_attr_e("Carregando ..."); ?>" on-submit="sendClaim" close-on-cancel='true' index="{{$index}}" spinner-condition="data.uploadSpinner">
        <form id="send-clain-form" class="js-ajax-upload" method="post" action="<?= $url ?>" data-group="formClaimUpload" enctype="multipart/form-data">
            <div>
                <?php i::_e("Mensagem"); ?>:<br />
                <textarea type="text" rows="5" cols="30" name="description[formClaimUpload]"></textarea>
            </div>
            <div>
                <?php i::_e("Anexar arquivo"); ?>:<br />
                <?php if ($filesSample) : ?>
                    <small><?php i::_e("Baixe o exemplo do arquivo no botão <b><i>Baixar arquivo de exemplo</i></b>"); ?></small> <br>
                    <small><?php i::_e("<b><i>Somente permitido arquivos em pdf</i></b>"); ?></small>
                <?php endif ?>
                <div class="alert danger hidden"></div>
                <p class="form-help"><?php i::_e("Tamanho máximo do arquivo:"); ?> {{maxUploadSizeFormatted}}</p>
                <input type="file" name="formClaimUpload" />

                <div class="js-ajax-upload-progress">
                    <div class="progress">
                        <div class="bar"></div>
                        <div class="percent">0%</div>
                    </div>
                </div>
            </div>
        </form>
    </edit-box>

    <div class="scrolling">
        <ul class="js-formClaimUpload">
            <?php if ($file) : ?>
                <li id="file-<?php echo $file->id ?>" class="objeto <?php if ($this->isEditable()) echo i::_e(' is-editable'); ?>">
                    <a href="<?php echo $file->url . '?id=' . $file->id; ?>" download><?= $file->name ?></a>
                    <?php if($app->user->is('admin') && !$entity->acceptClaim): ?>
                        <a href="<?=$accept_claim_url?><?=$file->id?>"  class="buttons-rigth hltip" title="Aceitar arquivo"><i class="fas fa-check"></i> <span class="configdelete"><?php i::_e("Aceitar"); ?></span></a>
                    <?php endif; ?>

                    <?php if ($canManipulate) : ?>                        
                        <a data-href="<?php echo $file->deleteUrl ?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="buttons-rigth delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo."><span class="configdelete"><?php i::_e("Excluir"); ?></span></a>
                    <?php endif; ?>
                    
                </li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script id='claim-form-response' class='js-mustache-template' type="html/template">
    <li id="file-{{id}}" class="objeto">
        <a href="{{url}}" rel="noopener noreferrer">{{name}}</a> 
        {{#canAcceptClaim}}
        <a href="{{acceptClaimUrl}}"  class="buttons-rigth hltip" title="Aceitar arquivo"><i class="fas fa-check"></i> <span class="configdelete">Aceitar</span></a>
        {{/canAcceptClaim}}
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="buttons-rigth delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"><span class="configdelete">Excluir</span></a>
    </li';
</script>