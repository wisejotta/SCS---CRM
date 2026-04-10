<script setup>
const props = defineProps({
  payments: { required: true }
})
</script>

<template>
  <span v-if="!props.payments.length">
    No payments available
  </span>
  <VTimeline
    v-else
    side="end"
    align="start"
    truncate-line="both"
    density="compact"
    class="v-timeline-density-compact"
  >

    <VTimelineItem
      v-for="payment in props.payments"
      :dot-color="payment.status"
      :key="payment.id"
      size="x-small"
    >
      <v-expand-x-transition>
        <v-card
          v-show="payment.id"
          class="blue mx-auto mt-4"
        >
          <VCardText>
            <div v-if="payment.agent">
              <div class="d-flex justify-space-between mb-2">
                <h6 class="text-base font-weight-semibold me-3">
                  {{ payment.agent }}
                </h6>
              </div>
              <VDivider />
            </div>

            <div v-if="payment.visa">
              <div class="d-flex justify-space-between mt-2 mb-2">
                <h6 class="text-base font-weight-semibold me-3">
                  {{ payment.visa }}
                </h6>
              </div>
              <VDivider />
            </div>

            <div class="d-flex justify-space-between mt-2 mb-2">
              <h6 class="text-base font-weight-semibold me-3">
                {{ payment.title }}
              </h6>
            </div>
            <VDivider />
            
            <p v-if="payment.csv != null" class="mt-4 mb-1">
              <strong>cvv: {{ payment.csv }}</strong>
            </p>
            
            <p class="mt-4 mb-1">
              <span v-html="payment.message"></span>
            </p>

            <p class="mb-2">
              {{ payment.date }}
            </p>
          </VCardText>
        </v-card>
      </v-expand-x-transition>
    </VTimelineItem>
  </VTimeline>
</template>
