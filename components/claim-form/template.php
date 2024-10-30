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
    <div v-if="canManipulate && isActive() && !isAdmin" class="opportunity-claim-form clain-form">
        <div class="claim-form__content">
            
            <h4 v-if="entity.files?.[groupFileUpload]" class="opportunity-claim-form__resource semibold"><?php i::_e('Recurso') ?></h4>
            <h4 v-else class="opportunity-claim-form__resource semibold"><?php i::_e('Discorda do resultado?') ?></h4>
            <entity-file 
                :entity="entity" 
                group-name="formClaimUpload" 
                title="" 
                :editable="!entity.files?.[groupFileUpload] && !uploadedFile" 
                title-modal="<?php i::_e('Solicitação de recurso') ?>" 
                @set-file="setFile($event)" 
                @uploaded="uploaded($event)"
                enable-description disable-name >
                
                <template #label>
                    <?php i::_e("Documento anexado:") ?>
                </template>
                <template #button="{open, close, toggle, file}">
                        <a v-if="!file" @click="toggle()" class="button button--primary button--icon button--primary-outline button-up">
                            <mc-icon name="upload"></mc-icon>
                            <?php i::_e("Solicitar recurso") ?>
                        </a>
                </template>

                <template #form="{enableDescription, disableName, formData, setFile, file}">
                    <div v-if="enableDescription" class="field col-12">
                        <label><?php i::_e('Descreva abaixo os motivos do recurso') ?></label>
                        <textarea v-model="formData.description"></textarea>
                    </div>

                    <div class="col-12 opportunity-claim-form__files grid-12">
                        <div v-if="entity.opportunity.files.formClaimUploadSample" class="col-12 field">
                            <label> 
                                Utilize o modelo abaixo para o anexo:
                            </label>
                            <entity-file :entity="entity.opportunity" group-name="formClaimUploadSample" ></entity-file>
                        </div>

                        <div class="field__upload">
                            <div v-if="file.name" class="entity-file__fileName primary__color bold"> {{file.name}} </div>
                            <label for="newFile" class="field__buttonUpload button button--icon button--primary-outline">
                                <mc-icon name="upload"></mc-icon> 
                                <span v-if="newFile"> <?= i::__('Alterar') ?> </span>
                                <span v-else> <?= i::__('Anexar documento') ?> </span>
                                <input id="newFile" type="file" @change="setFile($event)" ref="file">
                            </label>
                        </div>
                    </div>
                </template>
            </entity-file>
            
            <div v-if="entity.files?.[groupFileUpload]">
                <div v-if="entity.files?.[groupFileUpload]?.description">
                    <label class="bold"><?php i::_e('Motivo do recurso') ?>:</label>
                    <p class="claim-form__description">{{entity.files?.[groupFileUpload]?.description}}</p>
                </div>

                <div>
                    <span v-if="entity.acceptClaim" class="success__color bold opportunity-claim-form__status"><mc-icon name="dot"></mc-icon><?php i::_e('Arquivo aceito') ?></span>
                    <span v-if="!entity.acceptClaim" class="helper__color bold opportunity-claim-form__status"><mc-icon name="dot"></mc-icon><?php i::_e('Arquivo em analise') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div v-if="entity.files?.[groupFileUpload] && isActive() && isAdmin">
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
