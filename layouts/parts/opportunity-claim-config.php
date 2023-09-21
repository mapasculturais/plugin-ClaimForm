<?php
$this->import('enable-claim');
?>

<enable-claim v-if="phase.__objectType == 'evaluationmethodconfiguration'" :entity="phase.opportunity"></enable-claim>
<enable-claim v-else-if="!phase.evaluationMethodConfiguration" :entity="phase"></enable-claim>