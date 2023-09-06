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
<div v-if="isActive() && !isAdmin">
    <div>
        <entity-file :entity="entity" groupName="formClaimUpload" title="" :editable="!entity.acceptClaim" enableDescription></entity-file>
        
        <div v-if="filesUpload">
            <div>
                <strong><?php i::_e('Motivo do recurso') ?></strong>
                <p>{{filesUpload.description}}</p>
            </div>

            <div>
                <span v-if="entity.acceptClaim"><?php i::_e('O recurso foi aceito') ?></span>
                <span v-if="!entity.acceptClaim"><?php i::_e('O recurso esta pendente de análise') ?></span>
            </div>
        </div>
    </div>
</div>

<div v-if="filesUpload && isActive() && isAdmin">
    <div>
        <entity-file :entity="entity" groupName="formClaimUpload" title="<?php i::_e('Arquivo de recurso anexado') ?>"></entity-file>
    </div>
    <div>
        <button class="button button--primary" v-if="!entity.acceptClaim" @click="acceptClaim()"><?php i::_e('Aceitar recurso') ?></button>
        <button class="button button--primary" @click="refuseClaim()"><?php i::_e('Rejeitar recurso') ?></button>
    </div>
</div>