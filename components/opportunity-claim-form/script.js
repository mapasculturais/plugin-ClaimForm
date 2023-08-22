app.component('opportunity-claim-form', {
    template: $TEMPLATES['opportunity-claim-form'],
    setup() {
        const messages = useMessages();
        const text = Utils.getTexts('opportunity-claim-form')
        return { text, messages }
    },

    props: {
        entity: {
            type: Entity,
            required: true
        },

    },

    data() {
        return {
            claim: {
                registration_id: $MAPAS.config.opportunityClaimForm.registrationId
            },
        }
    },
    
    methods: {
        close(modal) {
            this.claim.message = '';
            modal.close();
        },
        open(modal) {
            this.claim.message = '';
            modal.open();
        },
        isActive() {
            if (this.entity.opportunity.status > 0 && this.entity.opportunity.publishedRegistrations && this.entity.opportunity.claimDisabled === "0") {
                return true;
            }
            return false;
        },
        async sendClain(modal) {
            let api = new API();
            let url = Utils.createUrl('opportunity', 'sendOpportunityClaimMessage');
            await api.POST(url, this.claim).then(data => {
                this.messages.success(this.text('Solicitação de recurso enviada'));
                this.close(modal);
            });

        }
    },

    computed: {
        modalTitle() {
            return this.text('Solicitar Recurso');
        },
        claimFromDate() {
            return this.entity.opportunity.claimFrom.date('numeric year') +" "+ this.entity.opportunity.claimFrom.time('long');
        },
        claimclaimTo() {
            return this.entity.opportunity.claimTo.date('numeric year') +" "+ this.entity.opportunity.claimTo.time('long');
        },
    },
});

