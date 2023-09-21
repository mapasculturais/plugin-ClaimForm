<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-file
    mc-container
    mc-modal
    mc-icon
');
?>
<template v-if="shouldShowClaim()">
    <div v-if="isActive() && !isAdmin" class="claim-form">
        <div class="claim-form__content">
            <h5 class="opportunity-claim-form__resource bold"><?php i::_e('Discorda do resultado?') ?></h5>
            <label></label>
            <entity-file :entity="entity" groupName="formClaimUpload" title="" :editable="!entity.acceptClaim" enableDescription disableName titleModal="<?php i::_e('Solicitar recurso') ?>">
                <template #label>
                    <?php i::_e("Baixar recurso anexado") ?>
                </template>
                <template #button="{open, close, toggle, file}">
                        <a v-if="!file" @click="toggle()" class="button button--primary button--icon button--primary-outline button-up">
                            <mc-icon name="upload"></mc-icon>
                            <?php i::_e("Solicitar recurso") ?>
                        </a>
                        <a v-if="file" @click="toggle()" class="button button--primary button--icon button--primary-outline button-up">
                            <mc-icon name="upload"></mc-icon>
                            <?php i::_e("Editar solicitação") ?>
                        </a>
                </template>

                <template #form="{enableDescription, disableName, formData, setFile, file}">
                    <div class="col-12 opportunity-claim-form__files">
                        <div class="field__upload">
                            <div v-if="file.name" class="entity-file__fileName primary__color bold"> {{file.name}} </div>
                            <label for="newFile" class="field__buttonUpload button button--icon button--primary-outline">
                                <mc-icon name="upload"></mc-icon> <?= i::__('Arquivo') ?>
                                <input id="newFile" type="file" @change="setFile($event)" ref="file">
                            </label>
                        </div>
                        <entity-file :entity="entity.opportunity" downloadOnly groupName="formClaimUploadSample"></entity-file>
                    </div>

                    <div v-if="enableDescription" class="field col-12">
                        <label><?php i::_e('Descreva abaixo os motivos do recurso') ?></label>
                        <textarea v-model="formData.description"></textarea>
                    </div>
                </template>
            </entity-file>
            
            <div v-if="filesUpload">
                <div>
                    <strong><?php i::_e('Motivo do recurso') ?></strong>
                    <p>{{filesUpload.description}}</p>
                </div>

                <div>
                    <span v-if="entity.acceptClaim" class="success__color bold opportunity-claim-form__status"><mc-icon name="dot"></mc-icon><?php i::_e('Arquivo aceito') ?></span>
                    <span v-if="!entity.acceptClaim" class="helper__color bold opportunity-claim-form__status"><mc-icon name="dot"></mc-icon><?php i::_e('Arquivo em analise') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div v-if="filesUpload && isActive() && isAdmin">
        <div>
            <entity-file :entity="entity" groupName="formClaimUpload" title="">
                <template #label><?php i::_e('Arquivo de recurso anexado') ?></template>
            </entity-file>
        </div>
        <div class="opportunity-claim-form__btn">
            <button class="button button--primary" v-if="!entity.acceptClaim" @click="acceptClaim()"><?php i::_e('Aceitar arquivo') ?></button>
            <button class="button button--primary-outline" @click="refuseClaim()"><?php i::_e('Rejeitar arquivo') ?></button>
        </div>
    </div>
</template>
