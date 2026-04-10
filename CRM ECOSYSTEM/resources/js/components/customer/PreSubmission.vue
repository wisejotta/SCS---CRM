<script setup>
import RetainerAgreement from '@/components/customer/AwaitingLOA/RetainerAgreement.vue'

const awaitingLOAProgress = ref(0)
const openedPanel = ref(null)

const props = defineProps({
  documents: {
    type: Object,
    required: true,
  },
})

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
  <VCard title="Pre-submission" variant="tonal">
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
        <!-- RETAINER AGREEMENT -->
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
                :color="backOfficeDocColor('COPY OF PASSPORT: STAMPED PAGES')"
                style="border: 1px solid #fff;"
              >
                {{ backOfficeDocStatus('COPY OF PASSPORT: STAMPED PAGES') }}
              </VChip>
            </div>
            COPY OF PASSPORT: STAMPED PAGES
          </VExpansionPanelTitle>
          <VExpansionPanelText>
            <RetainerAgreement />
          </VExpansionPanelText>
        </VExpansionPanel>

      </VExpansionPanels>
    </VCardText>
  </VCard>
</template>