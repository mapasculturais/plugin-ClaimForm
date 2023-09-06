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
    <div v-if="isActive() && !isAdmin">
        <div>
            <h5 class="opportunity-claim-form__resource bold"><?php i::_e('Discorda do resultado?') ?></h5>
            <entity-file :entity="entity" groupName="formClaimUpload" title="" :editable="!entity.acceptClaim" enableDescription disableName>
                <template #button="popover">
                    <a v-if="!popover.file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                        <mc-icon name="upload"></mc-icon>
                        <?php i::_e("Solicitar recurso") ?>
                    </a>

                    <a v-if="popover.file" @click="popover.toggle()" class="button button--primary button--icon button--primary-outline button-up">
                        <mc-icon name="upload"></mc-icon>
                        <?php i::_e("Editar solicitação") ?>
                    </a>
                </template>

                <template #form="{enableDescription, disableName, formData, setFile}">
                    <div class="col-6">
                        <div class="field">
                            <label><?php i::_e('Arquivo') ?></label>
                            <input type="file" @change="setFile($event)" ref="file">
                        </div>
                    </div>
                    <div class="col-6">
                        <entity-file :entity="entity.opportunity" groupName="formClaimUploadSample"></entity-file>
                    </div>

                    <div v-if="enableDescription" class="col-12">
                        <label><?php i::_e('Descreva abaixo os motivos do recurso') ?></label><br>
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
                    <span v-if="entity.acceptClaim"><?php i::_e('Arquivo aceito') ?></span>
                    <span v-if="!entity.acceptClaim"><?php i::_e('Arquivo em analise') ?></span>
                </div>
            </div>
        </div>
    </div>

    <div v-if="filesUpload && isActive() && isAdmin">
        <div>
            <entity-file :entity="entity" groupName="formClaimUpload" title="<?php i::_e('Arquivo de recurso anexado') ?>"></entity-file>
        </div>
        <div>
            <button class="button button--primary" v-if="!entity.acceptClaim" @click="acceptClaim()"><?php i::_e('Aceitar arquivo') ?></button>
            <button class="button button--primary" @click="refuseClaim()"><?php i::_e('Rejeitar arquivo') ?></button>
        </div>
    </div>
