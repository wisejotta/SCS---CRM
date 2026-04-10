<template>
  <VCard>
    <VCardText>
      <div class="d-flex align-center justify-space-between">
        <span class="text-sm text-disabled">Monthly Goal</span>
        <span class="text-success">{{ props.commission.perc }}</span>
      </div>
    </VCardText>

    <VCardText>
      <VRow no-gutters>
        <VCol cols="5">
          <div class="d-flex align-center mb-3 text-danger">
            <h4>Progress</h4>
          </div>
          <span class="text-sm">{{ props.commission.progress }}</span>
        </VCol>

        <VCol cols="2">
          <div class="d-flex flex-column align-center justify-center h-100">
            <VDivider
              vertical
              class="mx-auto"
            />

            <div class="pa-1">
              <VChip size="20">
                <span class="text-xs pa-1">VS</span>
              </VChip>
            </div>

            <VDivider
              vertical
              class="mx-auto"
            />
          </div>
        </VCol>

        <VCol
          cols="5"
          class="text-end"
        >
          <div class="d-flex align-center justify-end mb-3">
            <h4>Goal</h4>
          </div>
          <span class="text-sm">{{ props.commission.goal }}</span>
        </VCol>
      </VRow>

      <div class="mt-6">
        <VProgressLinear
          :max="props.commission.max || props.commission.goal"
          :model-value="props.commission.prog || props.commission.progress"
          color="info"
          height="8"
          rounded
        />
      </div>
    </VCardText>
  </VCard>
</template>

<script setup>
const props = defineProps({
  commission: {
    type: Object,
    required: true,
  },
})
</script>