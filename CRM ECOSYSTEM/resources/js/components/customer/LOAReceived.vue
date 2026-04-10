<script setup>
import RetainerAgreement from '@/components/customer/AwaitingLOA/RetainerAgreement.vue'
import LetterOfAcceptance from '@/components/customer/AwaitingLOA/LetterOfAcceptance.vue'
import EmploymentLetter from '@/components/customer/AwaitingLOA/EmploymentLetter.vue'

const props = defineProps({
  documents: {
    type: Object,
    required: true,
  },
})

const awaitingLOAProgress = ref(0)
const openedPanel = ref(null)

const backOfficeDocColor = (name) => {
  const doc = props.documents[name]
  if(doc) {
    if(doc.status == 'pre-approved') return 'success'
    if(doc.status == 'required') return 'error'
    if(doc.status == 'optional') return 'primary'
    if(doc.status == 'review') return 'info'
  }
  return 'error' 
}

const backOfficeDocStatus = (name) => {
  const doc = props.documents[name]
  return doc ? doc.status.toUpperCase() : 'REQUIRED'
}
</script>

<template>
  <VCard title="LOA Received" variant="tonal">
    <template #prepend>
      <VProgressCircular
        :rotate="360"
        :size="40"
        :width="6"
        :model-value="awaitingLOAProgress"
        color="primary"
      >
        {{ awaitingLOAProgress }}
      </VProgressCircular>
    </template>

    <VCardText>
      <VExpansionPanels
        v-model="openedPanel"
        class="expansion-panels-width-border"
      >
        <!-- IMM 1294 STUDY PERMIT FORM -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 0 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('IMM 1294 STUDY PERMIT FORM')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('IMM 1294 STUDY PERMIT FORM') }}
              </VChip>
            </div>
            IMM 1294 STUDY PERMIT FORM
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <RetainerAgreement />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- IMM 5645 FAMILY INFO FORM -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 1 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('IMM 5645 FAMILY INFO FORM')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('IMM 5645 FAMILY INFO FORM') }}
              </VChip>
            </div>
            IMM 5645 FAMILY INFO FORM
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <LetterOfAcceptance />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- IMM 5476 USE OF REPRESENTATIVE -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 2 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('IMM 5476 USE OF REPRESENTATIVE')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('IMM 5476 USE OF REPRESENTATIVE') }}
              </VChip>
            </div>
            IMM 5476 USE OF REPRESENTATIVE
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <EmploymentLetter />
          </VExpansionPanelText>
        </VExpansionPanel>

        <!-- IELTS -->
        <VExpansionPanel elevation="0">
          <VExpansionPanelTitle
            :color="openedPanel == 3 ? 'primary' : ''"
          >
            <div
              class="me-2 pa-1"
              style="background-color: #fff; border-radius: 5px;"
            >
              <VChip
                label
                :color="backOfficeDocColor('PROOF OF FUNDS')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('PROOF OF FUNDS') }}
              </VChip>
            </div>
            PROOF OF FUNDS
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <EmploymentLetter />
          </VExpansionPanelText>
        </VExpansionPanel>
      </VExpansionPanels>
    </VCardText>
  </VCard>
</template>